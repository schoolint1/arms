<?php

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Slim\Routing\RouteContext;
use PDO;

class ConfigController {
    protected $container;
    protected $view;
    protected $db;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->db = $this->container->get('db');
        $this->view = $this->container->get('view');
        $this->view->addAttribute('container', $this->container);
        $this->view->addAttribute('navbar', 'cfg/navbar.php');
    }
    
    use TraintGroups;
    use TraintClasses;
    use TraintAccess;
    use TraintYears;

    public function personal($request, $response, $args) {
        $this->view->setLayout('layout.php');
        $this->view->addAttribute('title', 'АРМы :: Пользователи');
        
        // Для выбора меню
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $this->view->addAttribute('routeName', $route->getName());
        
        $this->view->addAttribute('styles', ['main', 'cfg']);
        
        $this->view->addAttribute('groups', $this->getGroupsTree());
        
        return $this->view->render($response, 'cfg/personal.php');
    }
    
    public function personalEdit($request, $response, $args) {
        $this->view->setLayout('layout.php');
        $this->view->addAttribute('title', 'АРМы :: Редактировать пользователя');
        
        // Для выбора меню
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $this->view->addAttribute('routeName', $route->getName());
        
        $this->view->addAttribute('styles', ['main', 'cfg']);
        
        $this->view->addAttribute('groups', $this->getGroupsTree());
        
        $id = $route->getArgument('id');
        // Пользователь
        $user = [];
        $sth = $this->db->prepare('SELECT id, surname, firstname, patronymic, gender, birthday FROM users_base WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $user = [
                    'id' => (int)$a_row['id'],
                    'surname' => $a_row['surname'],
                    'firstname' => $a_row['firstname'],
                    'patronymic' => $a_row['patronymic'],
                    'gender' => $a_row['gender'],
                    'birthday' => $a_row['birthday']
                ];
            } else {
                $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
                $response = $responseFactory->createResponse(200);
                $view = $this->container->get('view');
                $view->addAttribute('container', $this->container);
                $view->addAttribute('title', 'АРМы :: Доступ запрещен');
                $view->addAttribute('styles', 'cover');
                $view->setLayout('layout_login.php');
                return $view->render($response, 'access_denied.php');
            }
            $sth->closeCursor();
        }
        $user['groups'] = $this->getGroupsForUser($id);
        $user['users'] = $this->getAccessForUser($id);
        $user['classes'] = $this->getClassesForUser($id);
        $this->view->addAttribute('user', $user);
        
        $this->view->addAttribute('classes', $this->getClassesForYear($this->container->get('session')->getSchoolYear()['id']));
        
        return $this->view->render($response, 'cfg/personal_edit.php');
    }
    
    public function positions($request, $response, $args) {
        $this->view->setLayout('layout.php');
        $this->view->addAttribute('title', 'АРМы :: Должности');
        
        // Для выбора меню
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $this->view->addAttribute('routeName', $route->getName());
        
        $this->view->addAttribute('styles', ['main', 'cfg']);
        
        $this->view->addAttribute('groups', $this->getGroupsTree());
        
        // Список универсальных АРМ специалистов
        $specialists = [];
        $sth = $this->db->prepare('SELECT id FROM vcm_specialists');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $specialists[] = (int)$a_row['id'];
            }
            $sth->closeCursor();
        }
        $this->view->addAttribute('specialists', $specialists);
        
        // Список доступа
        $this->view->addAttribute('accessList', $this->getAccess());
        
        return $this->view->render($response, 'cfg/positions.php');
    }
    
    public function years($request, $response, $args) {
        $this->view->setLayout('layout.php');
        $this->view->addAttribute('title', 'АРМы :: Учебные года');
        
        // Для выбора меню
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $this->view->addAttribute('routeName', $route->getName());
        
        $this->view->addAttribute('styles', ['main', 'cfg']);
        
        // Учебные годы
//        $years = [];
//        $sth = $this->db->prepare('SELECT id, name, begindate FROM years ORDER BY begindate');
//        if($sth->execute()) {
//            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
//                $years[] = [ 
//                    'id' => (int)$a_row['id'],
//                    'name' => $a_row['name'],
//                    'begindate' => $a_row['begindate']
//                ];
//            }
//            $sth->closeCursor();
//        }
        $this->view->addAttribute('years', $this->getAllYears());
        
        return $this->view->render($response, 'cfg/years.php');
    }
    
    public function classes($request, $response, $args) {
        $this->view->setLayout('layout.php');
        $this->view->addAttribute('title', 'АРМы :: Классы');
        
        // Для выбора меню
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $this->view->addAttribute('routeName', $route->getName());
        
        $this->view->addAttribute('styles', ['main', 'cfg']);
        
        list($years, $classes) = $this->getAllClasses();
        $this->view->addAttribute('classes', $classes);
        $this->view->addAttribute('years', $this->getAllYears());
        
        return $this->view->render($response, 'cfg/classes.php');
    }
}
