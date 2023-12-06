<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'DateUtil.php';

class Shows {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    function handleShows() {
        $r = null;
        switch (filter_input(INPUT_POST, 'f')) {
            case 'i':
                $this->insertShow(filter_input(INPUT_POST, 'timestamp'), filter_input(INPUT_POST, 'namei'), filter_input(INPUT_POST, 'locationi'), filter_input(INPUT_POST, 'detailsi'), filter_input(INPUT_POST, 'seatsi'), filter_input(INPUT_POST, 'userid'));
                break;
            case 'd':
                $r = $this->deleteShow(filter_input(INPUT_POST, 'id'));
                break;
            case 'u':
                $this->updateShow(filter_input(INPUT_POST, 'id'), filter_input(INPUT_POST, 'timestamp'), filter_input(INPUT_POST, 'name'), filter_input(INPUT_POST, 'location'), filter_input(INPUT_POST, 'details'), filter_input(INPUT_POST, 'seats'));
                break;
            default:
                break;
        }
        return $r;
    }

    function updateShow($id, $timestamp, $name, $location, $details, $seats) {
        $convertedDate = date("Y-m-d H:i:s", strtotime($timestamp));
        $stmt = $this->db->getConnection()->prepare("UPDATE spettacoli 
            SET nome=?, luogo=?, dettagli=?, data=?, posti=? 
            WHERE id=?");
        $stmt->bind_param("ssssii", $name, $location, $details, $convertedDate, $seats, $id);
        return $stmt->execute();
    }

    function returnDataForSpettacoloId($id) {
        $stmt = $this->db->getConnection()->prepare("SELECT * FROM spettacoli WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function deleteShow($id) {
        $stmt = $this->db->getConnection()->prepare("SELECT count(1) as count "
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
        $stmt = $this->db->getConnection()->prepare("DELETE FROM spettacoli WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    function insertShow($timestamp, $name, $location, $details, $seats, $userId) {
        $convertedDate = date("Y-m-d H:i:s", strtotime($timestamp));
        $stmt = $this->db->getConnection()->prepare("INSERT INTO spettacoli (nome, luogo, dettagli, data, posti) "
                . "VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss", $name, $location, $details, $convertedDate, $seats);
        $stmt->execute();
        $showId = $stmt->insert_id;
        $stmt = $this->db->getConnection()->prepare("INSERT INTO users_shows (show_id, user_id) "
                . "VALUES (?,?)");
        $stmt->bind_param("ii", $showId, $userId);
        $stmt->execute();
    }

    function retriveAllfutureShow($id) {
        $now = date("Y-m-d G:i:s", time());
        $stmt = $this->db->getConnection()->prepare("SELECT s.* "
                . "FROM spettacoli s "
                . "JOIN users_shows us ON us.show_id = s.id "
                . "WHERE data >= ? "
                . "AND us.user_id = ? "
                . "ORDER BY data ASC");
        $stmt->bind_param("si", $now, $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function returnDateOfShows($limit = false) {
        $now = time();
        if ($limit) {
            $today = date("Y-m-d G:i:s", $now + STOP_PRENO_HOUR);
        } else {
            $today = date("Y-m-d G:i:s", $now);
        }
        $stmt = $this->db->getConnection()->prepare("SELECT data,posti,id "
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
            $stmt = $this->db->getConnection()->prepare("SELECT s.id "
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
