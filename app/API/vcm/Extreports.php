<?php

namespace App\API\vcm;

use Psr\Container\ContainerInterface;
use PDO;

class Extreports {
    
    protected $container;
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $this->container->get('db');
    }
    
    public function get($message_in) {
        $userId = (int)$message_in['params']['userId'];
        $reports = [];
        $reportsId = [];
        // Список заключений
        $sth = $this->db->prepare('SELECT id, docNumber, docDate FROM vcm_extreports WHERE userId = :userId ORDER BY docDate DESC');
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $reportsId[] = $a_row['id'];
                $reports[] = [
                    'id' => $a_row['id'],
                    'docNumber' => $a_row['docNumber'],
                    'docDate' => $a_row['docDate'],
                    'specialistsId' => []
                ];
            }
            $sth->closeCursor();
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }
        if(count($reportsId)) {
            // Список специалистов к заключению
            $sth = $this->db->prepare('SELECT reportId, specialistId FROM vcm_extreports_items WHERE reportId IN(' . join(', ', $reportsId) . ')');
            if($sth->execute()) {
                while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                    foreach ($reports as &$value) {
                        if($value['id'] == $a_row['reportId']) {
                            $value['specialistsId'][] = $a_row['specialistId'];
                            break;
                        }
                    }
                }
                $sth->closeCursor();
            }
        }
        
        return [
            'result' => [
                'status' => 'ok',
                'reports' => $reports
            ]
        ];
    }
    
    public function add($message_in) {
        $userId = (int)$message_in['params']['userId'];
        $docNumber = (string)$message_in['params']['docNumber'];
        $docDate = (string)$message_in['params']['docDate'];
        
        
        $sth = $this->db->prepare('INSERT INTO vcm_extreports(userId, docNumber, docDate) VALUES(:userId, :docNumber, STR_TO_DATE(:docDate, \'%Y-%m-%d\'))');
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':docNumber', $docNumber, PDO::PARAM_STR);
        $sth->bindValue(':docDate', $docDate, PDO::PARAM_STR);
        
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
    
    public function update($message_in) {
        $id = (int)$message_in['params']['id'];
        $docNumber = (string)$message_in['params']['docNumber'];
        $docDate = (string)$message_in['params']['docDate'];
        
        
        $sth = $this->db->prepare('UPDATE vcm_extreports SET docNumber = :docNumber, docDate = STR_TO_DATE(:docDate, \'%Y-%m-%d\') WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        $sth->bindValue(':docNumber', $docNumber, PDO::PARAM_STR);
        $sth->bindValue(':docDate', $docDate, PDO::PARAM_STR);
        
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
        
        $sth = $this->db->prepare('DELETE FROM vcm_extreports WHERE id = :id');
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
