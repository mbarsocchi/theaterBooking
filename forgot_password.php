<?php
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Users.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Database.php';

$head = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_meta_head.php');
echo $head->render();
$user = new Users();
if (isset($_POST['password']) && isset($_POST['password-confirm'])) {
    $password = $_POST['password'];
    $passwordConfirm = $_POST['password-confirm'];
    if ($password === $passwordConfirm) {
        if (isset($_POST['token'])) {
            $token = $_POST['token'];
            if ($user->validate_reset_token($token)) {
                $user->update_password($token, $password);
                $data['msg'] = "La tua password è stata reimpostata con successo.";
                $fpv = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'forgot_psw_msg.php', $data);
                echo $fpv->render();
            } else {
                $data['msg'] = "Token non valido o scaduto.";
                $fpv = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'forgot_psw_msg.php', $data);
                echo $fpv->render();
            }
        } else {
            $data['msg'] = "Token mancante.";
            $fpv = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'forgot_psw_msg.php', $data);
            echo $fpv->render();
        }
    } else {
        $data['msg'] = "Le password non corrispondono.";
        $fpv = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'forgot_psw_msg.php', $data);
        echo $fpv->render();
    }
}else if (isset($_POST['email'])) {
    $email = $_POST['email'];
    if ($user->email_exists($email)) {
        // Generate a password reset token and send an email to the user
        $token = bin2hex(random_bytes(16));
        // Store the token in the database associated with the user's email
        $user->store_reset_token($email, $token);
        // Send the email with the reset link
        $user->send_reset_email($_SERVER['HTTP_HOST'], $email, $token);
    } 
    $data['msg'] = "Se la tua email è presente nel nostro sistema, riceverai un link per reimpostare la password.";
    $fpv = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'forgot_psw_msg.php', $data);
    echo $fpv->render();
}else if (isset($_GET['token'])) {
    $token = $_GET['token'];
    if ($user->validate_reset_token($token)) {
        $fpv = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'forgot_password_view.php');
        echo $fpv->render();
    } else {
        $data['msg'] = "Token non valido o scaduto.";
        $fpv = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'forgot_psw_msg.php', $data);
        echo $fpv->render();
    }
}else {
    $fpv = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'forgot_psw_email_view.php');
    echo $fpv->render();
}

$footer['includeFooter'] = true;
$foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php',$footer);
echo $foot->render();