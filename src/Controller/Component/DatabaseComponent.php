<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller\Component;
use Cake\Datasource\ConnectionManager;
use Cake\Controller\Component;
class DatabaseComponent extends Component
{    
    private $connection = null;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->connection = ConnectionManager::get('default');
    }
    
    public function BulkInsert(array $data, $table){
        
        foreach($data as $index => $row){
            $sql = "INSERT INTO $table(".implode(',',array_keys($row)).") VALUES ('";
            $sql.=implode("','",array_values( $row))."')";
            $this->connection->execute($sql);
        }
    }
    
    public function BulkUpdate(array $updateData,  $whereClause, $table){
        
        foreach($updateData as $index=>$row){
            $sql = "UPDATE $table SET ";
            foreach($row as $col=>$val){
                $sql .= $col ."=".($val == null ? "null":"'".$val."'").",";
            }
            $sql = substr($sql, 0, -1);
            $sql .= " WHERE ".$whereClause;
           
            $this->connection->execute($sql);
        }
    }
    
    
    public function CallProcedure($procedureName){
        $this->connection->execute('CALL '.$procedureName.'()');
    }
}
