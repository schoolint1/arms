<?php

namespace App\Controllers;
use Psr\Container\ContainerInterface;
use PDO;

class ApiController
{
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    
    private function access($modul) {
        $session = $this->container->get('session');
        if($session->isLogin()) {
            if($session->status($modul)) {
                return true;
            }
        }
        return false;
    }

    public function index($request, $response, $args) {
        $message_in = $request->getParsedBody();

        $message_out = [
            'jsonrpc' => '2.0',
            'id' => null
        ];

        if(!is_array($message_in)) {
            $message_out['error'] = [
                'code' => -32700,
                'message' => 'Parse error'
            ];
            $response->getBody()->write(json_encode($message_out));
            return $response->withHeader('Content-Type', 'application/json');
        }

        // Заполнение поля ID
        if(array_key_exists('id', $message_in)) {
            $message_out['id'] = $message_in['id'];
        } else {
            $message_out['id'] = null;
        }

        if(array_key_exists('method', $message_in)) {
            list($acname, $clname, $clmethod) = explode('_', $message_in['method']);
            $clname = '\\App\\API\\' . $acname . '\\' . $clname;
            if(class_exists($clname)) {
                if(($acname == 'all') || ($this->access($acname))) {
                    $api = new $clname($this->container);
                    if(method_exists($api, $clmethod)) {
                        $message_out = array_merge($message_out, $api->$clmethod($message_in));
                    } else {
                        $message_out['error'] = [
                            'code' => -32601,
                            'message' => 'Method not found'
                        ];
                    }
                } else {
                    $message_out['error'] = [
                        'code' => -32003,
                        'message' => 'Нет доступа'
                    ];
                }
            } else {
                $message_out['error'] = [
                    'code' => -32601,
                    'message' => 'Method not found'
                ];
            }
        } else {
            $message_out['error'] = [
                'code' => -32700,
                'message' => 'Parse error'
            ];
        }
        $response->getBody()->write(json_encode($message_out));
        return $response->withHeader('Content-Type', 'application/json');
    }
}