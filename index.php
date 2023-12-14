<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Login.php';

$login = new Login();

$login->isAuth(false);
$login->handleLogin();

$head = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_meta_head.php');
echo $head->render();

$lv = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'login_view.php');
echo $lv->render();

$foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php');
echo $foot->render();
