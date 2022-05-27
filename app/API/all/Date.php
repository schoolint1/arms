<?php

namespace App\API\all;

use Psr\Container\ContainerInterface;

class Date {
    
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function change($message_in) { 
        $date = new \DateTime($message_in['params']['date'], new \DateTimeZone('GMT'));
        $this->container->get('session')->setDate($date->format('U'));
        return [
            'result' => [
                'status' => 'ok'
            ]
        ];
    }
}
