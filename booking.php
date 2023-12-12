<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Shows.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Users.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Database.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Booking.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Login.php';



$db = new Database();
$shows = new Shows($db);
$booking = new Booking($db);
$login = new Login($db);
$users = new Users($db);

$login->isAuth(false);

if (filter_input(INPUT_POST, 'f') != null && in_array(filter_input(INPUT_POST, 'f'), array('b', 'db', 'ub'))) {
    $booking->handleBooking();
}

$thisUser = $users->getUserFromLogin($_SESSION['session_user']);
$data['isAdmin'] = $thisUser['access_level'] == 0;
if ($data['isAdmin']) {
    $data['usersInScope'] = $users->getUsersInScope($thisUser['id']);
}
$data['userName'] = $thisUser['name'];
$data['thisUserId'] = $thisUser['id'];
$futureShows = $shows->retriveAllfutureShow($thisUser['id']);
$data['allBookings'] = array();
if (count($futureShows)) {
    $data['allBookings'] = $booking->getBookings($shows->retriveAllfutureShow($thisUser['id']));
}
$loginData['isLogged'] = true;
$loginData['isAdmin'] = $data['isAdmin'];
$loginData['thispage'] = "booking";
$head = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_navmenu.php',$loginData);
echo $head->render();
$tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'booking_view.php', $data);
echo $tmpl->render();
$foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php');
echo $foot->render();
