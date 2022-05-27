<?php

namespace App\API\cfg;

use Psr\Container\ContainerInterface;
use PDO;

class Years {
    protected $container;
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $container->get('db');
    }
    
    public function insert($message_in) {
        $yearBegindate = $message_in['params']['begindate'];
        $yearName = $message_in['params']['name'];
        
        $sth = $this->db->prepare('INSERT INTO years(`name`, begindate) VALUES(:name, :begindate)');
        $sth->bindValue(':begindate', $yearBegindate, PDO::PARAM_STR);
        $sth->bindValue(':name', $yearName, PDO::PARAM_STR);
        if($sth->execute()) {
            return [
                'result' => [
                    'status' => 'ok',
                    'year' => [
                        'id' => $this->db->lastInsertId(),
                        'name' => $yearName,
                        'begindate' => $yearBegindate
                    ]
                ]
            ];
        }
        return [
            'error' => [
                'code' => -32603,
                'message' => 'Internal error'
            ]
        ];
    }
    
    public function update($message_in) {
        $yearId = (int)$message_in['params']['id'];
        $yearBegindate = $message_in['params']['begindate'];
        $yearName = $message_in['params']['name'];
        
        $sth = $this->db->prepare('UPDATE years SET begindate = :begindate, `name` = :name WHERE id = :id');
        $sth->bindValue(':id', $yearId, PDO::PARAM_INT);
        $sth->bindValue(':begindate', $yearBegindate, PDO::PARAM_STR);
        $sth->bindValue(':name', $yearName, PDO::PARAM_STR);
        if($sth->execute()) {
            return [
                'result' => [
                    'status' => 'ok'
                ]
            ];
        }
        return [
            'error' => [
                'code' => -32603,
                'message' => 'Internal error'
            ]
        ];
    }
    
    public function delete($message_in) {
        $yearId = (int)$message_in['params']['id'];

        $sth = $this->db->prepare('DELETE FROM years WHERE id = :id');
        $sth->bindValue(':id', $yearId, PDO::PARAM_INT);
        if($sth->execute()) {
            return [
                'result' => [
                    'status' => 'ok',
                    'groups' => $this->getGroupsTree()
                ]
            ];
        }
        return [
            'error' => [
                'code' => -32603,
                'message' => 'Internal error'
            ]
        ];
    }
}
