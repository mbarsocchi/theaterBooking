<?php

/**
 * Description of Company
 *
 * @author mbarsocchi
 */
class Company {

    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    function handle() {
        $r = null;
        $arryOfUsers = isset($_POST['user']) ? $_POST['user'] : array();
        $companyAdmin = isset($_POST['companyAdmin']) ? $_POST['companyAdmin'] : array();
        switch (filter_input(INPUT_POST, 'f')) {
            case 'ac':
                $r = $this->insertCompany(filter_input(INPUT_POST, 'name'), $arryOfUsers, $companyAdmin);
                break;
            case 'uc':
                $r = $this->updateCompany(filter_input(INPUT_POST, 'id'), filter_input(INPUT_POST, 'name'), $arryOfUsers, $companyAdmin);
                break;
            case 'dc':
                $this->deleteCompany(filter_input(INPUT_POST, 'id'));
            default:
                break;
        }
        header('Location: company.php');
    }
    private function validateField($name){
        if (!isset($name) || $name == "") {
            return "Il nome della compagnia non può essere vuoto";
        }

    }
    
    function insertCompany($name, $arryOfUsers, $companyAdmin) {
        $validate = $this->validateField($name);
        if (isset($validate)) {
            echo "<h2>" . $validate . "</h2>";
        }
        $stmt = $this->db->prepare("INSERT INTO theatre_companies (name) "
                . "VALUES (?)");
        $name =trim($name);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $companyId = $stmt->insert_id;

        foreach ($arryOfUsers as $userID) {
            if (in_array($userID, $companyAdmin)) {
                $stmt = $this->db->prepare("INSERT INTO companies_users (company_id, user_id,is_company_admin) "
                        . "VALUES (?,?,1)");
                $ci = intval($companyId);
                $stmt->bind_param("ii", $ci, $userID);
            } else {
                $stmt = $this->db->prepare("INSERT INTO companies_users (company_id, user_id) "
                        . "VALUES (?,?)");
                $ci = intval($companyId);
                $stmt->bind_param("ii", $ci, $userID);
            }
            $stmt->execute();
            $companyId = $stmt->insert_id;
        }
    }

    function deleteCompany($companyId) {
        $stmt = $this->db->prepare("DELETE FROM theatre_companies "
                . "WHERE id=?");
        $stmt->bind_param("i", $companyId);
        $stmt->execute();
    }

    function updateCompany($companyId, $name, $arryOfUsers, $companyAdmin) {
        $validate = $this->validateField($name);
        if (isset($validate)) {
            echo "<h2>" . $validate . "</h2>";
        }
        $stmt = $this->db->prepare("UPDATE theatre_companies 
            SET name=?
            WHERE id=?");
        $name=trim($name);
        $stmt->bind_param("si", $name, $companyId);
        $stmt->execute();
        $currentUserAndAdmin = $this->getUsersOfACompany($companyId);

        foreach ($currentUserAndAdmin as $currentConfiguration) {
            // the user is in the currentCompany but not in the user to add so i want to remove it 
            if (!in_array($currentConfiguration['user_id'], $arryOfUsers)) {
                $stmt = $this->db->prepare("DELETE FROM companies_users "
                        . "WHERE company_id=? "
                        . "AND user_id = ? ");
                $ci = intval($companyId);
                $stmt->bind_param("ii", $companyId, $currentConfiguration['user_id']);
                $stmt->execute();
            } else if (in_array($currentConfiguration['user_id'], $companyAdmin) && !$currentConfiguration['is_company_admin']) {
                // the user is in the currentCompany, is not an admin, but I want to be an admin
                $stmt = $this->db->prepare("UPDATE companies_users 
                    SET is_company_admin=1
                    WHERE user_id=? 
                    AND company_id=?");
                $ci = intval($companyId);
                $stmt->bind_param("ii", $currentConfiguration['user_id'], $ci);
                $stmt->execute();
            }
            $currentUserId[] = $currentConfiguration['user_id'];
            if ($currentConfiguration['is_company_admin']) {
                $currentAdminId[] = $currentConfiguration['user_id'];
            }
        }
        // add user not in the company
        $toAddAsUser = !isset($currentUserId) ? $arryOfUsers : array_diff($arryOfUsers, $currentUserId);
        foreach ($toAddAsUser as $userToAdd) {
            $admin = in_array($userToAdd, $companyAdmin);
            $stmt = $this->db->prepare("INSERT INTO companies_users (company_id, user_id,is_company_admin) "
                    . "VALUES (?,?,?)");
            $ci = intval($companyId);
            $stmt->bind_param("iii", $ci, $userToAdd, $admin);
            $stmt->execute();
        }
        // remove admin role for removed admin
        $toRemoveAsAdmin = !isset($currentAdminId) ? array() : array_diff($currentAdminId, $companyAdmin);
        foreach ($toRemoveAsAdmin as $userToRemoveAdmin) {
            $stmt = $this->db->prepare("UPDATE companies_users 
                    SET is_company_admin=0
                    WHERE user_id=? 
                    AND company_id=?");
            $ci = intval($companyId);
            $stmt->bind_param("ii", $userToRemoveAdmin, $ci);
            $stmt->execute();
        }
    }

    function getAllCompanies() {
        $stmt = $this->db->prepare("SELECT * "
                . "FROM theatre_companies tc "
                . "ORDER BY tc.name ASC;");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function companyDataForUsesAndCompany($allAdminCompanies, $companyForUser) {
        $resultedArray = array();
        foreach ($allAdminCompanies as $data) {
            $resultedArray[$data['id']]['inThisCompany'] = isset($companyForUser[$data['id']]);
            $resultedArray[$data['id']]['name'] = $data['name'];
            if ($resultedArray[$data['id']]['inThisCompany']) {
                $resultedArray[$data['id']]['isCompanyAdmin'] = $companyForUser[$data['id']]['isCompanyAdmin'];
            } else {
                $resultedArray[$data['id']]['isCompanyAdmin'] = 0;
            }
        }
        return $resultedArray;
    }

    function getCompany($id) {
        $stmt = $this->db->prepare("SELECT tc.id,tc.name,u.id as userid, u.name as username "
                . "FROM theatre_companies tc "
                . "LEFT JOIN companies_users cu ON cu.company_id = tc.id "
                . "LEFT JOIN users u ON u.id = cu.user_id "
                . "WHERE tc.id = ?;");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0];
    }

    function getallManagedCompany($userId) {
        $stmt = $this->db->prepare("SELECT tc.id,tc.name "
                . "FROM theatre_companies tc "
                . "LEFT JOIN companies_users cu ON cu.company_id = tc.id "
                . "LEFT JOIN users u ON u.id = cu.user_id "
                . "WHERE u .id = ? "
                . "AND cu.is_company_admin = 1 "
                . "ORDER by tc.name ASC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getUsersOfACompany($companyId) {
        $stmt = $this->db->prepare("SELECT user_id,u.name, is_company_admin "
                . "FROM companies_users cu "
                . "JOIN users u ON u.id = cu.user_id "
                . "WHERE cu.company_id = ?;");
        $stmt->bind_param("i", $companyId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

}
