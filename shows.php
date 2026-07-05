<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Login.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Shows.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Users.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Company.php';


$users = new Users();
$thisUser = $thisUser = $users->getUserFromLogin($_SESSION['session_user']);
$companies = new Company();
$loginData['isLogged'] = true;
$isSuperAdmin = $thisUser['access_level'] == 0;
$atLeastOneCompanyAdmin = false;
if(isset($thisUser['company'] )){
    foreach ($thisUser['company'] as $company) {
        if ($company['isCompanyAdmin'] == 1) {
           $atLeastOneCompanyAdmin = true;
        }
    }
}
if ($isSuperAdmin) {
    $data['companies'] = $companies->getAllCompanies();
} else if ($isSuperAdmin ||$atLeastOneCompanyAdmin) {
    $data['companies'] = $companies->getallManagedCompany($thisUser['id']);
}

$iAmACompanyAdmin = count($data['companies'])>0;
$loginData['isAdmin']= $iAmACompanyAdmin;
$data['isCompanyAdmin']= $iAmACompanyAdmin;
$loginData['thispage'] = "shows";

$shows = new Shows();

if (filter_input(INPUT_POST, 'f') != null) {
    if (in_array(filter_input(INPUT_POST, 'f'), array('i', 'd', 'u'))) {
        $shows->handleShows();
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
$data['isAdmin'] = $isSuperAdmin;
$data['userName'] = $thisUser['name'];
$data['thisUserId'] = $thisUser['id'];
$data['futureShow'] = $shows->retriveAllfutureShowICanManage($thisUser['id']);

$tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_navmenu.php', $loginData);
echo $tmpl->render();
$content = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'shows_view.php', $data);
echo $content->render();
$footer['includeFooter'] = true;
$foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php',$footer);
echo $foot->render();
