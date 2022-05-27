<?php

namespace App\Components;
use Psr\Container\ContainerInterface;
use PDO;

class Session
{
    protected $container;
    protected $db;
    protected $session;
    protected $user;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->session = new \SlimSession\Helper;
        $this->db = $this->container->get('db');
    }

    public function getUser() {
        if($this->user == null) {
            if ($this->isLogin()) {
                $this->user = new User($this->container);
                if (!$this->user->getUser($this->session->get('userBaseId', 0))) {
                    $this->user = null;
                }
            } else {
                $this->user = null;
            }
        }
        return $this->user;
    }

    public function getDate() {
        if(!$this->session->exists('date')) {
            $datenow = new \DateTime('NOW', new \DateTimeZone('GMT'));
            $this->session->set('date', $datenow->format('U'));
        }
        
        return \DateTime::createFromFormat('U', $this->session->get('date', 0));
    }
    
    public function getSchoolYearBeginDate() {
        return $this->getSchoolYear()['begindate'];
    }
    
    public function getSchoolYearEndDate() {
        return $this->getSchoolYear()['enddate'];
    }

    public function getSchoolYear() {
        $sth = $this->db->prepare('SELECT * FROM years ORDER BY begindate DESC');
        $years = [];
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $years[] = [
                    'id' => $a_row['id'],
                    'name' => $a_row['name'],
                    'begindate' => new \DateTime($a_row['begindate'], new \DateTimeZone('GMT')),
                    'enddate' => null,
                ];
            }
            $sth->closeCursor();
        }
        
        $countYears = count($years);
        for($i = 0; $i < $countYears; $i++) {
            if($i == 0) {
                    $years[$i]['enddate'] = clone $years[$i]['begindate'];
                    $years[$i]['enddate']->add(new \DateInterval('P1Y'));
            } else if($i < $countYears - 1) {
                $years[$i]['enddate'] = clone $years[$i - 1]['begindate'];
                $years[$i]['enddate']->sub(new \DateInterval('P1D'));
            }
        }
        
        foreach ($years as $item) {
            if($item['begindate'] < $this->getDate()) {
                return $item;
            }
        }
    }

    public function setDate($date) {
        $this->session->set('date', $date);
        return true;
    }

    public function status($modul = null) {

        if(!$this->isLogin()) {
            return false;
        }
        
        $user = $this->getUser();
        if($user == null) {
            return false;
        }
        
        $access = false;
        $sth = null;
        if($modul == null) {
            $sth = $this->db->prepare('SELECT groupId FROM access');
        } else {
            $sth = $this->db->prepare('SELECT groupId FROM access WHERE modul = :modul');
            $sth->bindValue(':modul', $modul, PDO::PARAM_STR);
        }
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if($user->isInGroup($a_row['groupId'])) { 
                    $access = true;
                }
            }
            $sth->closeCursor();
        } else {
            return false;
        }
        
        return $access;
    }
    
    public function isLogin() {
        if($this->session->exists('isLogin') && ($this->session->get('isLogin', false) == true)) {
            return true;
        }
        return false;
    }

    public function login($username, $password) {
        $sth = $this->db->prepare('SELECT * FROM users WHERE username = :username');
        $sth->bindValue(':username', $username, PDO::PARAM_STR);
        if($sth->execute()) {
            if(($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if(password_verify($password, $a_row['password'])) {
                    $this->session->set('isLogin', true);
                    $this->session->set('userBaseId', $a_row['userId']);
                    $this->session->set('date', time());
                    return true;
                }
            }
            $sth->closeCursor();
        }
        return false;
    }

    public function logout() {
        $this->session->set('isLogin', false);
        $this->session->delete('userBaseId');
        return true;
    }
}