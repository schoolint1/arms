<?php

namespace App\API\vcm;

use Psr\Container\ContainerInterface;
use PDO;

class ExtreportsItems {
    
    protected $container;
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $this->container->get('db');
    }
    
    public function get($message_in) {
        
    }
    
    public function add($message_in) {
        $reportId = (int)$message_in['params']['reportId'];
        $isNeed = (int)$message_in['params']['isNeed'];
        $specialistId = (int)$message_in['params']['specialistId'];
        $recom = (string)$message_in['params']['recom'];
        
        $sth = $this->db->prepare('INSERT INTO vcm_extreports_items(reportId, isNeed, specialistId, recom) VALUES(:reportId, :isNeed, :specialistId, :recom)');
        $sth->bindValue(':reportId', $reportId, PDO::PARAM_INT);
        $sth->bindValue(':isNeed', $isNeed, PDO::PARAM_INT);
        $sth->bindValue(':specialistId', $specialistId, PDO::PARAM_INT);
        $sth->bindValue(':recom', $recom, PDO::PARAM_STR);
        
        $id = null;
        if($sth->execute()) {
            $id = $this->db->lastInsertId();
            $sth->closeCursor();
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }
        return [
            'result' => [
                'status' => 'ok',
                'id' => $id
            ]
        ];
    }
    
    public function update($message_in) {
        $id = (int)$message_in['params']['id'];
        $isNeed = (int)$message_in['params']['isNeed'];
        $specialistId = (int)$message_in['params']['specialistId'];
        $recom = (string)$message_in['params']['recom'];;
        
        
        $sth = $this->db->prepare('UPDATE vcm_extreports_items SET isNeed = :isNeed, specialistId = :specialistId, recom = :recom WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        $sth->bindValue(':isNeed', $isNeed, PDO::PARAM_INT);
        $sth->bindValue(':specialistId', $specialistId, PDO::PARAM_INT);
        $sth->bindValue(':recom', $recom, PDO::PARAM_STR);;
        
        if($sth->execute()) {
            $sth->closeCursor();
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }
        return [
            'result' => [
                'status' => 'ok'
            ]
        ];
    }
    
    public function delete($message_in) {
        $id = (int)$message_in['params']['id'];
        
        $sth = $this->db->prepare('DELETE FROM vcm_extreports_items WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        
        if($sth->execute()) {
            $sth->closeCursor();
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }
        return [
            'result' => [
                'status' => 'ok'
            ]
        ];
    }
}
