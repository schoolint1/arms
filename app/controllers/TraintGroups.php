<?php

namespace App\Controllers;

use PDO;

trait TraintGroups {
    
    private function groupTreeChildren($groups, $level, $parentId) {
        $items = [];
        foreach ($groups as $value) {
            if($value['parentId'] == $parentId) {
                $item = $value;
                $item['level'] = $level;
                $items[] = $item;
                $subitems = $this->groupTreeChildren($groups, $level + 1, $item['id']);
                if(count($subitems)) {
                    $items = array_merge($items, $subitems);
                }
            }
        }
        return $items;
    }
    
    private function getGroupsTree() {
        $groups = [];
        $sth = $this->db->prepare('SELECT id, parentId, name FROM groups');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $groups[] = [
                    'id' => (int)$a_row['id'],
                    'name' => $a_row['name'],
                    'parentId' => (int)$a_row['parentId'],
                ];
            }
            $sth->closeCursor();
        }
        
        return $this->groupTreeChildren($groups, 0, 0);
    }
    
    private function getAccess() {
        $accessList = [];
        $sth = $this->db->prepare('SELECT id, modul, groupId  FROM access');
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $groupId = (int)$a_row['groupId'];
                if(!array_key_exists($groupId, $accessList)) {
                    $accessList[$groupId] = [];
                }
                $accessList[$groupId][] = $a_row['modul'];
            }
            $sth->closeCursor();
        }
        return $accessList;
    }
    
    private function getGroupsForUser($userId) {
        $groups = [];
        // Группы
        $sth = $this->db->prepare('SELECT id, groupId FROM users_groups WHERE userId = :userId');
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $groups[] = [
                    'id' => (int)$a_row['id'],
                    'groupId' => (int)$a_row['groupId']
                ];
            }
            $sth->closeCursor();
        }
        return $groups;
    }

}
