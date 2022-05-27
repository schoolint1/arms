<?php

namespace App\API;

use Psr\Container\ContainerInterface;
use PDO;

class InkClassCommissionData
{
    protected $container;
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $this->container->get('db');
    }

    public function index($message_in) {
        $user = $this->container->get('session')->getUser();
        $classId = (int)$message_in['params']['classId'];
        $yearId = $this->container->get('session')->getSchoolYear()['id'];
        
        // Получаем доступные комиссии
        $commission = [];
        $sth = $this->db->prepare('SELECT id, isFirstLock, firstDate, isSecondLock, secondDate, isThirdLock, thirdDate FROM ink_commissions WHERE id = :id');
        $sth->bindValue(':id', $yearId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if($a_row['firstDate'] !== null) {
                    $commission[1] = [
                        'name' => '1 комиссия',
                        'isLock' => $a_row['isFirstLock']?true:false,
                        'id' => null,
                        'val' => '',
                    ];
                }
                if($a_row['secondDate'] !== null) {
                    $commission[2] = [
                        'name' => '2 комиссия',
                        'isLock' => $a_row['isSecondLock']?true:false,
                        'id' => null,
                        'val' => '',
                    ];
                }
                if($a_row['thirdDate'] !== null) {
                    $commission[3] = [
                        'name' => '3 комиссия',
                        'isLock' => $a_row['isThirdLock']?true:false,
                        'id' => null,
                        'val' => '',
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
        $sth = $this->db->prepare('SELECT id, commissionNum, val  FROM ink_commission_classes_data WHERE classId = :classId AND commissionId = :commissionId');
        $sth->bindValue(':classId', $classId, PDO::PARAM_INT);
        $sth->bindValue(':commissionId', $yearId, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if(array_key_exists((int)$a_row['commissionNum'], $commission)) {
                    $commission[(int)$a_row['commissionNum']]['id'] =  $a_row['id'];
                    $commission[(int)$a_row['commissionNum']]['val'] = $a_row['val'];
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
                'commission' => $commission,
            ]
        ];
    }

    public function save($message_in) {
        $user = $this->container->get('session')->getUser();
        $classId = (int)$message_in['params']['classId'];
        $yearId = $this->container->get('session')->getSchoolYear()['id'];
        $commissionNum = (int)$message_in['params']['commissionNum'];
        $val = $message_in['params']['val'];

        // Проверка комиссии
        $sth = $this->db->prepare('SELECT id, isFirstLock, firstDate, isSecondLock, secondDate, isThirdLock, thirdDate FROM ink_commissions WHERE id = :id');
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
        // Проверка класса
        $sth = $this->db->prepare('SELECT id FROM classes WHERE id = :classId AND yearId = :yearId');
        $sth->bindValue(':classId', $classId, PDO::PARAM_INT);
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) === false) {
                return [
                    'error' => [
                        'code' => -32002,
                        'message' => 'Данного класса нет вы выбранном году'
                    ]
                ];
            }
            $sth->closeCursor();
        }
        // Добавить или обновить?
        $id = null;
        $sth = $this->db->prepare('SELECT id FROM ink_commission_classes_data WHERE classId = :classId AND commissionId = :yearId AND commissionNum = :commissionNum');
        $sth->bindValue(':classId', $classId, PDO::PARAM_INT);
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        $sth->bindValue(':commissionNum', $commissionNum, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $id = $a_row['id'];
            }
            $sth->closeCursor();
        }
        // Сохраняем
        if($id == null) {
            $sql = 'INSERT INTO ink_commission_classes_data(classId, commissionId, commissionNum, val) VALUES (:classId, :commissionId, :commissionNum, :val)';
            $sth = $this->db->prepare($sql);
            $sth->bindValue(':classId', $classId, PDO::PARAM_INT);
            $sth->bindValue(':commissionId', $yearId, PDO::PARAM_INT);
            $sth->bindValue(':commissionNum', $commissionNum, PDO::PARAM_INT);
            $sth->bindValue(':val', $val, PDO::PARAM_STR);
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
        } else {
            $sql = 'UPDATE ink_commission_classes_data SET val = :val WHERE id = :id';
            $sth = $this->db->prepare($sql);
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
        $sth = $this->db->prepare('SELECT val FROM ink_commission_classes_data WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $val = $a_row['val'];
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
                'val' => $val
            ]
        ];
    }
}