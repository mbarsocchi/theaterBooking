<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Shows.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Users.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Login.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Company.php';

$login = new Login();
$users = new Users();
$comp = new Company();
$shows = new Shows();

$login->isAuth();

$thisUser = $users->getUserFromLogin($_SESSION['session_user']);
$numberOfUsersInScope = count( $users->getUsersInScope($thisUser['id']));
$loginData['isLogged'] = true;
$loginData['isAdmin'] = $thisUser['access_level'] == 0;
$loginData['isCompanyAdmin'] = $numberOfUsersInScope> 1;
$loginData['thispage'] = "user";

if (filter_input(INPUT_POST, 'f') != null) {
    if (in_array(filter_input(INPUT_POST, 'f'), array('au', 'uu', 'du'))) {
        $users->handle();
    }
}

if ($loginData['isAdmin']) {
    $data['usersInScope'] = $users->getAllUsers();
} else if ($loginData['isCompanyAdmin']) {
    $data['usersInScope'] = $users->getAllUsersByCompany($thisUser['company']);
} else {
    header('Location: booking.php');
}
$userCompanies = $users->getCompanyForUser($thisUser['id']);
$data['companyICanAdmin'] = $userCompanies['adminArray'];

foreach ($data['usersInScope'] as $element) {
    $usersIdInScope[] = $element['id'];
}
foreach ($thisUser['company'] as $compId => $compData) {
    if ($compData['isCompanyAdmin']) {
        $companyResult[$compId] = $compData;
    }
}

if ($loginData['isAdmin']) {
    $data['companies'] = $comp->getAllCompanies();
} else if ($loginData['isCompanyAdmin']) {
    $data['companies'] = $comp->getallManagedCompany($thisUser['id']);
}

if (filter_input(INPUT_GET, 'ui') != null &&
        filter_input(INPUT_POST, 'f') != 'du' &&
        in_array(filter_input(INPUT_GET, 'ui'), $usersIdInScope)) {

    $data['userToModify'] = $users->getUser(filter_input(INPUT_GET, 'ui'));
    $data['userToModify']['company'] = $comp->companyDataForUsesAndCompany($data['companies'], $data['userToModify']['company']);
}
$data['isAdmin'] = $thisUser['access_level'] == 0;
$data['isCompanyAdmin'] = $numberOfUsersInScope > 1;
$data['userName'] = $thisUser['name'];
$data['thisUserId'] = $thisUser['id'];
$data['showUserMap'] = $shows->getShowInUserScope($data['usersInScope']);
$data['futureShow'] = $shows->retriveAllfutureShow($thisUser['id']);

$tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_navmenu.php', $loginData);
echo $tmpl->render();
$content = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'users_view.php', $data);
echo $content->render();
$footer['includeFooter'] = true;
$foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php',$footer);
echo $foot->render();
