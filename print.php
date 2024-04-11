<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Shows.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Users.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Booking.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Login.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'PrintPdf.php';

$shows = new Shows();
$booking = new Booking();
$login = new Login();
$users = new Users();
$print = new PrintPdf();

$login->isAuth(false);

if (filter_input(INPUT_POST, 'f') != null && in_array(filter_input(INPUT_POST, 'f'), array('pr'))) {
    $print->handlePrint();
}

$thisUser = $users->getUserFromLogin($_SESSION['session_user']);
$data['userName'] = $thisUser['name'];
$data['thisUserId'] = $thisUser['id'];
$futureShows = $shows->retriveAllfutureShow($thisUser['id']);
$data['allBookings'] = array();
$numberOfShows = count($futureShows);
if ($numberOfShows) {
    $data['allBookings'] = $booking->getBookings($shows->retriveAllfutureShow($thisUser['id']));
    if ($numberOfShows == 1) {
        $head = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_header_print.php');
        $tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'print_view.php', $data);
        $footer['includeFooter'] = false;
        $foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php', $footer);
        PrintPdf::createPdf($head->render() . $tmpl->render() . $foot->render());
    } else {
        $head = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_meta_head.php');
        echo $head->render();
        $loginData['isLogged'] = true;
        $loginData['isAdmin'] = $thisUser['access_level'] == 0;
        $loginData['isCompanyAdmin'] = $thisUser['is_company_admin'];
        $loginData['thispage'] = "print";
        $menu = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_navmenu.php', $loginData);
        echo $menu->render();
        $data['futureShow'] = $shows->retriveAllfutureShow($thisUser['id']);
        $tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'print_list_view.php', $data);
        echo $tmpl->render();
        $footer['includeFooter'] = true;
        $foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php', $footer);
        echo $foot->render();
    }
}
