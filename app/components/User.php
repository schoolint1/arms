<?php

namespace App\Components;
use Psr\Container\ContainerInterface;
use PDO;

class User
{
    protected $container;
    protected $db;

    public $id;
    public $surname;
    public $firstname;
    public $patronymic;
    public $gender;
    public $birthday;

    private $user_groups = null;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $this->container->get('db');
    }
    
    public function getId() {
        return $this->id;
    }

    public function getFullName() {
        $str = $this->surname . ' ' . $this->firstname;
        if(strlen($this->patronymic) > 0) {
            $str .= ' ' . $this->patronymic;
        }
        return $str;
    }

    public function getShortName() {
        $str = $this->surname . ' ' . mb_substr ($this->firstname, 0, 1) . '.';
        if(mb_strlen($this->patronymic) > 0) {
            $str .= ' ' . mb_substr ($this->patronymic, 0, 1) . '.';
        }
        return $str;
    }

    public function isInGroup($id) {
        if($this->user_groups == null) {
            $this->getGroups();
            if($this->user_groups == null || count($this->user_groups) == 0) {
                return false;
            }
        }
        return in_array($id, $this->user_groups);
    }

    public function getGroups() {
        if(is_array($this->user_groups)) {
            return $this->user_groups;
        }

        $sth = $this->db->prepare('SELECT * FROM groups');
        $all_groups = [];
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $all_groups[$a_row['id']] = [
                    'parentId' => $a_row['parentId'],
                    'name' => $a_row['name'],
                ];
            }
            $sth->closeCursor();
        }

        $this->user_groups = [];
        $sth = $this->db->prepare('SELECT groupId FROM users_groups WHERE userId = :userId');
        $sth->bindValue(':userId', $this->id, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if(array_key_exists($a_row['groupId'], $all_groups)) {
//                    $this->user_groups[] = $a_row['groupId'];
//                    if($all_groups[$a_row['groupId']]['parentId'] != 0) {
//                        $parentId = $all_groups[$a_row['groupId']]['parentId'];
//                        while ($parentId != 0) {
//                            if(!in_array($parentId, $this->user_groups) && array_key_exists($parentId, $all_groups)) {
//                                $this->user_groups[] = $parentId;
//                                $parentId = $all_groups[$parentId]['parentId'];
//                            } else {
//                                break;
//                            }
//                        }
//                    }
                    array_push($this->user_groups, $a_row['groupId']);
                }
            }
            $sth->closeCursor();
        }
        
        reset($this->user_groups);
        while(true) {
            $item = current($this->user_groups);
            if($item === false) {
                break;
            }
            foreach($all_groups AS $indexGroup => $group) {
                if($group['parentId'] == $item) {
                    array_push($this->user_groups, $indexGroup);
                }
            }
            next($this->user_groups);
        }

        return array_unique($this->user_groups);
    }

    public function getUser($id) {
        $this->id = $id;
        $status = false;
        $sth = $this->db->prepare('SELECT * FROM users_base WHERE id = :id');
        $sth->bindValue(':id', (int)$id, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $this->surname = $a_row['surname'];
                $this->firstname = $a_row['firstname'];
                $this->patronymic = $a_row['patronymic'];
                $this->gender = $a_row['gender'];
                $this->birthday = new \DateTime($a_row['birthday']);
                $status = true;
            }
            $sth->closeCursor();
        }
        return $status;
    }
}