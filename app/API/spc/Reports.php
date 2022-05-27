<?php

namespace App\API\spc;

use Psr\Container\ContainerInterface;
use PDO;

class Reports {
    
    protected $container;
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $this->container->get('db');
    }
    
    public function get($message_in) {
        $userId = (int)$message_in['params']['userId'];
        $specialistId = (int)$message_in['params']['specialistId'];
        $reports = [];
        $examsUserIds = [];
        // Список заключений
        $sth = $this->db->prepare('SELECT id, isNeed, docDate, val, examUserId FROM spc_increports WHERE userId = :userId AND specialistId = :specialistId ORDER BY docDate DESC');
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':specialistId', $specialistId, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $examUserId = (int)$a_row['examUserId'];
                $reports[$a_row['id']] = [
                    'id' => (int)$a_row['id'],
                    'isNeed' => (int)$a_row['isNeed'],
                    'docDate' => $a_row['docDate'],
                    'val' => $a_row['val'],
                    'examUserId' => $examUserId,
                    'examUserName' => '',
                ];
                if($examUserId && !in_array($examUserId, $examsUserIds)) {
                    $examsUserIds[] = $examUserId;
                }
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
        // Получить имена специолистов проведших осмотры
        if(count($examsUserIds)) {
            $sth = $this->db->prepare('SELECT id, surname, firstname, patronymic FROM users_base WHERE id IN(' . implode(', ', $examsUserIds) . ')');
            if($sth->execute()) {
                while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                    foreach (array_keys($reports) as $id) {
                        if($reports[$id]['examUserId'] == $a_row['id']) {
                            $reports[$id]['examUserName'] = $a_row['surname'] . ' ' . (mb_strlen($a_row['firstname']) ? mb_substr($a_row['firstname'], 0, 1) . '.':'') . (mb_strlen($a_row['patronymic']) ? mb_substr($a_row['patronymic'], 0, 1) . '.':'');
                        }
                    }
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
        
        $specialistsConfig = $this->container->get('specialistsConfig');
        if(array_key_exists($specialistId, $specialistsConfig)) {
            $specialistsConfig = $specialistsConfig[$specialistId];
        } else {
            $specialistsConfig = null;
        }
        $reportsId = array_keys($reports);
        if(count($reportsId) && ($specialistsConfig != null)) {
            $columns = [];
            foreach ($specialistsConfig['columns'] as $value) {
                array_push($columns, $value['name']);
            }
            // Данные из дополнительной таблицы
            $sth = $this->db->prepare('SELECT id, '. join(', ', $columns) . ' FROM ' . $specialistsConfig['tableName'] . ' WHERE id IN (' . join(', ', $reportsId). ')');
            if($sth->execute()) {
                while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                    if(array_key_exists($a_row['id'], $reports)) {
                        foreach($columns AS $column) {
                            $reports[$a_row['id']][$column] = $a_row[$column];
                        }
                    }
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
        
        // Выписка из Городской комиссии
        $extreportId = null;
        $sth = $this->db->prepare('SELECT t1.id
FROM
vcm_extreports AS t1
WHERE 
t1.docDate = (SELECT MAX(t2.docDate) FROM vcm_extreports AS t2 WHERE t2.userId = t1.userId) AND t1.userId = :userId');
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
        
        $extreport = [];
        if($extreportId != null) {
            $sth = $this->db->prepare('SELECT
    groups.`name` AS specialistName, 
    vcm_extreports_items.isNeed, 
    vcm_extreports_items.recom
FROM
    vcm_extreports_items
    INNER JOIN
    groups
    ON 
        vcm_extreports_items.specialistId = groups.id
WHERE
    vcm_extreports_items.reportId = :reportId');
            $sth->bindValue(':reportId', $extreportId, PDO::PARAM_INT);
            if($sth->execute()) {
                while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                    $extreport[] = [
                        'specialistName' => $a_row['specialistName'],
                        'isNeed' => $a_row['isNeed'],
                        'recom' => $a_row['recom'],
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
        }
        
        
        return [
            'result' => [
                'status' => 'ok',
                'reports' => array_values($reports),
                'extreport' => $extreport
            ]
        ];
    }
    
    public function add($message_in) {
        $userId = (int)$message_in['params']['userId'];
        $param = $message_in['params']['param'];
        $specialistId = (int)$message_in['params']['specialistId'];
        if(!array_key_exists('examUserId', $message_in['params'])) {
            return [
                'error' => [
                    'code' => -32602,
                    'message' => 'Invalid params'
                ]
            ];
        }
        $examUserId = (int)$message_in['params']['examUserId'];
        $yearId = $this->container->get('session')->getSchoolYear()['id'];
        
        $this->db->beginTransaction();
        $sth = $this->db->prepare('INSERT INTO spc_increports(yearId, specialistId, userId, isNeed, docDate, val, examUserId) VALUES(:yearId, :specialistId, :userId, :isNeed, STR_TO_DATE(:docDate, \'%Y-%m-%d\'), :val, :examUserId)');
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        $sth->bindValue(':specialistId', $specialistId, PDO::PARAM_INT);
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':isNeed', $param['isNeed'], PDO::PARAM_INT);
        $sth->bindValue(':docDate', $param['docDate'], PDO::PARAM_STR);
        $sth->bindValue(':val', $param['val'], PDO::PARAM_STR);
        $sth->bindValue(':examUserId', $examUserId, PDO::PARAM_INT);
        
        if($sth->execute()) {
            $sth->closeCursor();
            $id = $this->db->lastInsertId();
            
            $specialistsConfig = $this->container->get('specialistsConfig');
            if(array_key_exists($specialistId, $specialistsConfig)) {
                $specialistsConfig = $specialistsConfig[$specialistId];
            } else {
                $specialistsConfig = null;
            }
            if(($id != false) && ($specialistsConfig != null) && array_key_exists('columns', $specialistsConfig) && (count($specialistsConfig['columns']) > 0)) {
                $columns = [];
                $makrs = [];
                foreach ($specialistsConfig['columns'] as $value) {
                    array_push($columns, $value['name']);
                    array_push($makrs, ':' . $value['name']);
                }

                $columnsCount = count($specialistsConfig['columns']);
                $sql = 'INSERT INTO ' . $specialistsConfig['tableName'] . '(id, ';
                for($i = 0; $i < $columnsCount; $i++) {
                    $sql .= $specialistsConfig['columns'][$i]['name'];
                    if($i + 1 < $columnsCount) {
                        $sql .= ', ';
                    }
                }
                $sql .= ') VALUES (:id, ';
                for($i = 0; $i < $columnsCount; $i++) {
                    $sql .= ':' . $specialistsConfig['columns'][$i]['name'];
                    if($i + 1 < $columnsCount) {
                        $sql .= ', ';
                    }
                }
                $sql .= ') ON DUPLICATE KEY UPDATE ';
                for($i = 0; $i < $columnsCount; $i++) {
                    $sql .= $specialistsConfig['columns'][$i]['name'] . '= :' . $specialistsConfig['columns'][$i]['name'];
                    if($i + 1 < $columnsCount) {
                        $sql .= ', ';
                    }
                }
                $sth = $this->db->prepare($sql);
                $sth->bindValue(':id', $id, PDO::PARAM_INT);
                foreach ($specialistsConfig['columns'] as $value) {
                    $sth->bindValue(':' . $value['name'], $param[$value['name']], $value['type']);
                }
                if($sth->execute()) {
                    $sth->closeCursor();
                } else {
                    $this->db->rollBack();
                    return [
                        'error' => [
                            'code' => -32603,
                            'message' => 'Internal error'
                        ]
                    ];
                }
            }
        } else {
            $this->db->rollBack();
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }
        
        $this->db->commit();
        return [
            'result' => [
                'status' => 'ok'
            ]
        ];
    }
    
    public function update($message_in) {
        $userId = (int)$message_in['params']['userId'];
        $param = $message_in['params']['param'];
        $specialistId = (int)$message_in['params']['specialistId'];
        
        $this->db->beginTransaction();
        $sth = $this->db->prepare('UPDATE spc_increports SET docDate = STR_TO_DATE(:docDate, \'%Y-%m-%d\'), isNeed = :isNeed, val = :val WHERE id = :id');
        $sth->bindValue(':id', $param['id'], PDO::PARAM_INT);
        $sth->bindValue(':isNeed', $param['isNeed'], PDO::PARAM_INT);
        $sth->bindValue(':docDate', $param['docDate'], PDO::PARAM_STR);
        $sth->bindValue(':val', $param['val'], PDO::PARAM_STR);
        
        if($sth->execute()) {
            $sth->closeCursor();
            
            $specialistsConfig = $this->container->get('specialistsConfig');
            if(array_key_exists($specialistId, $specialistsConfig)) {
                $specialistsConfig = $specialistsConfig[$specialistId];
            } else {
                $specialistsConfig = null;
            }
            if(($specialistsConfig != null) && array_key_exists('columns', $specialistsConfig) && (count($specialistsConfig['columns']) > 0)) {
                $columns = [];
                $makrs = [];
                foreach ($specialistsConfig['columns'] as $value) {
                    array_push($columns, $value['name']);
                    array_push($makrs, ':' . $value['name']);
                }

                $columnsCount = count($specialistsConfig['columns']);
                $sql = 'INSERT INTO ' . $specialistsConfig['tableName'] . '(id, ';
                for($i = 0; $i < $columnsCount; $i++) {
                    $sql .= $specialistsConfig['columns'][$i]['name'];
                    if($i + 1 < $columnsCount) {
                        $sql .= ', ';
                    }
                }
                $sql .= ') VALUES (:id, ';
                for($i = 0; $i < $columnsCount; $i++) {
                    $sql .= ':' . $specialistsConfig['columns'][$i]['name'];
                    if($i + 1 < $columnsCount) {
                        $sql .= ', ';
                    }
                }
                $sql .= ') ON DUPLICATE KEY UPDATE ';
                for($i = 0; $i < $columnsCount; $i++) {
                    $sql .= $specialistsConfig['columns'][$i]['name'] . '= :' . $specialistsConfig['columns'][$i]['name'];
                    if($i + 1 < $columnsCount) {
                        $sql .= ', ';
                    }
                }
                
                $sth = $this->db->prepare($sql);
                $sth->bindValue(':id', $param['id'], PDO::PARAM_INT);
                foreach ($specialistsConfig['columns'] as $value) {
                    $sth->bindValue(':' . $value['name'], $param[$value['name']], $value['type']);
                }
                if($sth->execute()) {
                    $sth->closeCursor();
                } else {
                    $this->db->rollBack();
                    return [
                        'error' => [
                            'code' => -32603,
                            'message' => 'Internal error'
                        ]
                    ];
                }
            }
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }
        
        $this->db->commit();
        return [
            'result' => [
                'status' => 'ok'
            ]
        ];
    }
    
    public function delete($message_in) {
        $id = (int)$message_in['params']['id'];
        
        $sth = $this->db->prepare('DELETE FROM spc_increports WHERE id = :id');
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
