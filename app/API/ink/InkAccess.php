<?php
/* УСТАРЕЛО и не используется */
namespace App\API;

use PDO;

trait InkAccess {
    private function access() {
        $db = $this->container->get('db');
        $user = $this->container->get('session')->getUser();
        $access = false;
        if($user == null) {
            return ['result' => $access];
        }
        $sth = $db->prepare('SELECT groupId FROM usr_access');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                if($user->isInGroup($a_row['groupId'])) $access = true;
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
        return ['result' => $access];
    }
}