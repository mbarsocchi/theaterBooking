<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'Company.php';

class Users {

    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    function handle() {
        $r = null;
        $arryOfShows = isset($_POST['show']) ? $_POST['show'] : array();
        $arrayOfcompany = isset($_POST['company']) ? $_POST['company'] : array();
        $arrayOfiscompanyadmin = isset($_POST['iscompanyadminArr']) ? $_POST['iscompanyadminArr'] : array();
        switch (filter_input(INPUT_POST, 'f')) {
            case 'au':
                $r = $this->insertUser(filter_input(INPUT_POST, 'name'), filter_input(INPUT_POST, 'login'), filter_input(INPUT_POST, 'password'), $arryOfShows, $arrayOfcompany, $arrayOfiscompanyadmin);
                break;
            case 'uu':
                $r = $this->updateUser(filter_input(INPUT_POST, 'id'), filter_input(INPUT_POST, 'name'), filter_input(INPUT_POST, 'login'), filter_input(INPUT_POST, 'password'), $arryOfShows, $arrayOfiscompanyadmin, filter_input(INPUT_POST, 'iscompanyadmin'), $arrayOfcompany);
                break;
            case 'du':
                $this->deleteUser(filter_input(INPUT_POST, 'id'));
            default:
                break;
        }
        return $r;
    }

    function getUser($id) {
        $stmt = $this->db->prepare("SELECT u.id, u.name, user_login, company_id, tc.name as companyname, is_company_admin "
                . "FROM users u "
                . "LEFT JOIN companies_users cu ON u.id = cu.user_id "
                . "LEFT JOIN theatre_companies tc ON tc.id = cu.company_id "
                . "WHERE u.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $queryResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        if (count($queryResults) == 0) {
            $result = null;
        } else {
            $result['id'] = $queryResults[0]['id'];
            $result['name'] = $queryResults[0]['name'];
            $result['user_login'] = $queryResults[0]['user_login'];
            $result['company'] = array();
            foreach ($queryResults as $userData) {
                if ($userData['is_company_admin'] !== null) {
                    $result['company'][$userData['company_id']]['name'] = $userData['companyname'];
                    $result['company'][$userData['company_id']]['isCompanyAdmin'] = $userData['is_company_admin'];
                }
            }
        }
        return $result;
    }

    function getUserFromLogin($name) {
        $stmt = $this->db->prepare("SELECT u.id, u.name, u.user_login, u.access_level, cu.is_company_admin,tc.name as companyname,tc.id as companyid "
                . "FROM users u "
                . "LEFT JOIN companies_users cu ON cu.user_id = u.id "
                . "LEFT JOIN theatre_companies tc ON tc.id = cu.company_id "
                . "WHERE user_login = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $queryResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $r['is_company_admin'] = false;
        $r['company'] = array();
        if (count($queryResults) == 0) {
            $r = null;
        } else {
            $r['id'] = $queryResults[0]['id'];
            $r['name'] = $queryResults[0]['name'];
            $r['user_login'] = $queryResults[0]['user_login'];
            $r['access_level'] = $queryResults[0]['access_level'];
            foreach ($queryResults as $userData) {
                if ($userData['is_company_admin'] !== null) {
                    $r['is_company_admin'] = $r['is_company_admin'] || $userData['is_company_admin'];
                    $r['company'][$userData['companyid']]['name'] = $userData['companyname'];
                    $r['company'][$userData['companyid']]['isCompanyAdmin'] = $userData['is_company_admin'];
                }
            }
        }
        return $r;
    }

    function getAllUsers() {
        $stmt = $this->db->prepare("SELECT DISTINCT u.id, u.name, u.user_login, cu.is_company_admin,tc.name as companyname,tc.id as companyid  "
                . "FROM users u "
                . "LEFT JOIN companies_users cu ON cu.user_id = u.id "
                . "LEFT JOIN theatre_companies tc ON tc.id = cu.company_id "
                . "ORDER BY u.name ASC;");
        $stmt->execute();
        $queryResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $userUsedTemp = array();
        foreach ($queryResults as $user) {
            if (!in_array($user['id'], $userUsedTemp)) {
                array_push($userUsedTemp, $user['id']);
                $r[$user['id']]['id'] = $user['id'];
                $r[$user['id']]['name'] = $user['name'];
                $r[$user['id']]['user_login'] = $user['user_login'];
            }
            $r[$user['id']]['company'][$user['companyid']]['name'] = $user['companyname'];
            $r[$user['id']]['company'][$user['companyid']]['is_company_admin'] = $user['is_company_admin'];
        }
        foreach ($r as $t) {
            $result[] = $t;
        }
        return $result;
    }

    function getUsersInScope($id) {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("SELECT DISTINCT u.id, u.name, u.user_login, u.access_level "
                . "FROM users u "
                . "JOIN users_shows us ON u.id = us.user_id "
                . "WHERE show_id IN (SELECT show_id FROM users_shows us "
                . "JOIN spettacoli s ON s.id = us.show_id "
                . "WHERE user_id = ? "
                . "AND s.data >= ?)"
                . "ORDER BY u.name ASC;");
        $stmt->bind_param("is", $id, $now);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function getAllUsersByCompany($companiesArray) {
        foreach ($companiesArray as $id => $companyData) {
            if ($companyData['isCompanyAdmin']) {
                $companiesIds[] = $id;
            }
        }
        $implodedString = implode(',', $companiesIds);
        $stmt = $this->db->prepare("SELECT u.id, u.name, u.user_login, cu.company_id, cu.is_company_admin
FROM companies_users cu 
JOIN users u ON u.id = cu.user_id
WHERE cu.company_id IN (" . $implodedString . ") 
GROUP by u.id 
ORDER BY u.name ASC;");

        $stmt->execute();
        $returned = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return $returned;
    }

    function isAdmin($id) {
        $result = false;
        $stmt = $this->db->prepare("SELECT access_level "
                . "FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $queryResult = $stmt->get_result();
        if ($queryResult->num_rows > 0) {
            while ($row = $queryResult->fetch_assoc()) {
                $result = $row["access_level"] == 0;
                break;
            }
        }
        return $result;
    }

    function insertUser($name, $user_login, $passwordClear, $showsArray, $arrayOfcompany, $arrayOfiscompanyadmin) {
        $stmt = $this->db->prepare("INSERT INTO users (name, user_login, password) "
                . "VALUES (?,?,?)");
        $hash = md5($passwordClear);
        $stmt->bind_param("sss", $name, $user_login, $hash);
        $stmt->execute();
        $userId = $stmt->insert_id;
        foreach ($showsArray as $showId) {
            $stmt = $this->db->prepare("INSERT INTO users_shows (show_id, user_id) "
                    . "VALUES (?,?)");
            $si = intval($showId);
            $stmt->bind_param("ii", $si, $userId);
            $stmt->execute();
        }
        echo "company che vorrei dargli a userid: ".$userId;
        print_r($arrayOfcompany);
        echo "di queste sono admin";
        print_r($arrayOfiscompanyadmin);
        foreach ($arrayOfcompany as $companyId) {
            $companyAdminToInsert = in_array($companyId, $arrayOfiscompanyadmin)?1:0;
            $ci = intval($companyId);
            echo "companyID: ".$ci." userId: ".$userId." isCompanyId:".$companyAdminToInsert;
            $stmt = $this->db->prepare("INSERT INTO companies_users (company_id, user_id, is_company_admin) "
                    . "VALUES (?,?,?)");
            $stmt->bind_param("iii", $ci, $userId, $companyAdminToInsert);
            $stmt->execute();
        }
    }

    function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    function updateUser($userId, $name, $user_login, $passwordClear, $showsArray, $arrayOfiscompanyadmin, $isCompanyAdmin, $companyForThisUser) {
        $currentCompaniesForUser = $this->getCompanyForUser($userId);

        foreach ($companyForThisUser as $companyId) {
            if (!in_array($companyId, $currentCompaniesForUser['adminArray']) && !in_array($companyId, $currentCompaniesForUser['nonAdminArray'])) {
                $compAdmin = in_array($companyId, $arrayOfiscompanyadmin) ? 1 : 0;
                $stmt = $this->db->prepare("INSERT INTO companies_users (company_id, user_id,is_company_admin) "
                        . "VALUES (?,?,?)");
                $ci = intval($companyId);
                $stmt->bind_param("iii", $ci, $userId, $compAdmin);
                $stmt->execute();
            }
        }

        $companyThatUserAlreadyHas = array_merge($currentCompaniesForUser['adminArray'], $currentCompaniesForUser['nonAdminArray']);
        foreach ($companyThatUserAlreadyHas as $companyId) {
            if (!in_array($companyId, $companyForThisUser)) {
                $stmt = $this->db->prepare("DELETE FROM companies_users "
                        . "WHERE company_id=? "
                        . "AND user_id = ? ");
                $ci = intval($companyId);
                $stmt->bind_param("ii", $ci, $userId);
                $stmt->execute();
            }
        }

        $currentShowsForUser = $this->getShowsForUser($userId);

        if ($passwordClear != null & $passwordClear != "") {
            $stmt = $this->db->prepare("UPDATE users 
            SET name=?, user_login=?, password=?
            WHERE id=?");
            $hash = md5($passwordClear);
            $stmt->bind_param("sssi", $name, $user_login, $hash, $userId);
        } else {
            $stmt = $this->db->prepare("UPDATE users 
            SET name=?, user_login=?
            WHERE id=?");
            $stmt->bind_param("ssi", $name, $user_login, $userId);
        }
        $stmt->execute();
        // add admin to company
        $toAddCompanyAdmin = array_unique(array_merge($currentCompaniesForUser['adminArray'], $arrayOfiscompanyadmin), SORT_REGULAR);
        foreach ($toAddCompanyAdmin as $theatre_company_id) {
            $stmt = $this->db->prepare("UPDATE companies_users 
            SET is_company_admin=1
            WHERE user_id=? 
            AND company_id=?");
            $stmt->bind_param("ii", $userId, $theatre_company_id);
            $stmt->execute();
        }

        // remove admin to company
        $toRemoveCompanyAdmin = array_diff($currentCompaniesForUser['adminArray'], $arrayOfiscompanyadmin);
        foreach ($toRemoveCompanyAdmin as $theatre_company_id) {
            $stmt = $this->db->prepare("UPDATE companies_users 
            SET is_company_admin=0
            WHERE user_id=? 
            AND company_id=?");
            $stmt->bind_param("ii", $userId, $theatre_company_id);
            $stmt->execute();
        }

        $toAddArr = array_diff($showsArray, $currentShowsForUser);
        foreach ($toAddArr as $showId) {
            $stmt = $this->db->prepare("INSERT INTO users_shows (show_id, user_id) "
                    . "VALUES (?,?)");
            $si = intval($showId);
            $stmt->bind_param("ii", $si, $userId);
            $stmt->execute();
        }
        $toDeleteArr = array_diff($currentShowsForUser, $showsArray);
        foreach ($toDeleteArr as $showId) {
            $stmt = $this->db->prepare("DELETE FROM users_shows "
                    . "WHERE show_id =? "
                    . "AND user_id = ?");
            $si = intval($showId);
            $stmt->bind_param("ii", $si, $userId);
            $stmt->execute();
        }
    }

    function getShowsForUser($userId) {
        $result = array();
        $stmt = $this->db->prepare("SELECT s.id "
                . "FROM spettacoli s "
                . "JOIN users_shows us ON us.show_id = s.id "
                . "AND us.user_id = ? "
                . "ORDER BY data ASC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $showId) {
            $result[] = $showId['id'];
        }
        return $result;
    }

    function getCompanyForUser($userId) {
        $result = array();
        $stmt = $this->db->prepare("SELECT tc.id, cu.is_company_admin "
                . "FROM theatre_companies tc "
                . "JOIN companies_users cu ON cu.company_id = tc.id "
                . "WHERE cu.user_id = ? "
                . "ORDER BY tc.name ASC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result['adminArray'] = array();
        $result['nonAdminArray'] = array();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $companyInfo) {
            if ($companyInfo['is_company_admin']) {
                $result['adminArray'][] = $companyInfo['id'];
            } else {
                $result['nonAdminArray'][] = $companyInfo['id'];
            }
        }
        return $result;
    }

}
