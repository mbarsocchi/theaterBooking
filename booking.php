<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Shows.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Users.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Database.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Booking.php';

define('UNIQUE_TICKET_COST', 11);
define('STOP_PRENO_HOUR', 19000);

$head = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_head.php');
echo $head->render();

$db = new Database();
$shows = new Shows($db);
$booking = new Booking($db);
if (filter_input(INPUT_POST, 'f') != null && in_array(filter_input(INPUT_POST, 'f'), array('b','db','ub'))) {
    $booking->handleBooking();
}

$users = new Users($db);
session_start();
if (!isset($_SESSION['session_user']) || $_SESSION['session_user'] == null || $_SESSION['session_user'] == "") {
    header('location: index.php');
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
$tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'booking_view.php', $data);
echo $tmpl->render();
echo '<a href="logout.php">esci</a>';
$foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php');
echo $foot->render();
