<?php

namespace App\Controllers;

use PDO;

trait TraintAccess {
    private function getAccessForUser($userId) {
        $access = [];
        // Доступ
        $sth = $this->db->prepare('SELECT id, username FROM users WHERE userId = :id');
        $sth->bindValue(':id', $userId, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $access[] = [
                    'id' => (int)$a_row['id'],
                    'username' => $a_row['username']
                ];
            }
            $sth->closeCursor();
        }
        return $access;
    }
}
