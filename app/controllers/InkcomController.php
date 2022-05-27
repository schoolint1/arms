<?php

namespace App\Controllers;
use Psr\Container\ContainerInterface;
use Slim\Views\PhpRenderer;
use PDO;

class InkcomController
{
    protected $container;
    protected $view;
    protected $db;
    
    use TraintUsers;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->db = $this->container->get('db');
        $this->view = $this->container->get('view');
        $this->view->addAttribute('container', $this->container);
        $this->view->addAttribute('navbar', 'ink/navbar.php');
    }

    public function index($request, $response, $args) {
        $this->view->setLayout("layout.php");
        $this->view->addAttribute('title', 'АРМы :: Комиссия');
        
        // Дети
        $users = $this->getUsersTree();
        
        // Варианты
        $variants = [];
        $sth = $this->db->prepare('SELECT
id,
parameterId,
val
FROM ink_variants');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $variants[$a_row['id']] = [
                    'parameterId' => $a_row['parameterId'],
                    'val' => $a_row['val'],
                ];
            }
            $sth->closeCursor();
        }
        // Группы в комиссии
        $groupsSpecialists = [];
        $specialists = [];
        $sth = $this->db->prepare('SELECT
users_base.surname,
users_base.firstname,
users_base.patronymic,
users_base.id,
ink_commission_group_access.commissionGroupId
FROM
ink_commission_group_access
INNER JOIN groups ON ink_commission_group_access.groupId = groups.id
INNER JOIN users_groups ON users_groups.groupId = groups.id
INNER JOIN users_base ON users_groups.userId = users_base.id');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if(!array_key_exists($a_row['id'], $specialists)) {
                    $specialists[$a_row['id']] = [
                        'surname' => $a_row['surname'],
                        'firstname' => $a_row['firstname'],
                        'patronymic' => $a_row['patronymic'],
                    ];
                }
                if(array_key_exists($a_row['commissionGroupId'], $groupsSpecialists)) {
                    if(!in_array($a_row['id'], $groupsSpecialists[$a_row['commissionGroupId']])) {
                        $groupsSpecialists[$a_row['commissionGroupId']][] = (int)$a_row['id'];
                    }
                } else {
                    $groupsSpecialists[$a_row['commissionGroupId']] = [(int)$a_row['id']];
                }
            }
            $sth->closeCursor();
        }

        $this->view->addAttribute('styles', 'ink');
        $this->view->addAttribute('users', $users);
        $this->view->addAttribute('variants', $variants);
        $this->view->addAttribute('specialists', $specialists);
        $this->view->addAttribute('groupsSpecialists', $groupsSpecialists);
        return $this->view->render($response, 'ink/index.php');
    }

    public function commissions($request, $response, $args) {
        $this->view->setLayout("layout.php");
        $this->view->addAttribute('title', 'АРМы :: Комиссия');
       
        $commissions = [];
        $sth = $this->db->prepare('SELECT
years.id,
years.`name`,
ink_commissions.isFirstLock,
ink_commissions.firstDate,
ink_commissions.isSecondLock,
ink_commissions.secondDate,
ink_commissions.isThirdLock,
ink_commissions.thirdDate
FROM
years
LEFT JOIN ink_commissions ON years.id = ink_commissions.id
WHERE years.id <= :yearId
ORDER BY years.id DESC 
LIMIT 10');
        $sth->bindValue(':yearId', $this->container->get('session')->getSchoolYear()['id'], PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $commissions[] = [
                    'id' => $a_row['id'],
                    'name' => $a_row['name'],
                    'isCreate' => ($a_row['isFirstLock'] == null)?false:true,
                    'isFirstLock' => $a_row['isFirstLock']?true:false,
                    'firstDate' => $a_row['firstDate'],
                    'isSecondLock' => $a_row['isSecondLock']?true:false,
                    'secondDate' => $a_row['secondDate'],
                    'isThirdLock' => $a_row['isThirdLock']?true:false,
                    'thirdDate' => $a_row['thirdDate'],
                ];
            }
            $sth->closeCursor();
        }
        //krsort($commissions);
        $this->view->addAttribute('commissions', $commissions);
        return $this->view->render($response, 'ink/coms.php');
    }

    public function classes($request, $response, $args) {
        $this->view->setLayout("layout.php");
        $this->view->addAttribute('title', 'АРМы :: Классы');
        
        // Классы
        $classes = [];
        $sth = $this->db->prepare('SELECT id, `name`, parallel
FROM classes
WHERE yearId = :yearId
ORDER BY parallel, `name`');
        $sth->bindValue(':yearId', $this->container->get('session')->getSchoolYear()['id'], PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if(!array_key_exists($a_row['parallel'], $classes)) {
                    $classes[$a_row['parallel']] = [
                        'check' => false,
                        'classes' => [],
                    ];
                }
                $classes[$a_row['parallel']]['classes'][$a_row['id']] = [
                    'id' => $a_row['id'],
                    'name' => $a_row['name'],
                    'isSelected' => false,
                ];
            }
            $sth->closeCursor();
        }
        $this->view->addAttribute('styles', 'ink');
        $this->view->addAttribute('classes', $classes);
        return $this->view->render($response, 'ink/classes.php');
    }

    public function recom($request, $response, $args) {
        $this->view->setLayout("layout.php");
        $this->view->addAttribute('title', 'АРМы :: Рекомендации');
        
        // Дети
        $users = [];
        $sth = $this->db->prepare('SELECT
users_base.id,
users_base.surname,
users_base.firstname,
users_base.patronymic,
users_base.gender,
users_base.birthday,
classes.`name` AS className,
classes.parallel
FROM
users_base
INNER JOIN users_classes ON users_classes.userId = users_base.id
INNER JOIN classes ON users_classes.classId = classes.id
WHERE
classes.yearId = :yearId
ORDER BY classes.parallel, classes.`name`, users_base.surname, users_base.firstname');
        $sth->bindValue(':yearId', $this->container->get('session')->getSchoolYear()['id'], PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if(!array_key_exists($a_row['parallel'], $users)) {
                    $users[$a_row['parallel']] = [
                        'check' => false,
                        'classes' => [],
                    ];
                }
                if(!array_key_exists($a_row['className'], $users[$a_row['parallel']]['classes'])) {
                    $users[$a_row['parallel']]['classes'][$a_row['className']] = [
                        'check' => false,
                        'users' => [],
                    ];
                }
                $users[$a_row['parallel']]['classes'][$a_row['className']]['users'][] = [
                    'id' => $a_row['id'],
                    'name' => $a_row['surname'] . ' ' . $a_row['firstname'] . ' ' . $a_row['patronymic'],
                    'gender' => $a_row['gender'],
                    'birthday' => $a_row['birthday'],
                    'isSelected' => false,
                ];
            }
            $sth->closeCursor();
        }

        $this->view->addAttribute('styles', 'ink');
        $this->view->addAttribute('users', $users);
        return $this->view->render($response, 'ink/recom.php');
    }
}