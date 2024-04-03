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

        return $r;
    }
    
    function getAllCompanies(){
        $stmt = $this->db->prepare("SELECT * "
                . "FROM theatre_companies tc "
                . "ORDER BY tc.nome ASC;");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    function getCompany($id){
        $stmt = $this->db->prepare("SELECT * "
                . "FROM theatre_companies tc "
                . "WHERE id = ?;");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0];
    }
}
