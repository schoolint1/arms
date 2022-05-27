<?php

namespace App\Components;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Psr\Container\ContainerInterface;

class PermissionMiddleware {
    
    protected $container;
    protected $db;
    protected $pref;


    public function __construct(ContainerInterface $container, string $pref) {
        $this->container = $container;
        $this->pref = $pref;
        $this->db = $this->container->get('db');
    }
    
    public function __invoke(Request $request, RequestHandler $handler)
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        if(empty($route)) { 
            throw new NotFoundException($request, $response);
        }
        if(!$this->container->get('session')->status($this->pref)) {
            $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
            $response = $responseFactory->createResponse(200);
            $view = $this->container->get('view');
            $view->addAttribute('container', $this->container);
            $view->addAttribute('title', 'АРМы :: Доступ запрещен');
            $view->addAttribute('styles', 'cover');
            $view->setLayout('layout_login.php');
            return $view->render($response, 'access_denied.php');
        } 

        return $handler->handle($request);
    }
    
}
