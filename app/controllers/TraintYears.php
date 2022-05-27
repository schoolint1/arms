<?php

namespace App\Controllers;

use PDO;

trait TraintYears {
    private function getAllYears() {
        $years = [];
        $sth = $this->db->prepare('SELECT id, name, begindate FROM years ORDER BY begindate');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $years[] = [ 
                    'id' => (int)$a_row['id'],
                    'name' => $a_row['name'],
                    'begindate' => $a_row['begindate']
                ];
            }
            $sth->closeCursor();
        }
        return $years;
    }
}
