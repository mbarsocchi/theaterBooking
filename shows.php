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
$loginData['thispage'] = "shows";
$head = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_head.php', $loginData);
echo $head->render();
$shows = new Shows();
if (filter_input(INPUT_POST, 'f') != null) {
    if (in_array(filter_input(INPUT_POST, 'f'), array('i', 'd', 'u'))) {
        $r = $shows->handleShows();
    }
}
if (filter_input(INPUT_GET, 'si') != null) {
    $data['showToModify'] = $shows->returnDataForSpettacoloId(filter_input(INPUT_GET, 'si'));
    if (isset($data['showToModify']['data'])) {
        $data['showToModify']['data'] = date("Y-m-d H:i", strtotime($data['showToModify']['data']));
    }
}
if (isset($r) && isset($r['erromessage'])) {
    $data['errors'] = $r['erromessage'];
}

$data['userName'] = $thisUser['name'];
$data['thisUserId'] = $thisUser['id'];
$data['futureShow'] = $shows->retriveAllfutureShow($thisUser['id']);

$tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_navmenu.php', $loginData);
echo $tmpl->render();
$content = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'shows_view.php', $data);
echo $content->render();
$foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php');
echo $foot->render();
