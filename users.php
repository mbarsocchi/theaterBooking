<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Shows.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Users.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Login.php';

$login = new Login();

$login->isAuth();


$users = new Users();
$thisUser = $thisUser = $users->getUserFromLogin($_SESSION['session_user']);
$data['isAdmin'] = $thisUser['access_level'] == 0;
$loginData['isLogged'] = true;
$loginData['isAdmin'] = $data['isAdmin'];
$loginData['thispage'] = "user";
$head = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_head.php', $loginData);
echo $head->render();
$shows = new Shows();
if (filter_input(INPUT_POST, 'f') != null) {
    if (in_array(filter_input(INPUT_POST, 'f'), array('au', 'uu', 'du'))) {
        $r = $users->handle();
    }
}
if (filter_input(INPUT_GET, 'ui') != null && filter_input(INPUT_POST, 'f')!= 'du') {
    $data['userToModify'] = $users->getUser(filter_input(INPUT_GET, 'ui'));
}


$data['userName'] = $thisUser['name'];
$data['thisUserId'] = $thisUser['id'];
$data['usersInScope'] = $users->getAllUsers();
$data['showUserMap'] = $shows->getShowInUserScope($data['usersInScope']);
$data['futureShow'] = $shows->retriveAllfutureShow($thisUser['id']);



$tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_navmenu.php', $loginData);
echo $tmpl->render();
$content = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'users_view.php', $data);
echo $content->render();
$foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php');
echo $foot->render();
