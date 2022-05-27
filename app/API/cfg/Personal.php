<?php

namespace App\API\cfg;

use Psr\Container\ContainerInterface;
use PDO;
use App\Controllers\TraintGroups;
use App\Controllers\TraintAccess;

class Personal {
    protected $container;
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $container->get('db');
    }
    
    use TraintGroups;
    use TraintAccess;
    
    public function insertUser($message_in) {
        $user = $message_in['params']['user'];
        
        $sth = $this->db->prepare('INSERT INTO users_base (surname, firstname, patronymic, gender, birthday) VALUES (:surname, :firstname, :patronymic, :gender, :birthday)');
        $sth->bindValue(':surname', $user['surname'], PDO::PARAM_STR);
        $sth->bindValue(':firstname', $user['firstname'], PDO::PARAM_STR);
        $sth->bindValue(':patronymic', $user['patronymic'], PDO::PARAM_STR);
        $sth->bindValue(':gender', $user['gender'], PDO::PARAM_INT);
        $sth->bindValue(':birthday', $user['birthday'], PDO::PARAM_STR);
        
        if($sth->execute()) {
            return [
                'result' => [
                    'status' => 'ok',
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
    
    public function insertUserFromCSV($message_in) {
        $file = base64_decode($message_in['params']['file']);
        if($file == false) {
            return [
                'error' => [
                    'code' => -32602,
                    'message' => 'Invalid params'
                ]
            ];
        }
        // Фамилия, Имя, отчество, Пол (м,ж), Дата Рождения, Группы (через запятую), Класс
        $text = iconv('WINDOWS-1251', 'UTF-8', $file);
        $userList = [];
        foreach (explode("\r\n", $text) as $value) {
            if(mb_strlen($value)) {
                $userList[] = explode(';', $value);
            }
        }
        
        // Список групп
        $groups = [];
        $sth = $this->db->prepare('SELECT id, `name` FROM groups');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $groups[] = [
                    'id' => $a_row['id'],
                    'name' => $a_row['name']
                ];
            }
            $sth->closeCursor();
        }
        // Классы
        $classes = [];
        $sth = $this->db->prepare('SELECT id, `name` FROM classes WHERE yearId = :yearId');
        $sth->bindValue(':yearId', $this->container->get('session')->getSchoolYear()['id'], PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $classes[] = [
                    'id' => $a_row['id'],
                    'name' => $a_row['name']
                ];
            }
            $sth->closeCursor();
        }
        
        $users = [];
        $sth = $this->db->prepare('SELECT UPPER(surname) AS surname, UPPER(firstname) AS firstname, UPPER(patronymic) AS patronymic, DATE_FORMAT(birthday, \'%d.%m.%Y\') AS bd FROM users_base');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $users[] = [
                    'surname' => $a_row['surname'],
                    'firstname' => $a_row['firstname'],
                    'patronymic' => $a_row['patronymic'],
                    'birthday' => $a_row['bd']
                ];
            }
            $sth->closeCursor();
        }
        // Проверка
        $isError = false;
        $errorMessage = '';
        $userListForImport = [];
        $i = 1;
        foreach ($userList AS $user) {
            
            foreach ($users as $value) {
                if((mb_strtoupper($user[0]) == $value['surname']) &&
                   (mb_strtoupper($user[1]) == $value['firstname']) && 
                   (mb_strtoupper($user[2]) == $value['patronymic']) &&
                   (mb_strtoupper($user[4]) == $value['birthday'])) {
                    $isError = true;
                    $errorMessage .= 'Строка: ' . $i . ' Человек уже добавлен' . PHP_EOL;
                }
            }
            
            $userItem = [
                'surname' => $user[0],
                'firstname' => $user[1],
                'patronymic' => $user[2]
            ];
            switch (mb_strtolower($user[3])) {
                case 'м':
                    $userItem['gender'] = 1;
                    break;
                case 'ж':
                    $userItem['gender'] = 2;
                    break;
                default:
                    $userItem['gender'] = 0;
                    $isError = true;
                    $errorMessage .= 'Строка: ' . $i . ' Ошибка в столбце "Пол"' . PHP_EOL;
                    break;
            }
            $birthday = new \DateTime($user[4]);
            if($birthday == false) {
                $isError = true;
                $errorMessage .= 'Строка: ' . $i . ' Ошибка в столбце "Дата рождения"' . PHP_EOL;
            }
            $userItem['birthday'] = $birthday->format('Y-m-d');
            
            $userItem['groups'] = []; 
            foreach (explode(',', $user[5]) as $userGroup) {
                $isExist = false;
                $ug = mb_strtolower(trim($userGroup));
                foreach ($groups as $groupItem) {
                    if($ug == mb_strtolower($groupItem['name'])) {
                        $userItem['groups'][] = $groupItem['id'];
                        $isExist = true;
                        break;
                    }
                }
                if($isExist == false) {
                    $isError = true;
                    $errorMessage .= 'Строка: ' . $i . ' Ошибка в столбце "Группа"' . PHP_EOL;
                }
            }
            
            if(array_key_exists(6, $user) && ($user[6] != '')) {
                $isExist = false;
                $ucls = mb_strtolower(trim($user[6]));
                foreach ($classes as $cls) {
                    if($ucls == mb_strtolower($cls['name'])) {
                        $userItem['class'] = $cls['id'];
                        $isExist = true;
                        break;
                    }
                }
                if($isExist == false) {
                    $isError = true;
                    $errorMessage .= 'Строка: ' . $i . ' Ошибка в столбце "Класс"' . PHP_EOL;
                }
            }
            
            $userListForImport[] = $userItem;
            $i += 1;
        }
        
        if($isError == false) {
            $this->db->beginTransaction();
            
            $sthUser = $this->db->prepare('INSERT INTO users_base (surname, firstname, patronymic, gender, birthday) VALUES (:surname, :firstname, :patronymic, :gender, :birthday)');
            $sthGroup = $this->db->prepare('INSERT INTO users_groups(userId, groupId) VALUES(:userId, :groupId)');
            $sthClass = $this->db->prepare('INSERT INTO users_classes(userId, classId) VALUES(:userId, :classId)');
            
            foreach ($userListForImport as $userItem) {
                $sthUser->bindValue(':surname', $userItem['surname'], PDO::PARAM_STR);
                $sthUser->bindValue(':firstname', $userItem['firstname'], PDO::PARAM_STR);
                $sthUser->bindValue(':patronymic', $userItem['patronymic'], PDO::PARAM_STR);
                $sthUser->bindValue(':gender', $userItem['gender'], PDO::PARAM_INT);
                $sthUser->bindValue(':birthday', $userItem['birthday'], PDO::PARAM_STR);
                $userId = null;
                if($sthUser->execute()) {
                    $userId = $this->db->lastInsertId();
                } else {
                    $this->db->rollBack();
                    return [
                        'error' => [
                            'code' => -32603,
                            'message' => 'Internal error'
                        ]
                    ];
                }
                
                foreach ($userItem['groups'] as $groupItem) {
                    $sthGroup->bindValue(':userId', $userId, PDO::PARAM_INT);
                    $sthGroup->bindValue(':groupId', $groupItem, PDO::PARAM_INT);
                    if(!$sthGroup->execute()) {
                        $this->db->rollBack();
                        return [
                            'error' => [
                                'code' => -32603,
                                'message' => 'Internal error'
                            ]
                        ];
                    }
                }
                
                if(array_key_exists('class', $userItem)) {
                    $sthClass->bindValue(':userId', $userId, PDO::PARAM_INT);
                    $sthClass->bindValue(':classId', $userItem['class'], PDO::PARAM_INT);
                    if(!$sthClass->execute()) {
                        $this->db->rollBack();
                        return [
                            'error' => [
                                'code' => -32603,
                                'message' => 'Internal error'
                            ]
                        ];
                    }
                }
            }
            $this->db->commit();
        }
        
        return [
            'result' => [
                'status' => $isError?'error':'ok',
                'message' => $errorMessage,
                'users' => $userListForImport,
            ]
        ];
    }
    
    public function updateUser($message_in) {
        $user = $message_in['params']['user'];
        
        $sth = $this->db->prepare('UPDATE users_base SET surname = :surname, firstname = :firstname, patronymic = :patronymic, gender = :gender, birthday = :birthday WHERE id = :id');
        $sth->bindValue(':surname', $user['surname'], PDO::PARAM_STR);
        $sth->bindValue(':firstname', $user['firstname'], PDO::PARAM_STR);
        $sth->bindValue(':patronymic', $user['patronymic'], PDO::PARAM_STR);
        $sth->bindValue(':gender', $user['gender'], PDO::PARAM_INT);
        $sth->bindValue(':birthday', $user['birthday'], PDO::PARAM_STR);
        $sth->bindValue(':id', $user['id'], PDO::PARAM_INT);
        
        if($sth->execute()) {
            return [
                'result' => [
                    'status' => 'ok',
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
    
    public function deleteUser($message_in) {
        $id = (int)$message_in['params']['id'];
        
        $sth = $this->db->prepare('DELETE FROM users_base WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        
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

    public function insertGroup($message_in) {
        $userId = (int)$message_in['params']['userId'];
        $groupId = (int)$message_in['params']['groupId'];
        
        $sth = $this->db->prepare('SELECT COUNT(id) AS cnt FROM users_groups WHERE userId = :userId AND groupId = :groupId');
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':groupId', $groupId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if($a_row['cnt'] > 0) {
                    return [
                        'result' => [
                            'status' => 'error',
                            'message' => 'Упользователя уже есть эта группа'
                        ]
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
        
        $sth = $this->db->prepare('INSERT INTO users_groups(userId, groupId) VALUES(:userId, :groupId)');
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':groupId', $groupId, PDO::PARAM_INT);
        
        if($sth->execute()) {
            return [
                'result' => [
                    'status' => 'ok',
                    'groups' => $this->getGroupsForUser($userId)
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
    
    public function deleteGroup($message_in) {
        $id = (int)$message_in['params']['id'];
        $userId = (int)$message_in['params']['userId'];
        
        $sth = $this->db->prepare('DELETE FROM users_groups WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        
        if($sth->execute()) {
            return [
                'result' => [
                    'status' => 'ok',
                    'groups' => $this->getGroupsForUser($userId)
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
    
    public function insertAccess($message_in) {
        $userId = (int)$message_in['params']['userId'];
        $username = $message_in['params']['username'];
        $password = $message_in['params']['password'];
        
        $sth = $this->db->prepare('SELECT COUNT(id) AS cnt FROM users WHERE username = :username');
        $sth->bindValue(':username', $username, PDO::PARAM_STR);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if($a_row['cnt'] > 0) {
                    return [
                        'result' => [
                            'status' => 'error',
                            'message' => 'Пользователь с таким логином уже есть'
                        ]
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
        
        $sth = $this->db->prepare('INSERT INTO users(userId, username, `password`) VALUES(:userId, :username, :password)');
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':username', $username, PDO::PARAM_STR);
        $sth->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
        
        if($sth->execute()) {
            return [
                'result' => [
                    'status' => 'ok',
                    'users' => $this->getAccessForUser($userId),
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
    
    public function deleteAccess($message_in) {
        $id = (int)$message_in['params']['id'];
        $userId = (int)$message_in['params']['userId'];
        
        $sth = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        
        if($sth->execute()) {
            return [
                'result' => [
                    'status' => 'ok',
                    'users' => $this->getAccessForUser($userId),
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
    
    public function insertClasses($message_in) {
        $userId = (int)$message_in['params']['userId'];
        $classId = (int)$message_in['params']['classId'];
        
        
        $sth = $this->db->prepare('SELECT COUNT(id) AS cnt FROM users_classes WHERE userId = :userId AND classId = :classId');
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':classId', $classId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if($a_row['cnt'] > 0) {
                    return [
                        'result' => [
                            'status' => 'error',
                            'message' => 'У обучающегося такой класс назначен'
                        ]
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
        
        $sth = $this->db->prepare('INSERT INTO users_classes(userId, classId) VALUES(:userId, :classId)');
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':classId', $classId, PDO::PARAM_INT);
        
        if($sth->execute()) {
            return [
                'result' => [
                    'status' => 'ok',
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
    
    public function deleteClasses($message_in) {
         $id = (int)$message_in['params']['id'];
         $userId = (int)$message_in['params']['userId'];
        
        $sth = $this->db->prepare('DELETE FROM users_classes WHERE id = :id');
        $sth->bindValue(':userId', $id, PDO::PARAM_INT);
        
        if($sth->execute()) {
            return [
                'result' => [
                    'status' => 'ok',
                    'classes' => $this->getClassesForUser($userId)
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
