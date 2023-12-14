<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Database.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Shows.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Users.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Login.php';

$db = new Database();
$login = new Login($db);

$login->isAuth();


$users = new Users($db);
$thisUser = $thisUser = $users->getUserFromLogin($_SESSION['session_user']);
$data['isAdmin'] = $thisUser['access_level'] == 0;
$loginData['isLogged'] = true;
$loginData['isAdmin'] = $data['isAdmin'];
$loginData['thispage'] = "admin";
$head = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_head.php', $loginData);
echo $head->render();
$shows = new Shows($db);

if (filter_input(INPUT_POST, 'f') != null) {
    if (in_array(filter_input(INPUT_POST, 'f'), array('i', 'd', 'u'))) {
        $r = $shows->handleShows();
    }
    if (in_array(filter_input(INPUT_POST, 'f'), array('au', 'uu', 'du'))) {
        $r = $users->handle();
    }
}
if (isset($r) && isset($r['erromessage'])) {
    $data['errors'] = $r['erromessage'];
}
$data['userName'] = $thisUser['name'];
$data['isAdmin'] = true;
$data['thisUserId'] = $thisUser['id'];
$data['usersInScope'] = $users->getUsersInScope($thisUser['id']);
$data['showUserMap'] = $shows->getShowInUserScope($data['usersInScope']);
$data['futureShow'] = $shows->retriveAllfutureShow($thisUser['id']);

$loginData['isLogged'] = true;
$loginData['isAdmin'] = $data['isAdmin'];
$loginData['thispage'] = "admin";

$tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_navmenu.php', $loginData);
echo $tmpl->render();
$content = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'admin_view.php', $data);
echo $content->render();
$foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php');
echo $foot->render();
