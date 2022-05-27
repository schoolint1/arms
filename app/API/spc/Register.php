<?php

namespace App\API\spc;

use Psr\Container\ContainerInterface;
use PDO;

class Register {
    protected $container;
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $this->container->get('db');
    }
    
    public function setStatus($message_in) {
        $rblListId = (int)$message_in['params']['rblListId'];
        $status = (int)$message_in['params']['status'];
        
        $sth = $this->db->prepare('UPDATE rbl_list SET status = :status WHERE id = :id');
        $sth->bindValue(':status', $status, PDO::PARAM_INT);
        $sth->bindValue(':id', $rblListId, PDO::PARAM_INT);
        
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
    
    public function setSpecialist($message_in) {
        $rblListId = (int)$message_in['params']['rblListId'];
        $specialistId = (int)$message_in['params']['specialistId'];
        
        $sth = $this->db->prepare('UPDATE rbl_list SET specialistUserId = :specialistUserId WHERE id = :id');
        $sth->bindValue(':specialistUserId', $specialistId, PDO::PARAM_INT);
        $sth->bindValue(':id', $rblListId, PDO::PARAM_INT);
        
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
    
    public function getUserInformation($message_in) {
        $userId = (int)$message_in['params']['userId'];
        $specialistId = (int)$message_in['params']['specialistId'];
        
        $result = '';
        
        $sth = $this->db->prepare('SELECT val FROM spc_increports WHERE userId = :userId AND specialistId = :specialistId AND yearId = :yearId ORDER BY docDate DESC');
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':specialistId', $specialistId, PDO::PARAM_INT);
        $sth->bindValue(':yearId', $this->container->get('session')->getSchoolYear()['id'], PDO::PARAM_INT);
        if($sth->execute()) {
            $result = '<strong>Школьный специалист:</strong><br>';
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $result .= $a_row['val'] . "<br>";
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
        
        $extreportId = null;
        $sth = $this->db->prepare('SELECT id
FROM vcm_extreports WHERE vcm_extreports.userId = :userId
ORDER BY docDate DESC');
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $extreportId = $a_row['id'];
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
        
        if($extreportId !== null) {
            $sth = $this->db->prepare('SELECT recom
FROM vcm_extreports_items WHERE reportId = :reportId AND specialistId = :specialistId');
            $sth->bindValue(':reportId', $extreportId, PDO::PARAM_INT);
            $sth->bindValue(':specialistId', $specialistId, PDO::PARAM_INT);
            if($sth->execute()) {
                $result .= '<strong>Городская комиссия:</strong><br>';
                while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                    $result .= $a_row['recom'] . '<br>';
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
        }

        return [
            'result' => [
                'status' => 'ok',
                'information' => $result,
            ]
        ];
    }
}
