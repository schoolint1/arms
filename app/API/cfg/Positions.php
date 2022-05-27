<?php

namespace App\API\cfg;

use Psr\Container\ContainerInterface;
use App\Controllers\TraintGroups;
use PDO;

class Positions {
    protected $container;
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $container->get('db');
    }
    
    use TraintGroups;
    
    public function setARM($message_in) {
        $groupId = (int)$message_in['params']['groupId'];
        $status = $message_in['params']['status'];
        
        $sth = null;
        if($status == 'insert') {
            $sth = $this->db->prepare('INSERT INTO vcm_specialists(id) VALUES(:id)');
        }
        if($status == 'delete') {
            $sth = $this->db->prepare('DELETE FROM vcm_specialists WHERE id = :id');
        }
        
        if($sth !== null) {
            $sth->bindValue(':id', $groupId, PDO::PARAM_INT);
            if($sth->execute()) {
                return [
                    'result' => [
                        'status' => 'ok'
                    ]
                ];
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
            'error' => [
                'code' => -32601,
                'message' => 'Method not found'
            ]
        ];
    }
    
    public function insert($message_in) {
        $groupParentId = (int)$message_in['params']['parentId'];
        $groupName = $message_in['params']['name'];
        
        $sth = $this->db->prepare('INSERT INTO groups(parentId, `name`) VALUES(:parentId, :name)');
        $sth->bindValue(':parentId', $groupParentId, PDO::PARAM_INT);
        $sth->bindValue(':name', $groupName, PDO::PARAM_STR);
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
    
    public function update($message_in) {
        $groupId = (int)$message_in['params']['id'];
        $groupParentId = (int)$message_in['params']['parentId'];
        $groupName = $message_in['params']['name'];
        
        $sth = $this->db->prepare('UPDATE groups SET parentId = :parentId, `name` = :name WHERE id = :id');
        $sth->bindValue(':id', $groupId, PDO::PARAM_INT);
        $sth->bindValue(':parentId', $groupParentId, PDO::PARAM_INT);
        $sth->bindValue(':name', $groupName, PDO::PARAM_STR);
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
    
    public function delete($message_in) {
        $groupId = (int)$message_in['params']['id'];
        
        // Проверка на связанных с должностью пользователей
        $sth = $this->db->prepare('SELECT COUNT(id) AS cnt FROM users_groups WHERE groupId = :id');
        $sth->bindValue(':id', $groupId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if($a_row['cnt'] > 0) {
                    return [
                        'result' => [
                            'status' => 'error',
                            'message' => 'Есть пользователи с должностью'
                        ]
                    ];
                }
            } else {
                return [
                    'error' => [
                        'code' => -32603,
                        'message' => 'Internal error'
                    ]
                ];
            }
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }
        
        // Удаление
        $sth = $this->db->prepare('DELETE FROM groups WHERE id = :id');
        $sth->bindValue(':id', $groupId, PDO::PARAM_INT);
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
    
    public function setModulAccess($message_in) {
        $groupId = (int)$message_in['params']['groupId'];
        $modul = $message_in['params']['modul'];
        $status = $message_in['params']['status'];
        
        $sth = null;
        if($status == 'insert') {
            $sth = $this->db->prepare('INSERT INTO access(groupId, modul) VALUES(:groupId, :modul)');
        }
        if($status == 'delete') {
            $sth = $this->db->prepare('DELETE FROM access WHERE groupId = :groupId AND modul = :modul');
        }
        
        if($sth !== null) {
            $sth->bindValue(':groupId', $groupId, PDO::PARAM_INT);
            $sth->bindValue(':modul', $modul, PDO::PARAM_STR);
            if($sth->execute()) {
                return [
                    'result' => [
                        'status' => 'ok',
                        'accessList' => $this->getAccess()
                    ]
                ];
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
            'error' => [
                'code' => -32601,
                'message' => 'Method not found'
            ]
        ];
    }
}
