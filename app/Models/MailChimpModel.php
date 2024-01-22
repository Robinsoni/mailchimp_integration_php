<?php

namespace App\Models;

use CodeIgniter\Model;

class MailChimpModel extends Model
{
    protected $db;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    public function getApiConfig($account_id)
    {    
        $query = $this->db->table('api_config')
                    ->getWhere(['id'=> $account_id]);
        return $query->getRowArray();  
    }
    public function fetchAllListMember($list_id){ 
        return $this->db->table('members')
                    ->get( )-> getResultArray();
        
    }
    public function insertMembersToDB($data){
        return $this->db->table('members')->insertBatch($data);
    }
    
}