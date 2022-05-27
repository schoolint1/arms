<?php

namespace App\Controllers;

use PDO;

trait TraintSpecialists {
    private function getSpecialists() {
        // Специалисты
        $specialists = [];
        $sth = $this->db->prepare('SELECT
groups.id,
groups.`name`
FROM
groups
INNER JOIN vcm_specialists ON vcm_specialists.id = groups.id');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $specialists[(int)$a_row['id']] = $a_row['name'];
            }
            $sth->closeCursor();
        }
        return $specialists;
    }
}