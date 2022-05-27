<?php

namespace App\API;

use Psr\Container\ContainerInterface;
use PDO;

class InkUserCommissionData
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    use InkHelpers;

    public function index($message_in) {
        $user = $this->container->get('session')->getUser();
        $userId = (int)$message_in['params']['userId'];
        $yearId = $this->container->get('session')->getSchoolYear()['id'];
        $db = $this->container->get('db');

        // Получить класс
        $classmaster_name = '';
        $class_info = null;
        $sth = $db->prepare('SELECT classes.id, classes.name, users_base.surname, users_base.firstname, users_base.patronymic
FROM classes
INNER JOIN users_classes ON users_classes.classId = classes.id
LEFT JOIN users_base ON users_base.id = classes.teacherId
WHERE classes.yearId = :yearId AND users_classes.userId = :userId');
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $class_info = [
                    'id' => (int)$a_row['id'],
                    'name' => $a_row['name'],
                    'teacher' => [
                        'surname' => $a_row['surname'],
                        'firstname' => $a_row['firstname'],
                        'patronymic' => $a_row['patronymic'],
                    ],
                ];
                $classmaster_name = $class_info['teacher']['surname'] . '  ' . $class_info['teacher']['firstname'] . ' ' . $class_info['teacher']['patronymic'];
            }
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }

        // Доступ для групп комиссии
        $commission_groups_access = [];
        $sth = $db->prepare('SELECT commissionGroupId, groupId FROM ink_commission_group_access');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $commissionGroupId = (int)$a_row['commissionGroupId'];
                if(!array_key_exists($commissionGroupId, $commission_groups_access)) {
                    $commission_groups_access[$commissionGroupId] = false;
                }
                if($user->isInGroup((int)$a_row['groupId'])) {
                    $commission_groups_access[$commissionGroupId] = true;
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

        // Параметры комиссии
        $parameters = [];
        $commission_parameters = [];
        $sth = $db->prepare('SELECT * FROM ink_commission_parameters ORDER BY orderNum');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $commission_parameters[(int)$a_row['groupId']][] = [
                    'id' => (int)$a_row['id'],
                    'name' => $this->str_helper([$classmaster_name, '(Сознание)'], $a_row['name']),
                    'isFirstCommissionAccess' => ($a_row['isFirstCommissionAccess'] > 0)?true:false,
                    'isSecondCommissionAccess' => ($a_row['isSecondCommissionAccess'] > 0)?true:false,
                    'isThirdCommissionAccess' => ($a_row['isThirdCommissionAccess'] > 0)?true:false,
                ];
                $parameters[] = (int)$a_row['id'];
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

        // Группы парамеров комиссиий
        $commission_groups = [];
        $sth = $db->prepare('SELECT id, `name`, `access` FROM ink_commission_groups ORDER BY orderNum');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $access = false;
                // 0 - доступ выбранным группам, 1 - доступ всем кроме выбранных групп
                if($a_row['access'] == 0){
                    if (array_key_exists((int)$a_row['id'], $commission_groups_access) && $commission_groups_access[(int)$a_row['id']]) {
                        $access = true;
                    }
                }
                if($a_row['access'] == 1) {
                    if(array_key_exists((int)$a_row['id'], $commission_groups_access) && $commission_groups_access[(int)$a_row['id']]) {
                        $access = false;
                    } else {
                        $access = true;
                    }
                }

                $commission_groups[] = [
                    'id' => (int)$a_row['id'],
                    'name' => $this->str_helper([$classmaster_name, '(Сознание)'], $a_row['name']),
                    'access' => $access,
                    'parameters' => array_key_exists((int)$a_row['id'], $commission_parameters)?$commission_parameters[(int)$a_row['id']]:[],
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

        // Получаем доступные комиссии
        $commission = [];
        $sth = $db->prepare('SELECT id, isFirstLock, firstDate, isSecondLock, secondDate, isThirdLock, thirdDate FROM ink_commissions WHERE id = :id');
        $sth->bindValue(':id', $yearId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if($a_row['firstDate'] !== null) {
                    $commission[1] = [
                        'name' => '1 комиссия',
                        'isLock' => $a_row['isFirstLock']?true:false,
                    ];
                }
                if($a_row['secondDate'] !== null) {
                    $commission[2] = [
                        'name' => '2 комиссия',
                        'isLock' => $a_row['isSecondLock']?true:false,
                    ];
                }
                if($a_row['thirdDate'] !== null) {
                    $commission[3] = [
                        'name' => '3 комиссия',
                        'isLock' => $a_row['isThirdLock']?true:false,
                    ];
                }
            } else {
                return [
                    'error' => [
                        'code' => -32002,
                        'message' => 'Комиссия ещё не создана'
                    ]
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
        // Получаем записи специалистов о запрошенном ребенке для комиссии
        $data = [];
        $sth = $db->prepare('SELECT id, commissionNum, parameterId, val, specialistId FROM ink_commission_data WHERE userId = :userId AND commissionId = :commissionId AND parameterId IN (' . implode(', ', $parameters) . ')');
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':commissionId', $yearId, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if(!array_key_exists((int)$a_row['commissionNum'], $data)) {
                    $data[(int)$a_row['commissionNum']] = [];
                }
                if(!array_key_exists((int)$a_row['parameterId'], $data[(int)$a_row['commissionNum']])) {
                    $data[(int)$a_row['commissionNum']][(int)$a_row['parameterId']] = [];
                }
                $data[(int)$a_row['commissionNum']][(int)$a_row['parameterId']] = [
                    'id' => $a_row['id'],
                    'val' => $a_row['val'],
                    'specialistId' => $a_row['specialistId'],
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
        // Рекомендации на клас
        $commission_class_data = [];
        if($class_info != null) {
            $sth = $db->prepare('SELECT id, commissionNum, val  FROM ink_commission_classes_data WHERE classId = :classId AND commissionId = :commissionId');
            $sth->bindValue(':classId', $class_info['id'], PDO::PARAM_INT);
            $sth->bindValue(':commissionId', $yearId, PDO::PARAM_INT);
            if($sth->execute()) {
                while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                    $commission_class_data[$a_row['commissionNum']] = [
                        'id' => $a_row['id'],
                        'val' => $a_row['val']
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
                'commission' => $commission,
                'commission_groups' => $commission_groups,
                'commission_data' => $data,
                'class_info' => $class_info,
                'commission_class_data' => $commission_class_data,
            ]
        ];
    }

    public function save($message_in) {
        $user = $this->container->get('session')->getUser();
        $userId = (int)$message_in['params']['userId'];
        $yearId = $this->container->get('session')->getSchoolYear()['id'];
        $commissionNum = (int)$message_in['params']['commissionNum'];
        $parameterId = (int)$message_in['params']['parameterId'];
        $val = $message_in['params']['val'];
        $id =  $message_in['params']['id'];
        $specialistId = $message_in['params']['specialistId'];

        // Проверка на группу
        $db = $this->container->get('db');
        $sth = $db->prepare('SELECT id FROM ink_commission_parameters WHERE id = :id');
        $sth->bindValue(':id', $parameterId, PDO::PARAM_INT);
        if($sth->execute()) {
            if ($sth->fetch(PDO::FETCH_ASSOC) === false) {
                return [
                    'error' => [
                        'code' => -32004,
                        'message' => 'Такой параметр не предусмотрен в комиссии'
                    ]
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

        // Доступ к комиссиям
        $access = $this->access();
        if(array_key_exists('error', $access)) {
            return $access;
        } else if(array_key_exists('result', $access) && ($access['result'] == false)) {
            return [
                'error' => [
                    'code' => -32003,
                    'message' => 'Нет доступа'
                ]
            ];
        }
        $access = false;
        $commission_group_id = null;
        $sth = $db->prepare('SELECT
ink_commission_groups.access,
ink_commission_groups.id
FROM
ink_commission_groups
INNER JOIN ink_commission_parameters ON ink_commission_groups.id = ink_commission_parameters.groupId
WHERE
ink_commission_parameters.id = :id');
        $sth->bindValue(':id', $parameterId, PDO::PARAM_INT);
        if ($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $commission_group_id = $a_row['id'];
                $commission_group_access = $a_row['access'];
            } else {
                $access = false;
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
        if ($commission_group_id != null) {
            if($commission_group_access == 0) $access = false;
            if($commission_group_access == 1) $access = true;
            $sth = $db->prepare('SELECT groupId FROM ink_commission_group_access WHERE commissionGroupId = :commissionGroupId');
            $sth->bindValue(':commissionGroupId', $commission_group_id, PDO::PARAM_INT);
            if ($sth->execute()) {
                while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                    if(($commission_group_access == 0) && $user->isInGroup($a_row['groupId'])) $access = true;
                    if(($commission_group_access == 1) && $user->isInGroup($a_row['groupId'])) $access = false;
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
        if(!$access) {
            return [
                'error' => [
                    'code' => -32003,
                    'message' => 'Нет доступа'
                ]
            ];
        }

        // Проверка комиссии
        $sth = $db->prepare('SELECT id, isFirstLock, firstDate, isSecondLock, secondDate, isThirdLock, thirdDate FROM ink_commissions WHERE id = :id');
        $sth->bindValue(':id', $yearId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) === false) {
                return [
                    'error' => [
                        'code' => -32002,
                        'message' => 'Комиссия ещё не создана'
                    ]
                ];
            }
            switch ($commissionNum) {
                case 1:
                    if( ($a_row['isFirstLock'] > 0) ||($a_row['firstDate'] == null)) {
                        return [
                            'error' => [
                                'code' => -32005,
                                'message' => 'Комиссия ещё не открыта'
                            ]
                        ];
                    }
                    break;
                case 2:
                    if( ($a_row['isSecondLock'] > 0) ||($a_row['secondDate'] == null)) {
                        return [
                            'error' => [
                                'code' => -32005,
                                'message' => 'Комиссия ещё не открыта'
                            ]
                        ];
                    }
                    break;
                case 3:
                    if( ($a_row['isThirdLock'] > 0) ||($a_row['thirdDate'] == null)) {
                        return [
                            'error' => [
                                'code' => -32005,
                                'message' => 'Комиссия ещё не открыта'
                            ]
                        ];
                    }
                    break;
                default:
                    return [
                        'error' => [
                            'code' => -32602,
                            'message' => 'Invalid params'
                        ]
                    ];
                    break;
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

        // Проверка на наличие ребёнка в базе
        $sth = $db->prepare('SELECT id FROM users_base WHERE id = :id');
        $sth->bindValue(':id', $userId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) === false) {
                return [
                    'error' => [
                        'code' => -32002,
                        'message' => 'Обучающийся не найден в базе'
                    ]
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

        // Проверка на наличие специалиста в базе
        if($specialistId > 0) {
            $sth = $db->prepare('SELECT id FROM users_base WHERE id = :id');
            $sth->bindValue(':id', $specialistId, PDO::PARAM_INT);
            if($sth->execute()) {
                if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) === false) {
                    return [
                        'error' => [
                            'code' => -32002,
                            'message' => 'Специалис не найден в базе'
                        ]
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
        } else if ($specialistId < 0) {
            return [
                'error' => [
                    'code' => -32602,
                    'message' => 'Invalid params'
                ]
            ];
        }

        // Сохранение
        if($id == null) {
            $sql = 'INSERT INTO ink_commission_data(userId, commissionId, commissionNum, parameterId, val, specialistId) VALUES (:userId, :commissionId, :commissionNum, :parameterId, :val, :specialistId)';
            $sth = $db->prepare($sql);
            $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
            $sth->bindValue(':commissionId', $yearId, PDO::PARAM_INT);
            $sth->bindValue(':commissionNum', $commissionNum, PDO::PARAM_INT);
            $sth->bindValue(':parameterId', $parameterId, PDO::PARAM_INT);
            $sth->bindValue(':val', $val, PDO::PARAM_STR);
            $sth->bindValue(':specialistId', $specialistId, PDO::PARAM_INT);
            if($sth->execute()) {
                $id = $db->lastInsertId();
                $sth->closeCursor();
            } else {
                return [
                    'error' => [
                        'code' => -32603,
                        'message' => 'Internal error'
                    ]
                ];
            }
        } else {
            $sql = 'UPDATE ink_commission_data SET val = :val, specialistId = :specialistId WHERE id = :id';
            $sth = $db->prepare($sql);
            $sth->bindValue(':id', $id, PDO::PARAM_INT);
            $sth->bindValue(':val', $val, PDO::PARAM_STR);
            $sth->bindValue(':specialistId', $specialistId, PDO::PARAM_INT);
            if(!$sth->execute()) {
                return [
                    'error' => [
                        'code' => -32603,
                        'message' => 'Internal error'
                    ]
                ];
            }
            $sth->closeCursor();
        }

        // Забираем результат из базы
        $sth = $db->prepare('SELECT val, specialistId FROM ink_commission_data WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $val = $a_row['val'];
                $specialistId = $a_row['specialistId'];
            } else {
                $val = '';
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
                'id' => $id,
                'val' => $val,
                'specialistId' => $specialistId
            ]
        ];
    }
}