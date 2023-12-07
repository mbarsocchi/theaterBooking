<?php

class Login {

    private $db;
    private $errors;

    public function __construct($db) {
        $this->db = $db;
    }

    function handleLogin() {

        @session_start();

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
                $user = $this->getUser($username, $password);
                if ($user == null) {
                    $msg = 'Credenziali utente errate %s';
                } else {
                    $this->setSessionValues($user['name']);
                    if (filter_input(INPUT_POST, 'remember') != null && filter_input(INPUT_POST, 'remember') == 'on') {
                        $this->remember($user['id']);
                    }
                    header('Location: booking.php');
                    exit;
                }
            }
        }
    }

    private function setSessionValues($userName) {
        session_regenerate_id();
        $_SESSION['session_id'] = session_id();
        $_SESSION['session_user'] = $userName;
    }

    private function remember($id) {
        $selector = base64_encode(random_bytes(9));
        $authenticator = random_bytes(33);
        $expireTimestamp = time() + 864000;
        setcookie(
                'remember',
                $selector . ':' . base64_encode($authenticator),
                $expireTimestamp,
                '/',
                '',
                true,
                true
        );
        $stmt = $this->db->getConnection()->prepare("INSERT INTO "
                . "auth_tokens (selector, token, userid, expires) "
                . "VALUES (?, ?, ?, ?)");
        $token = hash('sha256', $authenticator);
        $expires = date('Y-m-d\TH:i:s', $expireTimestamp);
        $stmt->bind_param("ssis", $selector, $token, $id, $expires);
        $stmt->execute();
    }

    private function updateSession($id) {
        $expireTimestamp = time() + 864000;
        $stmt = $this->db->getConnection()->prepare("UPDATE "
                . "auth_tokens "
                . "SET expires = ? "
                . "WHERE userid = ?");
        $expires = date('Y-m-d\TH:i:s', $expireTimestamp);
        $stmt->bind_param("si", $id, $expires);
        $stmt->execute();
        $currentCookieValue = $_COOKIE['remember'];
        setcookie(
                'remember',
                $currentCookieValue,
                $expireTimestamp,
                '/',
                '',
                true,
                true
        );
    }

    private function canReAuthOnPageLoad($selector, $authenticator) {
        $stmt = $this->db->getConnection()->prepare("SELECT at.*, u.user_login, u.id "
                . "FROM auth_tokens at "
                . "JOIN users u ON at.userid = u.id "
                . "WHERE selector = ?");
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $authTokenArray = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        if (!count($authTokenArray) && isset($_COOKIE['remember'])) {
            $his->exit();
        } else {
            foreach ($authTokenArray as $authSess) {
                if (hash_equals($authSess['token'], hash('sha256', base64_decode($authenticator)))) {
                    $this->setSessionValues($authSess['user_login']);
                    $this->updateSession($authSess['id']);
                }
            }
        }
        $expireTimestamp = time();
        $stmt = $this->db->getConnection()->prepare("DELETE "
                . "FROM auth_tokens "
                . "WHERE expires < ? "
                . "LIMIT 5");
        $stmt->bind_param("s", $expireTimestamp);
        $stmt->execute();
    }

    public function isAuth($redirect = true) {
        @session_start();
        if (!empty($_COOKIE['remember'])) {
            list($selector, $authenticator) = explode(':', $_COOKIE['remember']);
            $this->canReAuthOnPageLoad($selector, $authenticator);
        }
        if ($redirect && (!isset($_SESSION['session_user']) || $_SESSION['session_user'] == null || $_SESSION['session_user'] == "")) {
            header('location: index.php');
        }
    }

    private function getUser($username, $password) {
        $stmt = $this->db->getConnection()->prepare("SELECT id,user_login "
                . "FROM users WHERE "
                . "user_login = ? "
                . "AND password = ? ");

        $passwordHash = md5($password);
        $stmt->bind_param("ss", $username, $passwordHash);

        $stmt->execute();
        $result = null;
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $user) {
            $result['id'] = $user['id'];
            $result['name'] = $user['user_login'];
        }
        $stmt->free_result();
        return $result;
    }

    public function exit() {
        session_start();
        session_destroy();
        if (!empty($_COOKIE['remember'])) {
            list($selector, $authenticator) = explode(':', $_COOKIE['remember']);
            unset($_COOKIE['remember']);
            setcookie('remember', '', -1, '/');
            $stmt = $this->db->getConnection()->prepare("DELETE "
                    . "FROM auth_tokens "
                    . "WHERE selector = ?");
            $stmt->bind_param("s", $selector);
            $stmt->execute();
        }
        header('Location: index.php');
        exit;
    }

}
