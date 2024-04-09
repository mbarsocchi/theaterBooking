<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'DateUtil.php';

class Shows {

    const SQL_DATE_FORMAT = "y-m-d G:i:s";

    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    function handleShows() {
        $r = null;
        switch (filter_input(INPUT_POST, 'f')) {
            case 'i':
                $r = $this->insertShow(filter_input(INPUT_POST, 'timestamp'), filter_input(INPUT_POST, 'namei'), filter_input(INPUT_POST, 'locationi'), filter_input(INPUT_POST, 'detailsi'), filter_input(INPUT_POST, 'seatsi'), filter_input(INPUT_POST, 'userid'));
                break;
            case 'd':
                $this->deleteShow(filter_input(INPUT_POST, 'id'));
                break;
            case 'u':
                $r = $this->updateShow(filter_input(INPUT_POST, 'id'), filter_input(INPUT_POST, 'timestamp'), filter_input(INPUT_POST, 'name'), filter_input(INPUT_POST, 'location'), filter_input(INPUT_POST, 'details'), filter_input(INPUT_POST, 'seats'));
                break;
            default:
                break;
        }
        if (filter_input(INPUT_GET, 'si') != null) {
            header('Location: shows.php?si=' . filter_input(INPUT_GET, 'si'));
        } else {
            header('Location: shows.php');
        }
        return $r;
    }
    private function validateField($name, $seats){
        if (!isset($name) || $name == "") {
            return "Il nome non può essere vuoto";
        }
        if (!isset($seats)|| $seats =="" || filter_var($seats, FILTER_VALIDATE_INT)) {
            return "Devi insererire un numero di posti a sedere";
        }
    }
    
    function updateShow($id, $timestamp, $name, $location, $details, $seats) {
        $validate = $this->validateField($name, $seats);
        if (isset($validate)) {
            echo "<h2>" . $validate . "</h2>";
        }
        $convertedDate = date("Y-m-d H:i:s", strtotime($timestamp));
        $stmt = $this->db->prepare("UPDATE spettacoli 
            SET nome=?, luogo=?, dettagli=?, data=?, posti=? 
            WHERE id=?");
        $name = trim($name);
        $stmt->bind_param("ssssii", $name, $location, $details, $convertedDate, $seats, $id);
        return $stmt->execute();
    }

    function returnDataForSpettacoloId($id) {
        $stmt = $this->db->prepare("SELECT * FROM spettacoli WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $r = count($r) == 0 ? null : $r[0];
        return $r;
    }

    function deleteShow($id) {
        $stmt = $this->db->prepare("SELECT count(1) as count "
                . "FROM users u "
                . "JOIN users_shows us ON us.user_id = u.id "
                . "AND us.show_id = ? "
                . "AND u.access_level != 0");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $c) {
            if ($c['count'] > 0) {
                $r['erromessage'] = "Devi prima eliminare tutti gli utenti, tranne te, per eliminare uno spettacolo";
                return $r;
            }
        }
        $stmt = $this->db->prepare("DELETE FROM spettacoli WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    function insertShow($timestamp, $name, $location, $details, $seats, $userId) {
        $validate = $this->validateField($name, $seats);
        if (isset($validate)) {
            echo "<h2>" . $validate . "</h2>";
        }
        $convertedDate = date("Y-m-d H:i:s", strtotime($timestamp));
        $stmt = $this->db->prepare("INSERT INTO spettacoli (nome, luogo, dettagli, data, posti) "
                . "VALUES (?,?,?,?,?)");
        $name = trim($name);
        $stmt->bind_param("sssss", $name, $location, $details, $convertedDate, $seats);
        $stmt->execute();
        $showId = $stmt->insert_id;
        $stmt = $this->db->prepare("INSERT INTO users_shows (show_id, user_id) "
                . "VALUES (?,?)");
        $stmt->bind_param("ii", $showId, $userId);
        $stmt->execute();
        
        $stmt = $this->db->prepare("SELECT company_id FROM companies_users "
                . "WHERE user_id = ? "
                . "AND is_company_admin = 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $companyIdArray = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function retriveAllfutureShow($id) {
        $now = date(self::SQL_DATE_FORMAT, time());
        $stmt = $this->db->prepare("SELECT s.* "
                . "FROM spettacoli s "
                . "JOIN users_shows us ON us.show_id = s.id "
                . "WHERE data >= ? "
                . "AND us.user_id = ? "
                . "ORDER BY data ASC");
        $stmt->bind_param("si", $now, $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function retriveShowByShowIds($arrayOfIds) {
        $inCondition = implode(', ', $arrayOfIds);
        $now = date(self::SQL_DATE_FORMAT, time());
        $stmt = $this->db->prepare("SELECT s.* "
                . "FROM spettacoli s "
                . "WHERE data >= ? "
                . "AND s.ID IN  (".$inCondition.") "
                . "ORDER BY data ASC");
        $stmt->bind_param("s", $now);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function returnDateOfShows($limit = false) {
        $now = time();
        if ($limit) {
            $today = date(self::SQL_DATE_FORMAT, $now + STOP_PRENO_HOUR);
        } else {
            $today = date(self::SQL_DATE_FORMAT, $now);
        }
        $stmt = $this->db->prepare("SELECT data,posti,id "
                . "FROM spettacoli "
                . "WHERE data >= ? "
                . "ORDER BY `spettacoli`.`data` ASC");
        $stmt->bind_param("s", $today);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $show) {
            $date = new DateTime($show['data']);
            $dayOfTheWeek = DateUtil::transformDay($date->format('N'));
            $result[] = array("data" => $show['data'], "dayOfTheShow" => $dayOfTheWeek);
        }
        return $result;
    }

    function getShowInUserScope($usersArray) {
        $result = array();
        foreach ($usersArray as $user) {
            $stmt = $this->db->prepare("SELECT s.id "
                    . "FROM spettacoli s "
                    . "JOIN users_shows us ON us.show_id = s.id "
                    . "AND us.user_id = ? "
                    . "ORDER BY data ASC");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $showId) {
                $result[$user['id']][] = $showId['id'] . " ";
            }
        }
        return $result;
    }

}
