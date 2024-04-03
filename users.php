<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Shows.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Users.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Login.php';

$login = new Login();

$login->isAuth();


$users = new Users();
$thisUser = $users->getUserFromLogin($_SESSION['session_user']);
$loginData['isLogged'] = true;
$loginData['isAdmin'] = $thisUser['access_level'] == 0;
$loginData['isCompanyAdmin'] = $thisUser['is_company_admin'];
$loginData['thispage'] = "user";


$shows = new Shows();
if (filter_input(INPUT_POST, 'f') != null) {
    if (in_array(filter_input(INPUT_POST, 'f'), array('au', 'uu', 'du'))) {
        $r = $users->handle();
    }
}

if ($loginData['isAdmin'] ){
    $data['usersInScope'] = $users->getAllUsers();
}else if ($loginData['isCompanyAdmin']){
    $data['usersInScope'] = $users->getAllUsersByCompany($thisUser['company']);
}
foreach ($data['usersInScope'] as $element) {
    $usersIdInScope[] = $element['id'];
}

if (filter_input(INPUT_GET, 'ui') != null && 
        filter_input(INPUT_POST, 'f')!= 'du' &&
        in_array(filter_input(INPUT_GET, 'ui'),$usersIdInScope)) {
    $data['userToModify'] = $users->getUser(filter_input(INPUT_GET, 'ui'));
    $data['userToModify']['company'] = array_intersect_key($data['userToModify']['company'], $thisUser['company']);
}

$data['isAdmin'] = $thisUser['access_level'] == 0;
$data['isCompanyAdmin'] = $thisUser['is_company_admin'];
$data['companies']= $thisUser['company'];
$data['userName'] = $thisUser['name'];
$data['thisUserId'] = $thisUser['id'];
$data['isAdmin'] = $thisUser['is_company_admin'];
$data['showUserMap'] = $shows->getShowInUserScope($data['usersInScope']);
$data['futureShow'] = $shows->retriveAllfutureShow($thisUser['id']);

$tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_navmenu.php', $loginData);
echo $tmpl->render();
$content = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'users_view.php', $data);
echo $content->render();
$foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php');
echo $foot->render();
