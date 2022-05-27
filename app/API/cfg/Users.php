<?php

namespace App\API\cfg;

use Psr\Container\ContainerInterface;
use PDO;

class Users {
    protected $container;
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $container->get('db');
    }
    
    public function get($message_in) {
        
        $itemsPerPage = 50;
        
        $groupsId = $message_in['params']['groups'];
        $searchText  = $message_in['params']['searchText'];
        $page = isset($message_in['params']['page'])?$message_in['params']['page']:0;
        
        /* Получить всех потомков выбранных групп */
        $all_groups = [];
        $sth = $this->db->prepare('SELECT id, parentId, `name` FROM groups');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $all_groups[$a_row['id']] = [
                    'parentId' => $a_row['parentId'],
                    'name' => $a_row['name']
                ];
            }
            $sth->closeCursor();
        }

        reset($groupsId);
        while(($item = current($groupsId)) !== false) {
            foreach ($all_groups as $key => $value) {
                if(($value['parentId'] == $item) && (!array_search($key, $groupsId))) {
                    $groupsId[] = $key;
                }
            }
            next($groupsId);
        }
        
        $users = [];
        $count = 0;
        // Подсчёт количества записей
        $sql = 'SELECT COUNT(users_base.id) AS cnt FROM users_base';
        
        if(count($groupsId)) {
            $sql .= ' INNER JOIN users_groups ON users_groups.userId = users_base.id WHERE users_groups.groupId IN (' . implode(', ', $groupsId) . ')';
            if(strlen($searchText)) {
                $sql .= ' AND surname LIKE :surname';
            }
        } else {
            if(strlen($searchText)) {
                $sql .= ' WHERE surname LIKE :surname';
            }
        } 
        $sth = $this->db->prepare($sql);
        if(strlen($searchText)) {
            $sth->bindValue(':surname', '%' . $searchText . '%', PDO::PARAM_STR);
        }
        
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $count = $a_row['cnt'];
            } else {
                $sth->closeCursor();
                return [
                    'error' => [
                        'code' => -32002,
                        'message' => 'Ошибка поиска'
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
        // Посик записей
        $sql = 'SELECT users_base.id, users_base.surname, users_base.firstname, users_base.patronymic, users_base.gender, users_base.birthday FROM users_base';
        
        if(count($groupsId)) {
            $sql .= ' INNER JOIN users_groups ON users_groups.userId = users_base.id WHERE users_groups.groupId IN (' . implode(', ', $groupsId) . ')';
            if(strlen($searchText)) {
                $sql .= ' AND surname LIKE :surname';
            }
        } else {
            if(strlen($searchText)) {
                $sql .= ' WHERE surname LIKE :surname';
            }
        } 
        $sql .= ' ORDER BY users_base.surname, users_base.firstname LIMIT ' . ($page * $itemsPerPage) . ', ' . $itemsPerPage;
        $sth = $this->db->prepare($sql);
        if(strlen($searchText)) {
            $sth->bindValue(':surname', '%' . $searchText . '%', PDO::PARAM_STR);
        }
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $users[] = [
                    'id' => $a_row['id'],
                    'surname' => $a_row['surname'],
                    'firstname' => $a_row['firstname'],
                    'patronymic' => $a_row['patronymic'],
                    'gender' => $a_row['gender'],
                    'birthday' => $a_row['birthday'],
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
                'users' => $users,
                'pages' => ceil($count / $itemsPerPage),
            ]
        ];
    }
}
