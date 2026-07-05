<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Login.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Shows.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Users.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Company.php';


$users = new Users();
$comp = new Company();
$shows = new Shows();

$thisUser = $users->getUserFromLogin($_SESSION['session_user']);
$usersInScope = $users->getUsersInScope($thisUser['id']);
$numberOfUsersInScope = count($usersInScope);
$loginData['isLogged'] = true;
$isSuperAdmin = $thisUser['access_level'] == 0;
$loginData['isAdmin'] = $isSuperAdmin;
$atLeastOneCompanyAdmin = false;
if(isset($thisUser['company'] )){
    foreach ($thisUser['company'] as $company) {
        if ($company['isCompanyAdmin'] == 1) {
           $atLeastOneCompanyAdmin = true;
        }
    }
}
$loginData['isCompanyAdmin'] = $numberOfUsersInScope > 1;
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
    $data['futureShow'] =  $shows->retriveAllfutureShowForCompanies($data['userToModify']['company']);
    $data['userToModify']['company'] = $comp->companyDataForUsesAndCompany($data['companies'], $data['userToModify']['company']);
}else {
    if ($isSuperAdmin){
        //die("che show dovrei far vedere come superadmin???");
        $data['futureShow'] =[];
    }else if ($atLeastOneCompanyAdmin) {
        foreach ($data['companies'] as $compData) {
            $rearangedCompanies[$compData['id']]= $compData['name'];
        }
        $data['futureShow'] =  $shows->retriveAllfutureShowForCompanies( $rearangedCompanies);
    }else{
        $data['futureShow'] =  $shows->retriveAllfutureShow($thisUser['id']);
    }
}
$data['isAdmin'] = $isSuperAdmin;
$data['isCompanyAdmin'] = $numberOfUsersInScope > 1;
$data['userName'] = $thisUser['name'];
$data['thisUserId'] = $thisUser['id'];
$data['showUserMap'] = $shows->getShowInUserScope($data['usersInScope']);


$tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_navmenu.php', $loginData);
echo $tmpl->render();
$content = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'users_view.php', $data);
echo $content->render();
$footer['includeFooter'] = true;
$foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php',$footer);
echo $foot->render();
