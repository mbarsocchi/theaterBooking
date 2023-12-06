<?php

class Login {

    private $db;
    private $errors;

    public function __construct($db) {
        $this->db = $db;
    }

    function handleLogin() {

        session_start();

        if (isset($_SESSION['session_id'])) {
            header('Location: booking.php');
            exit;
        }

        if (filter_input(INPUT_POST, 'username') != null && filter_input(INPUT_POST, 'password') != null) {
            $username = filter_input(INPUT_POST, 'username') ?? '';
            $password = filter_input(INPUT_POST, 'password') ?? '';

            if (empty($username) || empty($password)) {
                $msg = 'Inserisci username e password %s';
            } else {
                $userName = $this->getUserId($username, $password);
                if ($userName == null) {
                    $msg = 'Credenziali utente errate %s';
                } else {
                    session_regenerate_id();
                    $_SESSION['session_id'] = session_id();
                    $_SESSION['session_user'] = $userName;

                    header('Location: booking.php');
                    exit;
                }
            }
        }
    }

    private function getUserId($username, $password) {
        $stmt = $this->db->getConnection()->prepare("SELECT user_login "
                . "FROM users WHERE "
                . "user_login = ? "
                . "AND password = ? ");

        $passwordHash = md5($password);
        $stmt->bind_param("ss", $username, $passwordHash);

        $stmt->execute();
        $result = null;
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $user) {
            $result = $user['user_login'];
        }
        $stmt->free_result();
        return $result;
    }

}
