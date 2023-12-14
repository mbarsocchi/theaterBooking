<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'DateUtil.php';

class Booking {

    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    function handleBooking() {
        switch (filter_input(INPUT_POST, 'f')) {
            case 'b':
                $this->insertPreno(filter_input(INPUT_POST, 'name'), filter_input(INPUT_POST, 'user'), filter_input(INPUT_POST, 'showId'));
                break;
            case 'db':
                $this->deletePreno(filter_input(INPUT_POST, 'id'));
                break;
            case 'ub':
                break;
        }
        header('Location: booking.php');
    }

    function updatePrenoWithGeneratedCode($id, $rifUserId, $code) {
        global $wpdb;
        $preno_table = $wpdb->prefix . THEATER_BOOKING_TBNAME;
        $current_user = wp_get_current_user();
        $currentUserName = $current_user->user_login;
        $today = date("y-m-d G:i:s");
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
            $today = date("y-m-d G:i:s");
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

    function insertPreno($name, $userId, $id) {
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
            $today = date("y-m-d G:i:s");
            $stmt = $this->db->prepare("INSERT INTO prenotazioni (id_spettacolo, nome, id_user_ref, _ins) "
                    . "VALUES (?,?,?,?)");
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
            $dateFormatted = $date->format('d/m/y h:i');
            $bookingData[$dateFormatted]['id'] = $show['id'];
            $bookingData[$dateFormatted]['title'] = $show['nome'];
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
            $dateFormatted = $date->format('d/m/y h:i');
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
                . "ORDER BY p.nome");
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
