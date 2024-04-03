<?php

class Users {

    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    function handle() {
        $r = null;
        $arryOfShows = isset($_POST['show']) ? $_POST['show'] : array();
        $arrayOfiscompanyadmin = isset($_POST['iscompanyadminArr']) ? $_POST['iscompanyadminArr'] : array();
        switch (filter_input(INPUT_POST, 'f')) {
            case 'au':
                $r = $this->insertUser(filter_input(INPUT_POST, 'name'), filter_input(INPUT_POST, 'login'), filter_input(INPUT_POST, 'password'), $arryOfShows, filter_input(INPUT_POST, 'iscompanyadmin'), filter_input(INPUT_POST, 'said'));
                break;
            case 'uu':
                $r = $this->updateUser(filter_input(INPUT_POST, 'id'), filter_input(INPUT_POST, 'name'), filter_input(INPUT_POST, 'login'), filter_input(INPUT_POST, 'password'), $arryOfShows, $arrayOfiscompanyadmin, filter_input(INPUT_POST, 'iscompanyadmin'));
                break;
            case 'du':
                $this->deleteUser(filter_input(INPUT_POST, 'id'));
            default:
                break;
        }
        return $r;
    }

    function getUser($id) {
        $stmt = $this->db->prepare("SELECT u.id, name, user_login, company_id, tc.nome, is_company_admin "
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
                    $result['company'][$userData['company_id']]['name'] = $userData['nome'];
                    $result['company'][$userData['company_id']]['isCompanyAdmin'] = $userData['is_company_admin'];
                }
            }
        }
        return $result;
    }

    function getUserFromLogin($name) {
        $stmt = $this->db->prepare("SELECT u.id, u.name, u.user_login, u.access_level, cu.is_company_admin,tc.nome as companyname,tc.id as companyid "
                . "FROM users u "
                . "LEFT JOIN companies_users cu ON cu.user_id = u.id "
                . "LEFT JOIN theatre_companies tc ON tc.id = cu.company_id "
                . "WHERE user_login = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $queryResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $r['is_company_admin'] = false;
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
        $stmt = $this->db->prepare("SELECT DISTINCT u.id, u.name, u.user_login, u.access_level "
                . "FROM users u "
                . "ORDER BY u.name ASC;");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
        $stmt = $this->db->prepare("SELECT u.id, u.name, u.user_login, cu.is_company_admin
FROM companies_users cu 
JOIN users u ON u.id = cu.user_id
WHERE cu.company_id IN (?)
ORDER BY u.name ASC;");
        $implodedString = implode(', ', $companiesIds);
        $stmt->bind_param("s", $implodedString);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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

    function insertUser($name, $user_login, $passwordClear, $showsArray, $isCompanyAdmin, $adminId = null) {
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
        if ($adminId != null) {
            $stmt = $this->db->prepare("SELECT company_id "
                    . "FROM companies_users "
                    . "WHERE user_id = ? "
                    . "AND is_company_admin = 1");
            $stmt->bind_param("i", $adminId);
            $stmt->execute();
            $queryResult = $stmt->get_result();
            if ($queryResult->num_rows > 0) {
                while ($row = $queryResult->fetch_assoc()) {
                    $companiesArray[] = $row["company_id"];
                    break;
                }
            }
            $companyAdminToInsert = $isCompanyAdmin == null? 0:1;
            foreach ($companiesArray as $companyId) {
                $stmt = $this->db->prepare("INSERT INTO companies_users (company_id, user_id, is_company_admin) "
                        . "VALUES (?,?,?)");
                $ci = intval($companyId);
                $stmt->bind_param("iii", $ci, $userId, $companyAdminToInsert);
                $stmt->execute();
            }
        }
    }

    function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    function updateUser($userId, $name, $user_login, $passwordClear, $showsArray, $arrayOfiscompanyadmin, $isCompanyAdmin) {
        $currentShowsForUser = $this->getShowsForUser($userId);
        $currentCompaniesForUser = $this->getCompanyForUser($userId);

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

        $toAddCompanyAdmin = array_unique(array_merge($currentCompaniesForUser['adminArray'], $arrayOfiscompanyadmin), SORT_REGULAR);
        foreach ($toAddCompanyAdmin as $theatre_company_id) {
            $stmt = $this->db->prepare("UPDATE companies_users 
            SET is_company_admin=1
            WHERE user_id=? 
            AND company_id=?");
            $stmt->bind_param("ii", $userId, $theatre_company_id);
            $stmt->execute();
        }

        $toRemoveCompanyAdmin = array_diff($currentCompaniesForUser['adminArray'],$arrayOfiscompanyadmin);
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
                . "ORDER BY tc.nome ASC");
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
