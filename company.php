<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'RenderTemplate.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Company.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Users.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Login.php';

$login = new Login();

$login->isAuth();


$users = new Users();
$thisUser = $users->getUserFromLogin($_SESSION['session_user']);
$loginData['isLogged'] = true;
$loginData['isAdmin'] = $thisUser['access_level'] == 0;
$loginData['isCompanyAdmin'] = $thisUser['is_company_admin'];
$loginData['thispage'] = "company";


$company = new Company();
if (filter_input(INPUT_POST, 'f') != null) {
    if (in_array(filter_input(INPUT_POST, 'f'), array('ac', 'uc', 'dc'))) {
        $company->handle();
    }
}

if ($loginData['isAdmin'] ){
    $data['companies'] = $company->getAllCompanies();
    $data['users'] = $users->getAllUsers();
}

if (filter_input(INPUT_GET, 'cu') != null && 
        filter_input(INPUT_POST, 'f') != 'dc') {
    $data['companyToModify'] = $company->getCompany(filter_input(INPUT_GET, 'cu'));
}

$data['isAdmin'] = $thisUser['access_level'] == 0;
$data['isCompanyAdmin'] = $thisUser['is_company_admin'];
$data['userName'] = $thisUser['name'];
$data['thisUserId'] = $thisUser['id'];
$data['isAdmin'] = $thisUser['is_company_admin'];

$tmpl = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_navmenu.php', $loginData);
echo $tmpl->render();
$content = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'companies_view.php', $data);
echo $content->render();
$footer['includeFooter'] = true;
$foot = new RenderTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'part_foot.php',$footer);
echo $foot->render();
