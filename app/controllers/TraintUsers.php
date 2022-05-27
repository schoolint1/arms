<?php

namespace App\Controllers;

use PDO;

trait TraintUsers {
    private function getUsersTree() {
        $users = [];
        $sth = $this->db->prepare('SELECT
users_base.id,
users_base.surname,
users_base.firstname,
users_base.patronymic,
users_base.gender,
users_base.birthday,
UPPER(classes.`name`) AS className,
classes.parallel
FROM
users_base
INNER JOIN users_classes ON users_classes.userId = users_base.id
INNER JOIN classes ON users_classes.classId = classes.id
WHERE
classes.yearId = :yearId
ORDER BY classes.parallel, classes.`name`, users_base.surname, users_base.firstname');
        $sth->bindValue(':yearId', $this->container->get('session')->getSchoolYear()['id'], PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if(!array_key_exists($a_row['parallel'], $users)) {
                    $users[$a_row['parallel']] = [
                        'check' => false,
                        'classes' => [],
                    ];
                }
                if(!array_key_exists($a_row['className'], $users[$a_row['parallel']]['classes'])) {
                    $users[$a_row['parallel']]['classes'][$a_row['className']] = [
                        'check' => false,
                        'users' => [],
                    ];
                }
                $users[$a_row['parallel']]['classes'][$a_row['className']]['users'][] = [
                    'id' => (int)$a_row['id'],
                    'name' => $a_row['surname'] . ' ' . $a_row['firstname'] . ' ' . $a_row['patronymic'],
                    'gender' => $a_row['gender'],
                    'birthday' => $a_row['birthday'],
                    'isSelected' => false,
                    'className' => $a_row['className'],
                ];
            }
            $sth->closeCursor();
        }
        return $users;
    }
}

