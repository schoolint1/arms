<?php

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Slim\Routing\RouteContext;
use PDO;

class SpecialistController {
    protected $container;
    protected $view;
    protected $db;
    protected $specialistId;

    use TraintUsers;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->db = $this->container->get('db');
        $this->view = $this->container->get('view');
        $this->view->addAttribute('container', $this->container);
        $this->view->addAttribute('navbar', 'spc/navbar.php');
    }
    
    private function getSpecialistInfo($id) {
        $specialist = [];
        $sth = $this->db->prepare('SELECT `name` FROM groups WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $specialist['id'] = $id;
                $specialist['name'] = $a_row['name'];
            }
            $sth->closeCursor();
        }
        
        $specialistsConfig = $this->container->get('specialistsConfig');
        if(array_key_exists($id, $specialistsConfig)) {
            if(array_key_exists('name', $specialistsConfig[$id])) {
                $specialist['name'] = $specialistsConfig[$id]['name'];
            }
        }
        
        return $specialist;
    }

    public function register($request, $response, $args) {
        $this->view->setLayout('layout.php');
        // Для выбора меню
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $this->specialistId = $route->getArgument('id');
        $this->view->addAttribute('specialistId', $this->specialistId);
        $this->view->addAttribute('routeName', $route->getName());
        $specialist = $this->getSpecialistInfo($this->specialistId);
        $this->view->addAttribute('specialist', $specialist);
        $this->view->addAttribute('title', 'АРМы :: ' . $specialist['name'] . ' :: Список');
        // Дети
        $users = [];
        $usersIds = [];
        $sth = $this->db->prepare('SELECT
    rbl_list.id, 
    rbl_list.`status`, 
    rbl_list.specialistId,
    rbl_list.specialistUserId, 
    users_base.id AS userId,
    users_base.surname, 
    users_base.firstname, 
    users_base.patronymic
FROM
    rbl_list
    INNER JOIN
    users_base
    ON 
        rbl_list.userId = users_base.id
WHERE
    rbl_list.specialistId = :specialistId AND rbl_list.yearId = :yearId
ORDER BY surname, firstname, patronymic');
        $sth->bindValue(':specialistId', $this->specialistId, PDO::PARAM_INT);
        $sth->bindValue(':yearId', $this->container->get('session')->getSchoolYear()['id'], PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $users[] = [
                    'id' => (int)$a_row['id'],
                    'userId' => (int)$a_row['userId'],
                    'specialistId' => (int)$a_row['specialistId'],
                    'name' => $a_row['surname'] . ' ' . $a_row['firstname'] . ' ' . $a_row['patronymic'],
                    'status' => (int)$a_row['status'],
                    'specialistUserId' => (int)$a_row['specialistUserId'],
                    'className' => [],
                ];
                if(!in_array((int)$a_row['userId'], $usersIds)) {
                    $usersIds[] = (int)$a_row['userId'];
                }
            }
            $sth->closeCursor();
        }
        
        // Класс ученика
        if(count($usersIds)) {
            $sth = $this->db->prepare('SELECT
    users_classes.userId AS userId,
    UPPER(classes.`name`) AS className
FROM
    users_classes
    INNER JOIN
    classes
    ON 
        users_classes.classId = classes.id
WHERE
    classes.yearId = :yearId AND
    users_classes.userId IN (' . implode(', ', $usersIds) . ')');
            $sth->bindValue(':yearId', $this->container->get('session')->getSchoolYear()['id'], PDO::PARAM_INT);
            if($sth->execute()) {
                while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                    for($i = 0; $i < count($users); $i++) {
                        if($users[$i]['userId'] == $a_row['userId']) {
                            $users[$i]['className'][] = $a_row['className'];
                        }
                    }
                }
                $sth->closeCursor();
            }
        }
                
        $this->view->addAttribute('users', $users);
        
        // Специалисты
        $specialists = [];
        $specialists[0] = 'Не выбран';
        $sth = $this->db->prepare('SELECT
    users_base.surname, 
    users_base.firstname, 
    users_base.patronymic, 
    users_base.id
FROM
    users_base
    INNER JOIN
    users_groups
    ON 
        users_base.id = users_groups.userId
WHERE
    users_groups.groupId = :specialistId');
        $sth->bindValue(':specialistId', $this->specialistId, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $specialists[$a_row['id']] = $a_row['surname'] . ' ' . $a_row['firstname'] . ' ' . $a_row['patronymic'];
            }
            $sth->closeCursor();
        }
        $this->view->addAttribute('specialists', $specialists);
        
        $this->view->addAttribute('styles', ['main', 'ink', 'spc', 'ui-toggle']);
        
        return $this->view->render($response, 'spc/register.php');
    }
    
    public function reports($request, $response, $args) {
        $this->view->setLayout('layout.php');
        
        // Для выбора меню
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $this->view->addAttribute('routeName', $route->getName());
        $this->specialistId = $route->getArgument('id');
        $this->view->addAttribute('specialistId', $this->specialistId);
        // Дети
        $this->view->addAttribute('users', $this->getUsersTree());
        $this->view->addAttribute('styles', ['main', 'ink', 'spc']);
        $specialist = $this->getSpecialistInfo($this->specialistId);
        $this->view->addAttribute('specialist', $specialist);
        
        $this->view->addAttribute('title', 'АРМы :: ' . $specialist['name'] . ' :: Комиссия');
        
        return $this->view->render($response, 'spc/reports.php');
    }
}
