<?php

namespace App\Controllers;

use PDO;

trait TraintClasses {
    private function getClassesTree() {
        $classes = [];
        $sth = $this->db->prepare('SELECT id, UPPER(`name`) AS `name`, parallel
FROM classes
WHERE yearId = :yearId
ORDER BY parallel, `name`');
        $sth->bindValue(':yearId', $this->container->get('session')->getSchoolYear()['id'], PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if(!array_key_exists($a_row['parallel'], $classes)) {
                    $classes[$a_row['parallel']] = [
                        'check' => false,
                        'classes' => [],
                    ];
                }
                $classes[$a_row['parallel']]['classes'][$a_row['id']] = [
                    'id' => $a_row['id'],
                    'name' => $a_row['name'],
                    'isSelected' => false,
                ];
            }
            $sth->closeCursor();
        }
        return $classes;
    }
    
    private function getAllClasses() {
        $classes = [];
        $years = [];
        $sth = $this->db->prepare('SELECT
        years.id AS yearId,
	years.`name` AS yearName,  
	classes.id AS clsId, 
	UPPER(classes.`name`) AS clsName,
        classes.parallel
FROM
	years
	INNER JOIN
	classes
	ON 
            years.id = classes.yearId
ORDER BY
	years.begindate DESC, 
	classes.parallel, 
	classes.`name`');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $isYearExist = false;
                foreach ($years as $value) {
                    if($value['id'] == (int)$a_row['yearId']) {
                        $isYearExist = true;
                        break;
                    }
                }
                if(!$isYearExist) {
                    $years[] = [
                        'id' => (int)$a_row['yearId'],
                        'name' => $a_row['yearName']
                    ];
                }
                
                if(!array_key_exists((int)$a_row['yearId'], $classes)) {
                    $classes[(int)$a_row['yearId']] = [];
                }
                if(!array_key_exists($a_row['parallel'], $classes[(int)$a_row['yearId']])) {
                    $classes[(int)$a_row['yearId']][(int)$a_row['parallel']] = [];
                }
                $classes[(int)$a_row['yearId']][(int)$a_row['parallel']][] = [ 
                    'id' => (int)$a_row['clsId'],
                    'name' => $a_row['clsName']
                ];
            }
            $sth->closeCursor();
        }
        return [$years, $classes];
    }
    
    private function getClassesForUser($userId) {
        $classes = [];
        // Классы
        $sth = $this->db->prepare('SELECT
years.`name` AS yearName,
classes.`name` AS className,
users_classes.classId
FROM
users_classes
INNER JOIN classes ON users_classes.classId = classes.id
INNER JOIN years ON classes.yearId = years.id
WHERE
users_classes.userId = :id');
        $sth->bindValue(':id', $userId, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $classes[] = [
                    'id' => (int)$a_row['classId'],
                    'className' => $a_row['className'],
                    'yearName' => $a_row['yearName'],
                ];
            }
            $sth->closeCursor();
        }
        return $classes;
    }
    
    private function getClassesForYear($yearId) {
        $classes = [];
        // Классы
        $sth = $this->db->prepare('SELECT classes.`name`,
classes.id FROM classes WHERE yearId = :yearId ORDER BY parallel, `name`');
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $classes[] = [
                    'id' => (int)$a_row['id'],
                    'name' => $a_row['name']
                ];
            }
            $sth->closeCursor();
        }
        return $classes;
    }
}

