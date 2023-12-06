<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Database.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Shows.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Users.php';

$head = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_head.php');
echo $head->render();
$db = new Database();
$shows = new Shows($db);
$users = new Users($db);
session_start();
if (!isset($_SESSION['session_user']) || $_SESSION['session_user'] == null || $_SESSION['session_user'] == "") {
    header('location: index.php');
}


$thisUser = $thisUser = $users->getUserFromLogin($_SESSION['session_user']);
if ($thisUser != null && $thisUser['access_level'] === 0) {
    if (filter_input(INPUT_POST, 'f') != null) {
        if (in_array(filter_input(INPUT_POST, 'f'), array('i', 'd', 'u'))) {
            $r = $shows->handleShows();
        }
        if (in_array(filter_input(INPUT_POST, 'f'), array('au', 'uu', 'du'))) {
            $r = $users->handle();
        }
    }
    if (isset($r) && isset($r['erromessage'])){
        $data['errors'] = $r['erromessage'];
    }
    $data['userName'] = $thisUser['name'];
    $data['isAdmin'] = true;
    $data['thisUserId'] = $thisUser['id'];
    $data['usersInScope'] = $users->getUsersInScope($thisUser['id']);
    $data['showUserMap'] = $shows->getShowInUserScope($data['usersInScope']);
    $data['futureShow'] = $shows->retriveAllfutureShow($thisUser['id']);
    $tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'admin_view.php', $data);
    echo $tmpl->render();
} else {
    header('location: index.php');
}
echo '<a href="logout.php">esci</a>';
$foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php');
echo $foot->render();
