<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'DateUtil.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Shows.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Users.php';

class Booking {

    const DATE_FORMAT = 'd/m/y H:i';
    const SQL_DATE_FORMAT = "y-m-d G:i:s";

    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    function handleBooking($myUserId) {
        switch (filter_input(INPUT_POST, 'f')) {
            case 'b':
                $userIdRef = filter_input(INPUT_POST, 'user');
                $showId = filter_input(INPUT_POST, 'showId');
                if ($myUserId != $userIdRef && $this->userIsAdminOfShow($myUserId, $userIdRef, $showId)) {
                    $r = $this->insertPreno(filter_input(INPUT_POST, 'name'), $userIdRef, $showId);
                } else if ($myUserId == $userIdRef) {
                    $r = $this->insertPreno(filter_input(INPUT_POST, 'name'), $userIdRef, $showId);
                }
                break;
            case 'db':
                $this->deletePreno(filter_input(INPUT_POST, 'id'));
                break;
            case 'ub':
                break;
        }
        header('Location: booking.php');
    }

    function userIsAdminOfShow($myUserId, $ref, $showId) {
        $shows = new Shows();
        $showData = $shows->returnDataForSpettacoloId($showId);
        $users = new Users();
        $c = $users->getCompanyForUser($ref);
        $myC = $users->getCompanyForUser($myUserId);
        $allCompanyOfRef = array_merge($c['adminArray'], $c['nonAdminArray']);
        return in_array($showData['company_id'], $allCompanyOfRef) && in_array($showData['company_id'], $myC['adminArray']);
    }

    function updatePrenoWithGeneratedCode($id, $rifUserId, $code) {
        global $wpdb;
        $preno_table = $wpdb->prefix . THEATER_BOOKING_TBNAME;
        $current_user = wp_get_current_user();
        $currentUserName = $current_user->user_login;
        $today = date(self::SQL_DATE_FORMAT);
        if (!current_user_can('manage_options') && $riferimentoSent == $currentUserName) {
            $returned = $wpdb->update($preno_table, array('_upd' => $today, 'prenocode' => $code),
                    array('id' => $id,
                        'user_id' => $currentUserName));
        } else {
            $returned = $wpdb->update($preno_table, array('_upd' => $today, 'prenocode' => $code), array('id' => $id));
        }
        return $returned;
    }

    function deletePreno($id) {
        $stmt = $this->db->prepare("DELETE FROM prenotazioni WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    function updatePrenoName($namePost, $idPost) {
        if (isset($namePost) && isset($idPost)) {
            $currentUserName = $current_user->user_login;
            $today = date(self::SQL_DATE_FORMAT);
            if (!current_user_can('manage_options')) {
                $returned = $wpdb->update($preno_table, array('nome' => $_POST['firstname'], '_upd' => $today),
                        array('id' => $_POST['id'],
                            'user_id' => $currentUserName));
            } else {
                $returned = $wpdb->update($preno_table, array('nome' => $_POST['firstname'], '_upd' => $today), array('id' => $_POST['id']));
            }
        }
        return $returned;
    }

    private function validateField($name) {
        if (!isset($name) || $name == "") {
            return "Il nome non può essere vuoto";
        }
    }

    function insertPreno($name, $userId, $id) {
        $validate = $this->validateField($name);
        if (isset($validate)) {
            echo "<h2>" . $validate . "</h2>";
        }
        $stmt = $this->db->prepare("SELECT id,posti FROM spettacoli WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $show) {
            $idShow = $show['id'];
            $maxPosti = $show['posti'];
            $stmt->free_result();
        }
        $stmt = $this->db->prepare("SELECT count(1) as count FROM prenotazioni WHERE id_spettacolo = ?");
        $stmt->bind_param("s", $idShow);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_assoc();
        $count = $rows['count'];
        $stmt->free_result();
        if ($count + 1 > $maxPosti) {
            echo "non ci sono più posti";
        } else {
            $today = date(self::SQL_DATE_FORMAT);
            $stmt = $this->db->prepare("INSERT INTO prenotazioni (id_spettacolo, nome, id_user_ref, _ins) "
                    . "VALUES (?,?,?,?)");
            $name = trim($name);
            $stmt->bind_param("isis", $idShow, $name, $userId, $today);
            $stmt->execute();
        }
    }

    function getBookings($showDates) {
        $inCondition = $this->builtInCondition($showDates);
        $bookingsAll = $this->getBookingData($inCondition);
        $temp = $this->reorderPrenos($bookingsAll);
        $bookingData = array();
        foreach ($showDates as $show) {
            $date = new DateTime($show['data']);
            $dateFormatted = $date->format(self::DATE_FORMAT);
            $bookingData[$dateFormatted]['id'] = $show['id'];
            $bookingData[$dateFormatted]['title'] = $show['nome'];
            $bookingData[$dateFormatted]['companyId'] = $show['company_id'];
            $bookingData[$dateFormatted]['dayOfTheWeek'] = DateUtil::transformDay($date->format('N'));
            $bookingData[$dateFormatted]['occupiedSeats'] = isset($temp[$dateFormatted]) ? count($temp[$dateFormatted]['bookings']) : 0;
            $bookingData[$dateFormatted]['freeSeats'] = $show['posti'] - $bookingData[$dateFormatted]['occupiedSeats'];
            $bookingData[$dateFormatted]['bookings'] = isset($temp[$dateFormatted]['bookings']) ? $temp[$dateFormatted]['bookings'] : array();
        }
        return $bookingData;
    }

    private function reorderPrenos($prenos) {
        $result = array();
        foreach ($prenos as $prenoPerDay) {
            $date = new DateTime($prenoPerDay['data']);
            $dateFormatted = $date->format(self::DATE_FORMAT);
            $result[$dateFormatted]['bookings'][] = array(
                'id' => $prenoPerDay['id'],
                'name' => $prenoPerDay['nome'],
                'riferimento' => $prenoPerDay['riferimento'],
                'riferimentoId' => $prenoPerDay['id_user_ref'],
                'prenocode' => $prenoPerDay['prenocode']);
        }

        return $result;
    }

    private function getBookingData($idArrays) {
        $stmt = $this->db->prepare("SELECT s.data, s.nome, p.id, p.nome, p.id_user_ref,  u.name as riferimento, p.prenocode "
                . "FROM prenotazioni AS p "
                . "LEFT JOIN spettacoli s ON s.id = p.id_spettacolo "
                . "LEFT JOIN users u ON u.id = p.id_user_ref "
                . "WHERE p.id_spettacolo IN ($idArrays) "
                . "ORDER BY u.name,p.nome");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function builtInCondition($daysOfShow) {
        $arrayOfIds = array();
        foreach ($daysOfShow as $day) {
            $arrayOfIds[] = $day['id'];
        }
        return count($arrayOfIds) > 0 ? implode(', ', $arrayOfIds) : null;
    }

}
