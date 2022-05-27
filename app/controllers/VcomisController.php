<?php

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Slim\Routing\RouteContext;
use PDO;

class VcomisController {
    protected $container;
    protected $view;
    protected $db;
    
    use TraintUsers;
    use TraintSpecialists;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->db = $this->container->get('db');
        $this->view = $this->container->get('view');
        $this->view->addAttribute('container', $this->container);
        $this->view->addAttribute('navbar', 'vcm/navbar.php');
    }
    
    public function index($request, $response, $args) {
        $this->view->setLayout('layout.php');
        $this->view->addAttribute('title', 'АРМы :: Городская комиссия');
        
        // Свециалисты
        $this->view->addAttribute('specialists', $this->getSpecialists());
        // Дети
        $this->view->addAttribute('users', $this->getUsersTree());
        $this->view->addAttribute('styles', ['main', 'ink', 'vcomis']);
        
        return $this->view->render($response, 'vcm/index.php');
    }
    
    public function report($request, $response, $args) {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        
        $this->view->setLayout('layout.php');
        $this->view->addAttribute('title', 'АРМы :: Городская комиссия :: Заключение');
        
        $this->view->addAttribute('styles', ['main', 'ink', 'vcomis']);
        
        $reportId = $route->getArgument('id');
        $this->view->addAttribute('reportId', $reportId);
        
        $sth = $this->db->prepare('SELECT userId, docNumber, DATE_FORMAT(docDate, "%d.%m.%Y") AS docd FROM vcm_extreports WHERE id = :id');
        $sth->bindValue(':id', $reportId, PDO::PARAM_INT);
        $userId = 0;
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $userId = $a_row['userId'];
                $this->view->addAttribute('docNumber', $a_row['docNumber']);
                $this->view->addAttribute('docDate', $a_row['docd']);
            }
            $sth->closeCursor();
        }
        
        $sth = $this->db->prepare('SELECT surname, firstname, patronymic FROM users_base WHERE id = :userId');
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $this->view->addAttribute('name', $a_row['surname'] . ' ' . $a_row['firstname'] . ' ' . $a_row['patronymic']);
            }
            $sth->closeCursor();
        }
        // Свециалисты
        $this->view->addAttribute('specialists', $this->getSpecialists());
        
        // Записи
        $extreportItems = [];
        $sth = $this->db->prepare('SELECT id, isNeed, specialistId, recom
FROM vcm_extreports_items WHERE reportId = :reportId');
        $sth->bindValue(':reportId', $reportId, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $extreportItems[] = [
                    'id' => $a_row['id'],
                    'isNeed' => $a_row['isNeed'],
                    'specialistId' => $a_row['specialistId'],
                    'recom' => $a_row['recom']
                ];
            }
            $sth->closeCursor();
        }
        $this->view->addAttribute('extreportItems', $extreportItems);
        
        return $this->view->render($response, 'vcm/report.php');
    }
}
