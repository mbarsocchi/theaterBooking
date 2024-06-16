<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Shows.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Users.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Company.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Booking.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Login.php';

$shows = new Shows();
$booking = new Booking();
$login = new Login();
$users = new Users();
$company = new Company();

$login->isAuth();
$thisUser = $users->getUserFromLogin($_SESSION['session_user']);

if (filter_input(INPUT_POST, 'f') != null && in_array(filter_input(INPUT_POST, 'f'), array('b', 'db', 'ub'))) {
    $booking->handleBooking($thisUser['id']);
}

$data['isAdmin'] = $thisUser['access_level'] == 0;
$futureShows = $shows->retriveAllfutureShow($thisUser['id']);
$allUsersForShows = $shows->getAllUsersForShows($futureShows);
$userCompanies = $users->getCompanyForUser($thisUser['id']);
$data['companyICanAdmin'] = $userCompanies['adminArray'];
$data['usersInScope'] = [];
$visibilityUser = [];
foreach ($futureShows as $show) {
    if (in_array($show['company_id'], $userCompanies['adminArray'])) {
        $visibilityUser[$show['company_id']] = $company->getUsersOfACompany($show['company_id']);
    }
}
$t = [];
foreach ($visibilityUser as $companyId => $allUsersOfACompany) {
   foreach ($allUsersOfACompany as $o){
       if (in_array($o['user_id'], $allUsersForShows) && ! isset($t[$o['user_id']]) ){
           $t[$o['user_id']] = $o;
       }
   } 
}
$data['usersInScope'] = $t;

$data['userName'] = $thisUser['name'];
$data['thisUserId'] = $thisUser['id'];
$data['allBookings'] = array();
if (count($futureShows)) {
    $data['allBookings'] = $booking->getBookings($futureShows);
}

$loginData['isLogged'] = true;
$loginData['isAdmin'] = $data['isAdmin'];
$loginData['thispage'] = "booking";
$iAmACompanyAdmin = count($userCompanies['adminArray'])>0;
$loginData['isCompanyAdmin']= $iAmACompanyAdmin;
$data['isCompanyAdmin']= $iAmACompanyAdmin;
$head = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_navmenu.php', $loginData);
echo $head->render();
$tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'booking_view.php', $data);
echo $tmpl->render();
$footer['includeFooter'] = true;
$foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php', $footer);
echo $foot->render();
