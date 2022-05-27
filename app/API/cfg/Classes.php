<?php

namespace App\API\cfg;

use Psr\Container\ContainerInterface;
use PDO;
use App\Controllers\TraintClasses;

class Classes {
    protected $container;
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $container->get('db');
    }
    
    use TraintClasses;
    
    public function insert($message_in) {
        $className = $message_in['params']['name'];
        $classYearId = (int)$message_in['params']['yearId'];
        $classParallel = (int)$message_in['params']['parallel'];
        
        $sth = $this->db->prepare('INSERT INTO classes(`name`, yearId, parallel) VALUES(:name, :yearId, :parallel)');
        $sth->bindValue(':yearId', $classYearId, PDO::PARAM_INT);
        $sth->bindValue(':parallel', $classParallel, PDO::PARAM_INT);
        $sth->bindValue(':name', $className, PDO::PARAM_STR);
        if($sth->execute()) {
            list($years, $classes) = $this->getAllClasses();
            return [
                'result' => [
                    'status' => 'ok',
                    'classes' => $classes
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
        $classId = (int)$message_in['params']['id'];
        $className = $message_in['params']['name'];
        $classParallel = (int)$message_in['params']['parallel'];;
        
        $sth = $this->db->prepare('UPDATE classes SET `name` = :name, parallel = :parallel WHERE id = :id');
        $sth->bindValue(':id', $classId, PDO::PARAM_INT);
        $sth->bindValue(':parallel', $classParallel, PDO::PARAM_INT);
        $sth->bindValue(':name', $className, PDO::PARAM_STR);
        if($sth->execute()) {
            list($years, $classes) = $this->getAllClasses();
            return [
                'result' => [
                    'status' => 'ok',
                    'classes' => $classes
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
        $id = (int)$message_in['params']['id'];
        
        // Проверка на связанных с пользователями
        $sth = $this->db->prepare('SELECT COUNT(id) AS cnt FROM users_classes WHERE classId = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if($a_row['cnt'] > 0) {
                    return [
                        'result' => [
                            'status' => 'error',
                            'message' => 'Есть обучающиеся в классе'
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

        $sth = $this->db->prepare('DELETE FROM classes WHERE id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        if($sth->execute()) {
            list($years, $classes) = $this->getAllClasses();
            return [
                'result' => [
                    'status' => 'ok',
                    'classes' => $classes
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
