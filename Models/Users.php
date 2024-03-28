<?php

class Users {

    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    function handle() {
        $r = null;
        $arryOfShows = isset($_POST['show']) ? $_POST['show'] : null;
        switch (filter_input(INPUT_POST, 'f')) {
            case 'au':
                $r = $this->insertUser(filter_input(INPUT_POST, 'name'), filter_input(INPUT_POST, 'login'), filter_input(INPUT_POST, 'password'), filter_input(INPUT_POST, 'accessLevel'), $arryOfShows);
                break;
            case 'uu':
                $r = $this->updateUser(filter_input(INPUT_POST, 'id'), filter_input(INPUT_POST, 'name'), filter_input(INPUT_POST, 'login'), filter_input(INPUT_POST, 'password'), filter_input(INPUT_POST, 'accessLevel'), $arryOfShows);
                break;
            case 'du':
                $this->deleteUser(filter_input(INPUT_POST, 'id'));
            default:
                break;
        }
        return $r;
    }

    function getUser($id) {
        $stmt = $this->db->prepare("SELECT id, name, user_login, access_level "
                . "FROM users "
                . "WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $r = count($r) == 0 ? null : $r[0];
        return $r;
    }

    function getUserFromLogin($name) {
        $stmt = $this->db->prepare("SELECT id, name, user_login, access_level "
                . "FROM users "
                . "WHERE user_login = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $r = count($r) == 0 ? null : $r[0];
        return $r;
    }
    
    function getAllUsers(){
         $stmt = $this->db->prepare("SELECT DISTINCT u.id, u.name, u.user_login, u.access_level "
                . "FROM users u "
                . "ORDER BY u.name ASC;");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function getUsersInScope($id) {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("SELECT DISTINCT u.id, u.name, u.user_login, u.access_level "
                . "FROM users u "
                . "JOIN users_shows us ON u.id = us.user_id "
                . "WHERE show_id IN (SELECT show_id FROM users_shows us "
                . "JOIN spettacoli s ON s.id = us.show_id "
                . "WHERE user_id = ? "
                . "AND s.data >= ?)"
                . "ORDER BY u.name ASC;");
        $stmt->bind_param("is", $id,$now);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function isAdmin($id) {
        $result = false;
        $stmt = $this->db->prepare("SELECT access_level "
                . "FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $queryResult = $stmt->get_result();
        if ($queryResult->num_rows > 0) {
            while ($row = $queryResult->fetch_assoc()) {
                $result = $row["access_level"] == 0;
                break;
            }
        }
        return $result;
    }

    function insertUser($name, $user_login, $passwordClear, $access_level, $showsArray) {
        if ($showsArray == null) {
            $r['erromessage'] = "Non puoi inserire un utente senza spettacoli associati";
            return $r;
        }
        $stmt = $this->db->prepare("INSERT INTO users (name, user_login, password, access_level) "
                . "VALUES (?,?,?,?)");
        $hash = md5($passwordClear);
        $al = intval(trim($access_level));
        $stmt->bind_param("sssi", $name, $user_login, $hash, $al);
        $stmt->execute();
        $userId = $stmt->insert_id;
        foreach ($showsArray as $showId) {
            $stmt = $this->db->prepare("INSERT INTO users_shows (show_id, user_id) "
                    . "VALUES (?,?)");
            $si = intval($showId);
            $stmt->bind_param("ii", $si, $userId);
            $stmt->execute();
        }
    }

    function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    function updateUser($userId, $name, $user_login, $passwordClear, $access_level, $showsArray) {
        $currentShowsForUser = $this->getShowsForUser($userId);

        if ($passwordClear != null & $passwordClear != "") {
            $stmt = $this->db->prepare("UPDATE users 
            SET name=?, user_login=?, password=?, access_level=? 
            WHERE id=?");
            $hash = md5($passwordClear);
            $al = intval(trim($access_level));
            $stmt->bind_param("sssii", $name, $user_login, $hash, $access_level, $userId);
        } else {
            $stmt = $this->db->prepare("UPDATE users 
            SET name=?, user_login=?, access_level=? 
            WHERE id=?");
            $al = intval(trim($access_level));
            $stmt->bind_param("ssii", $name, $user_login, $access_level, $userId);
        }
        $stmt->execute();
        
            $toAddArr = array_diff($showsArray, $currentShowsForUser);
            foreach ($toAddArr as $showId) {
                $stmt = $this->db->prepare("INSERT INTO users_shows (show_id, user_id) "
                        . "VALUES (?,?)");
                $si = intval($showId);
                $stmt->bind_param("ii", $si, $userId);
                $stmt->execute();
            }
            $toDeleteArr = array_diff($currentShowsForUser, $showsArray);
            foreach ($toDeleteArr as $showId) {
                $stmt = $this->db->prepare("DELETE FROM users_shows "
                        . "WHERE show_id =? "
                        . "AND user_id = ?");
                $si = intval($showId);
                $stmt->bind_param("ii", $si, $userId);
                $stmt->execute();
            }
       
    }

    function getShowsForUser($userId) {
        $result = array();
        $stmt = $this->db->prepare("SELECT s.id "
                . "FROM spettacoli s "
                . "JOIN users_shows us ON us.show_id = s.id "
                . "AND us.user_id = ? "
                . "ORDER BY data ASC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $showId) {
            $result[] = $showId['id'];
        }
        return $result;
    }

}
