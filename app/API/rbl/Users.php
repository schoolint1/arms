<?php

namespace App\API\rbl;

use Psr\Container\ContainerInterface;
use PDO;

class Users {
    protected $container;
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $this->container->get('db');
    }
    
    public function get($message_in) {
        
        $classId = $message_in['params']['classId'];
        $yearId = $this->container->get('session')->getSchoolYear()['id'];
        
        // Список детей
        $users = [];
        $sth = $this->db->prepare('SELECT
users_base.id,
users_base.surname,
users_base.firstname,
users_base.patronymic,
users_base.gender,
users_base.birthday,
classes.`name` AS className,
classes.parallel
FROM
users_base
INNER JOIN users_classes ON users_classes.userId = users_base.id
INNER JOIN classes ON users_classes.classId = classes.id
WHERE
classes.id = :classId
ORDER BY classes.parallel, classes.`name`, users_base.surname, users_base.firstname');
        $sth->bindValue(':classId', $classId, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $users[(int)$a_row['id']] = [
                    'id' => $a_row['id'],
                    'name' => $a_row['surname'] . ' ' . $a_row['firstname'] . ' ' . $a_row['patronymic'],
//                    'gender' => $a_row['gender'],
//                    'birthday' => $a_row['birthday'],
                    'extreports' => [],
                    'increports' => [],
                    'slcspecialists' => [],
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
        
        $usersId = array_keys($users);

        // Список должностей
        $groups = [];
        $sth = $this->db->prepare('SELECT id, name FROM groups');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $groups[$a_row['id']] = $a_row['name'];
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
        // Последняя городская комиссия для ребенка
        $extreports = [];
        $sth = $this->db->prepare('SELECT t1.id, t1.userId
FROM
vcm_extreports AS t1
WHERE 
t1.docDate = (SELECT MAX(t2.docDate) FROM vcm_extreports AS t2 WHERE t2.userId = t1.userId) AND t1.userId IN (' . join(', ', $usersId) . ')');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $extreports[$a_row['id']] = $a_row['userId'];
                /*for($i = 0; $i < count($users); $i++) {
                    if($users[$i]['id'] == $a_row['userId']) {
                        $users[$i]['extreportId'] = $a_row['id'];
                        break;
                    }
                }*/
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
        $extreportsId = array_keys($extreports);
        // Заключения городской комиссии для ребёнка
        if(count($extreportsId)) {
            $sth = $this->db->prepare('SELECT reportId, isNeed, specialistId
FROM vcm_extreports_items
WHERE reportId IN (' . join(', ', $extreportsId) . ')');
            if($sth->execute()) {
                while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                   $userId = $extreports[$a_row['reportId']];
                   $users[$userId]['extreports'][] = [
                       'isNeed' => $a_row['isNeed'] > 0?true:false,
                       'specialist' => $groups[$a_row['specialistId']]
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
        // Заключения внутренних специалистов (логопед)
        $increports = [];
        $sth = $this->db->prepare('SELECT userId, isNeed, specialistId
FROM spc_increports AS t1 WHERE t1.docDate = (SELECT MAX(t2.docDate) FROM spc_increports AS t2 WHERE t2.userId = t1.userId) AND t1.userId IN (' . join(', ', $usersId) . ') AND t1.yearId = :yearId');
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $userId = (int)$a_row['userId'];
                if(array_key_exists($userId, $users)) {
                    $users[$userId]['increports'][] = [
                        'isNeed' => $a_row['isNeed'] > 0?true:false,
                        'specialist' => $groups[$a_row['specialistId']]
                    ];
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
        // Выбранные специалисты
        $sth = $this->db->prepare('SELECT userId, specialistId FROM rbl_list WHERE yearId = :yearId AND userId IN (' . join(', ', $usersId) . ')');
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $userId = (int)$a_row['userId'];
                if(array_key_exists($userId, $users)) {
                    $users[$userId]['slcspecialists'][] = $a_row['specialistId'];
                    
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
        
        return [
            'result' => [
                'status' => 'ok',
                'users' => array_values($users),
            ]
        ];
    }
    
    public function addToList($message_in) {
        $userId = (int)$message_in['params']['userId'];
        $specialistId = (int)$message_in['params']['specialistId'];
        $yearId = $this->container->get('session')->getSchoolYear()['id'];
        
        // Проверка на то, что ребенок уже у специалиста
        $sth = $this->db->prepare('SELECT COUNT(id) AS cnt FROM rbl_list WHERE yearId = :yearId AND userId = :userId AND specialistId = :specialistId');
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':specialistId', $specialistId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if($a_row['cnt'] > 0) {
                    return [
                        'result' => [
                            'status' => 'error',
                            'message' => 'Обучающийся уже назначен специалисту'
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
        // Добавить ребёнка к специалисту
        $sth = $this->db->prepare('INSERT INTO rbl_list(yearId, userId, specialistId) VALUES(:yearId, :userId, :specialistId)');
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':specialistId', $specialistId, PDO::PARAM_INT);
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
    
    public function delFromList($message_in) {
        $userId = $message_in['params']['userId'];
        $specialistId = $message_in['params']['specialistId'];
        $yearId = $this->container->get('session')->getSchoolYear()['id'];
        
        $sth = $this->db->prepare('DELETE FROM rbl_list WHERE yearId = :yearId AND userId = :userId AND specialistId = :specialistId');
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':specialistId', $specialistId, PDO::PARAM_INT);
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
