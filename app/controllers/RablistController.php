<?php

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Slim\Routing\RouteContext;
use PDO;

class RablistController {
    protected $container;
    protected $view;
    protected $db;
    
    use TraintSpecialists;
    use TraintClasses;
    
    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->db = $this->container->get('db');
        $this->view = $this->container->get('view');
        $this->view->addAttribute('container', $this->container);
        $this->view->addAttribute('navbar', 'rbl/navbar.php');
    }
    
    public function index($request, $response, $args) {
        $this->view->setLayout("layout.php");
        $this->view->addAttribute('title', 'АРМы :: Список в работу');

        $this->view->addAttribute('styles', ['main', 'ink']);
        // Классы
        $this->view->addAttribute('classes', $this->getClassesTree());
        // Специалисты
        $this->view->addAttribute('specialists', $this->getSpecialists());
        return $this->view->render($response, 'rbl/index.php');
    }
}
