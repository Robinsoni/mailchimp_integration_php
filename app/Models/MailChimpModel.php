<?php

namespace App\Models;

use CodeIgniter\Model;

class MailChimpModel extends Model
{
   
    public function getApiConfig($account_id)
    {   
        $db = \Config\Database::connect();
        $query = $db->table('api_config')
                    ->getWhere(['id'=> $account_id]);
        return $query->getRowArray();  
    }
    
}