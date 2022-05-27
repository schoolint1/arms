<?php

namespace App\Controllers;
use Psr\Container\ContainerInterface;

class LoginController
{
    protected $container;
    protected $view;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->view = $this->container->get("view");
        $this->view->addAttribute('styles', 'signin');
    }
    
    private function getParam(array $params, string $name, $default) {
        if(array_key_exists($name, $params)) {
            return $params[$name];
        }
        return $default;
    }

    public function login($request, $response, $args) {
        $this->view->setLayout("layout_login.php");
        $this->view->addAttribute('title', 'АРМы :: Вход');
        $errorMessage = null;
        if($request->getMethod() == 'POST') {
            $params = (array)$request->getParsedBody();
            if($this->container->get('session')->login($this->getParam($params, 'username', ''), $this->getParam($params, 'password', ''))) {
                return $response->withHeader('Location', '/')
                        ->withStatus(302);
            } else {
                $errorMessage = 'Не верный логин или пароль';
            }
        }

        return $this->view->render($response, 'login/login.php', ['errorMessage' => $errorMessage]);
    }

    public function logout($request, $response, $args) {
        $this->container->get('session')->logout();
        return $response->withHeader('Location', '/login')
                ->withStatus(302);
    }
}