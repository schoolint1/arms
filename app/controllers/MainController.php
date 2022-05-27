<?php

namespace App\Controllers;
use Psr\Container\ContainerInterface;

class MainController
{
    protected $container;
    protected $view;
    protected $db;
    
    use TraintSpecialists;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->db = $this->container->get('db');
        $this->view = $this->container->get('view');
        $this->view->addAttribute('container', $this->container);
    }

    public function index($request, $response, $args) {
        $this->view->setLayout("layout.php");
        $this->view->addAttribute('title', 'АРМы');
        if($this->container->get('session')->status()) {
            $this->view->addAttribute('styles', ['main']);
            // АРМы
            $this->view->addAttribute('specialists', $this->getSpecialists());
            $this->view->addAttribute('specialistsConfig', $this->container->get('specialistsConfig'));
            return $this->view->render($response, 'main/index.php');
        }
        return $response->withHeader('Location', '/login')
                ->withStatus(302);
    }
}