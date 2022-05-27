<?php

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Slim\Routing\RouteContext;
use PDO;

class PlanController {
    protected $container;
    protected $view;
    protected $db;
    
    use TraintUsers;
    use TraintClasses;
    use TraintSpecialists;
    
    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->db = $this->container->get('db');
        $this->view = $this->container->get('view');
        $this->view->addAttribute('container', $this->container);
        $this->view->addAttribute('navbar', 'pln/navbar.php');
    }
    
    public function index($request, $response, $args) {
        $this->view->setLayout("layout.php");
        $this->view->addAttribute('title', 'АРМы :: Расписание');
        // Для выбора меню
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $this->view->addAttribute('routeName', $route->getName());
        
        $this->view->addAttribute('users', $this->getUsersTree());
        $this->view->addAttribute('styles', ['main', 'ink', 'pln']);
        
        $schoolYearWeekBegin = $this->container->get('session')->getSchoolYear()['begindate'];
        $schoolYearWeeks = [];
        $schoolYearWeekNumber = 1;
        $currentWeekNumber = 1;
        while($schoolYearWeekBegin <= $this->container->get('session')->getSchoolYear()['enddate']) {
            $dayNumberOfWeek = $schoolYearWeekBegin->format('N');
            $schoolYearWeekEnd = clone $schoolYearWeekBegin;
            $schoolYearWeekEnd->modify('+' . (7 - $dayNumberOfWeek) . ' days');
            
            if($this->container->get('session')->getDate() >= $schoolYearWeekBegin &&
               $this->container->get('session')->getDate() <= $schoolYearWeekEnd) {
                $currentWeekNumber = $schoolYearWeekNumber;
            }
            
            $schoolYearWeeks[$schoolYearWeekNumber] = [
                'begin' => $schoolYearWeekBegin->format('d.m.Y'),
                'end' => $schoolYearWeekEnd->format('d.m.Y'),
                'beginSQL' => $schoolYearWeekBegin->format('Y-m-d'),
                'endSQL' => $schoolYearWeekEnd->format('Y-m-d'),
            ];
            
            $schoolYearWeekBegin->modify('+' . (8 - $dayNumberOfWeek) . ' days');
            $schoolYearWeekNumber += 1;
        }
        $this->view->addAttribute('weeks', $schoolYearWeeks);
        $this->view->addAttribute('weekNumber', $currentWeekNumber);
        // Специалисты
        $this->view->addAttribute('specialists', $this->getSpecialists());
        return $this->view->render($response, 'pln/index.php');
    }
    
    public function addUser($request, $response, $args) {
        $this->view->setLayout("layout.php");
        $this->view->addAttribute('title', 'АРМы :: Добавить расписание для учеников');
         // Для выбора меню
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $this->view->addAttribute('routeName', $route->getName());
        
        $this->view->addAttribute('users', $this->getUsersTree());
        // Специалисты
        $this->view->addAttribute('specialists', $this->getSpecialists());
        $this->view->addAttribute('styles', ['main', 'ink', 'pln']);
        return $this->view->render($response, 'pln/adduser.php');
    }
    
    public function addClass($request, $response, $args) {
        $this->view->setLayout("layout.php");
        $this->view->addAttribute('title', 'АРМы :: Добавить расписание для классов');
         // Для выбора меню
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $this->view->addAttribute('routeName', $route->getName());
        
        $this->view->addAttribute('classes', $this->getClassesTree());
        // Специалисты
        $this->view->addAttribute('specialists', $this->getSpecialists());
        $this->view->addAttribute('styles', ['main', 'ink', 'pln']);
        return $this->view->render($response, 'pln/addclass.php');
    }
}
