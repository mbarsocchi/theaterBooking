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
                $r = $this->insertShow(
                        filter_input(INPUT_POST, 'timestamp'), 
                        filter_input(INPUT_POST, 'namei'), 
                        filter_input(INPUT_POST, 'locationi'),
                        filter_input(INPUT_POST, 'detailsi'), 
                        filter_input(INPUT_POST, 'seatsi'),
                        filter_input(INPUT_POST, 'realseatsi'), 
                        filter_input(INPUT_POST, 'dayhoverbooki'),
                        filter_input(INPUT_POST, 'userid'),
                        filter_input(INPUT_POST, 'company')
                        );
                break;
            case 'd':
                $this->deleteShow(filter_input(INPUT_POST, 'id'));
                break;
            case 'u':
                $r = $this->updateShow(
                    filter_input(INPUT_POST, 'id'), 
                    filter_input(INPUT_POST, 'timestamp'), 
                    filter_input(INPUT_POST, 'name'), 
                    filter_input(INPUT_POST, 'location'), 
                    filter_input(INPUT_POST, 'details'), 
                    filter_input(INPUT_POST, 'seats'), 
                    filter_input(INPUT_POST, 'realseats'), 
                    filter_input(INPUT_POST, 'dayhoverbook'), 
                    filter_input(INPUT_POST, 'company')
                );
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

    private function validateField($name, $seats, $realSeats, $dayHoverbook) {
        if (!isset($name) || $name == "") {
            return "Il nome non può essere vuoto";
        }
        if ((!isset($seats)|| $seats == "") && (!isset($realSeats)|| $realSeats == "" )){
            return "Numero di posti non settato";
        } else  if ((isset($seats) && $seats != "") && (isset($realSeats) && $realSeats != "" ) && $seats < $realSeats){
            return "Il numero di posti in hoverbooking deve essere uguale o maggiore a quello dei posti reali";
        } else if (isset($seats) && isset($realSeats) && ($seats != $realSeats) && !isset($dayHoverbook) ){
            return "Devi inserire il numero di giorni di hoverbooking, sennò che li hai messi a fari diversi ?";
        }
    }

    function updateShow($id, $timestamp, $name, $location, $details, $seats, $realSeats, $dayHoverbook) {
        $validate = $this->validateField($name, $seats, $realSeats, $dayHoverbook);
        if (isset($validate)) {
            echo "<h2>" . $validate . "</h2>";
            return;
        }
        if ((isset($seats) && $seats != "") && (!isset($realSeats) || $realSeats == "")){
            $realSeats = $seats;
        }else if ((!isset($seats) || $seats == "") && (isset($realSeats) && $realSeats != "")){
            $seats = $realSeats;
        }
        if (!isset($dayHoverbook)){
            $dayHoverbook = 0;
        }

        $convertedDate = date("Y-m-d H:i:s", strtotime($timestamp));
        $stmt = $this->db->prepare("UPDATE spettacoli 
            SET nome=?, luogo=?, dettagli=?, data=?, posti=?, posti_reali=?, hoverbook_giorni=?
            WHERE id=?");
        $name = trim($name);
        $stmt->bind_param("ssssiiii", $name, $location, $details, $convertedDate, $seats, $realSeats, $dayHoverbook, $id);
        Database::executeQuery($stmt);
    }

    function returnDataForSpettacoloId($id) {
        $stmt = $this->db->prepare("SELECT s.*,tc.name as companyName "
                . "FROM spettacoli s "
                . "JOIN theatre_companies tc ON s.company_id = tc.id "
                . "WHERE s.id = ?");
        $stmt->bind_param("s", $id);
        Database::executeQuery($stmt);
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
        Database::executeQuery($stmt);
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $c) {
            if ($c['count'] > 0) {
                $r['erromessage'] = "Devi prima eliminare tutti gli utenti, tranne te, per eliminare uno spettacolo";
                return $r;
            }
        }
        $stmt = $this->db->prepare("DELETE FROM spettacoli WHERE id=?");
        $stmt->bind_param("i", $id);
       Database::executeQuery($stmt);
    }

    function insertShow($timestamp, $name, $location, $details, $seats, $realSeats, $dayHoverbook, $userId, $companyId) {
        $validate = $this->validateField($name, $seats, $realSeats, $dayHoverbook);
        if (isset($validate)) {
            echo "<h2>" . $validate . "</h2>";
            die();
        }
        if ((isset($seats) && $seats != "") && (!isset($realSeats) || $realSeats == "")){
            $realSeats = $seats;
        }else if ((!isset($seats) || $seats == "") && (isset($realSeats) && $realSeats != "")){
            $seats = $realSeats;
        }
        if (!isset($dayHoverbook)){
            $dayHoverbook = 0;
        }

        $convertedDate = date("Y-m-d H:i:s", strtotime($timestamp));

        $stmt = $this->db->prepare("INSERT INTO spettacoli (nome, luogo, company_id, dettagli, data, posti, posti_reali, hoverbook_giorni) "
                . "VALUES (?,?,?,?,?,?,?,?)");
        $name = trim($name);
        $stmt->bind_param("ssissiii", $name, $location, $companyId, $details, $convertedDate, $seats, $realSeats, $dayHoverbook);
        Database::executeQuery($stmt);
    }

    function retriveAllfutureShow($userId) {
        $stmt = $this->db->prepare("SELECT s.* "
                . "FROM spettacoli s "
                . "JOIN users_shows us ON us.show_id = s.id "
                . "WHERE data >= NOW() "
                . "AND us.user_id = ? "
                . "ORDER BY data ASC");
        $stmt->bind_param("i", $userId);
        Database::executeQuery($stmt);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function retriveAllfutureShowForCompanies($companyArray) {
        $stmt = $this->db->prepare("SELECT s.* "
                . "FROM spettacoli s "
                . "JOIN theatre_companies tc ON tc.id = s.company_id "
                . "WHERE data >= NOW() "
                . "AND tc.id IN (?) "
                . "ORDER BY data ASC");
        $companyIds =  array_keys($companyArray);   
        $inCondition = implode(',', $companyIds);
        $stmt->bind_param("s", $inCondition);       
        Database::executeQuery($stmt);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function retriveAllfutureShowICanManage($userId) {
        $now = date(self::SQL_DATE_FORMAT, time());
        $stmt = $this->db->prepare("SELECT s.* FROM spettacoli s "
                . "WHERE company_id IN (SELECT company_id from companies_users cu "
                . "     WHERE cu.user_id = ?  "
                . "     AND cu.is_company_admin = 1 "
                . ")"
                . "AND s.data >= NOW() "
                . "ORDER BY data ASC");
        $stmt->bind_param("i", $userId);
        Database::executeQuery($stmt);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function getAllUsersForShows($showsArray) {
        if (count($showsArray)){
            $showIds = array_map(function ($o) {
                return $o['id'];
            }, $showsArray);
            $inCondition = implode(',', $showIds);
            $stmt = $this->db->prepare("SELECT us.user_id "
                    . "FROM users_shows us "
                    . "WHERE us.show_id "
                    . "IN (".$inCondition.") "
                    . "GROUP BY us.user_id ");
            try{
                $stmt->execute();
            }catch( mysqli_sql_exception $e ){
                global $debug;
                if($debug){
                    echo $e->getMessage();
                    die;
                }
            }
            $queryResult = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return array_map(function ($o) {
                return $o['user_id'];
            }, $queryResult);
        } else {
            return [];
        }
    }

    function retriveShowByShowIds($arrayOfIds) {
        $inCondition = implode(', ', $arrayOfIds);
        $now = date(self::SQL_DATE_FORMAT, time());
        $stmt = $this->db->prepare("SELECT s.* "
                . "FROM spettacoli s "
                . "WHERE data >= ? "
                . "AND s.ID IN  (" . $inCondition . ") "
                . "ORDER BY data ASC");
        $stmt->bind_param("s", $now);
        Database::executeQuery($stmt);
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
        Database::executeQuery($stmt);
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
            Database::executeQuery($stmt);
            foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $showId) {
                $result[$user['id']][] = $showId['id'] . " ";
            }
        }
        return $result;
    }

}
