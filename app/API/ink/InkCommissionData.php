<?php

namespace App\API;

use Psr\Container\ContainerInterface;
use PDO;

class InkCommissionData {
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function create($message_in) {
        $id = (int)$message_in['params']['id'];
        if(!$this->container->get('session')->getUser()->isInGroup(5)) {
            return [
                'error' => [
                    'code' => -32000, // Недостаточно прав
                    'message' => 'Недостаточно прав для создания комиссии'
                ]
            ];
        }
        $db = $this->container->get('db');
        $sth = $db->prepare('SELECT id FROM ink_commissions WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        if($sth->execute()) {
            if ($sth->fetch(PDO::FETCH_ASSOC) !== false) {
                return [
                    'error' => [
                        'code' => -32001,
                        'message' => 'Комиссия уже создана'
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
        // Создаём комиссию
        $sth = $db->prepare('INSERT INTO ink_commissions(id) VALUES(:id)');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        if($sth->execute()) {
            return [
                'result' => [
                    'status' => 'ok',
                    'id' => $id,
                    'isCreate' => true
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

    public function lock($message_in) {
        $id = (int)$message_in['params']['id'];
        $num = (int)$message_in['params']['num'];
        if(!$this->container->get('session')->getUser()->isInGroup(5)) {
            return [
                'error' => [
                    'code' => -32000, // Недостаточно прав
                    'message' => 'Недостаточно прав для изменения комиссии'
                ]
            ];
        }
        $db = $this->container->get('db');
        $sth = $db->prepare('SELECT id, isFirstLock, isSecondLock, isThirdLock FROM ink_commissions WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) === false) {
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
        $sql = 'UPDATE ink_commissions SET ';
        switch ($num) {
            case 1:
                $sql .= 'isFirstLock = :val';
                $val = ($a_row['isFirstLock'] > 0)?0:1;
                break;
            case 2:
                $sql .= 'isSecondLock = :val';
                $val = ($a_row['isSecondLock'] > 0)?0:1;
                break;
            case 3:
                $sql .= 'isThirdLock = :val';
                $val = ($a_row['isThirdLock'] > 0)?0:1;
                break;
        }
        $sql .= ' WHERE id = :id';
        // Меняем статус комиссии
        $sth = $db->prepare($sql);
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        $sth->bindValue(':val', $val, PDO::PARAM_INT);
        if($sth->execute()) {
            return [
                'result' => [
                    'status' => 'ok',
                    'id' => $id,
                    'num' => $num,
                    'isLock' => $val?true:false
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

    private function date($message_in) {
        $id = (int)$message_in['params']['id'];
        $num = (int)$message_in['params']['num'];
        $date = $message_in['params']['date'];
        
        if(!$this->container->get('session')->getUser()->isInGroup(5)) {
            return [
                'error' => [
                    'code' => -32000, // Недостаточно прав
                    'message' => 'Недостаточно прав для изменения комиссии'
                ]
            ];
        }
        $db = $this->container->db;
        $sth = $db->prepare('SELECT id, firstDate, secondDate, thirdDate FROM ink_commissions WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) === false) {
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
        $sql = 'UPDATE ink_commissions SET ';
        switch ($num) {
            case 1:
                $sql .= 'firstDate = :val';
                $val = $a_row['firstDate'];
                break;
            case 2:
                $sql .= 'secondDate = :val';
                $val = $a_row['secondDate'];
                break;
            case 3:
                $sql .= 'thirdDate = :val';
                $val = $a_row['thirdDate'];
                break;
        }
        $sql .= ' WHERE id = :id';
        if($val == $date) {
            return [
                'error' => [
                    'code' => -32003,
                    'message' => 'Та же самая дата'
                ]
            ];
        }
        // Меняем статус комиссии
        $sth = $db->prepare($sql);
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        $sth->bindValue(':val', $date, PDO::PARAM_STR);
        if($sth->execute()) {
            return [
                'result' => [
                    'status' => 'ok',
                    'id' => $id,
                    'num' => $num,
                    'date' => $date
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

    private function save_variant($message_in) {
        $user = $this->container->get('session')->getUser();
        $val = $message_in['params']['val'];
        $id = $message_in['params']['id'];
        $parameterId = $message_in['params']['parameterId'];

        // Проверка на группу
        $db = $this->container->get('db');
        $sth = $db->prepare('SELECT id FROM ink_commission_parameters WHERE id = :id');
        $sth->bindValue(':id', $parameterId, PDO::PARAM_INT);
        if ($sth->execute()) {
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

        // Есть ли права у пользователя
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

        if($id != null) {
            // Проверка на наличие варианта в базе
            $sth = $db->prepare('SELECT id FROM ink_variants WHERE id = :id');
            $sth->bindValue(':id', $id, PDO::PARAM_INT);
            if($sth->execute()) {
                if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) === false) {
                    $id = null;
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

        if($id == null) {
            $sql = 'INSERT INTO ink_variants(parameterId, val) VALUES (:parameterId, :val)';
            $sth = $db->prepare($sql);
            $sth->bindValue(':parameterId', $parameterId, PDO::PARAM_INT);
            $sth->bindValue(':val', $val, PDO::PARAM_STR);
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
            $sql = 'UPDATE ink_variants SET val = :val WHERE id = :id';
            $sth = $db->prepare($sql);
            $sth->bindValue(':id', $id, PDO::PARAM_INT);
            $sth->bindValue(':val', $val, PDO::PARAM_STR);
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
        $sth = $db->prepare('SELECT val FROM ink_variants WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $val = $a_row['val'];
            } else {
                return [
                    'error' => [
                        'code' => -32603,
                        'message' => 'Internal error'
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

        return [
            'result' => [
                'status' => 'ok',
                'id' => $id,
                'val' => $val
            ]
        ];
    }

    private function delete_variant($message_in) {
        $user = $this->container->get('session')->getUser();
        $id =  $message_in['params']['id'];
        $db = $this->container->get('db');

        // Проверка на наличие варианта в базе
        $sth = $db->prepare('SELECT id, specialistId FROM ink_variants WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) === false) {
                return [
                    'error' => [
                        'code' => -32002,
                        'message' => 'Варианта нет в базе'
                    ]
                ];
            }
            $groupId = $a_row['specialistId'];
            $id = $a_row['id'];
            $sth->closeCursor();
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }

        // Есть ли права у пользователя
        if(!$user->isInGroup(5) && !$user->isInGroup($groupId)) {
            return [
                'error' => [
                    'code' => -32003,
                    'message' => 'Нет специальностей у пользователя'
                ]
            ];
        }

        $sql = 'DELETE FROM ink_variants WHERE id = :id';
        $sth = $db->prepare($sql);
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        if(!$sth->execute()) {
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
            ]
        ];
    }
}
