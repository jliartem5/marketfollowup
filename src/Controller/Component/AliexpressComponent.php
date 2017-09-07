<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\ConnectionManager;
use Cake\Core\Configure;

class AliexpressComponent extends Component
{
    private  $connection = null;
    private $market_name = 'aliexpress';

   public function initialize(array $config)
   {
       parent::initialize($config);
      $this->connection = ConnectionManager::get('default');
   }
   
   public function getByFilter(array $filter,$count = 100,$page=0, &$totalRow=0){
       
       $procedure_parameters = Configure::read('Procedure_Parameters.Products_Filter');

       $sql = 'call Products_Filter(';
       
       $sql = $sql."@market:='".$this->market_name."', ";
       
       
       foreach($procedure_parameters as $index=>$param){
           $param_name = $param['name'];
           $param_type =$param['type'];
		   $find = false;
		   if($param_type == 'Optionel'){
			
            foreach($filter as $key=>$value){
                if(strcasecmp($param_name,  $key)==0){
                    $sql =$sql."'".$value."',";
					$find = true;
					break;
                }
             }
			//rien trouvé, on met une valeur vide
			if($find == false){
				$sql = $sql."'',";
			}
		   }
       }
       if(substr($sql,-1)==','){
           $sql = substr($sql, 0, strlen($sql)-1);
       }
       $sql = $sql.',@count:='.$count;
       $sql = $sql.',@p:='.$page;
       $sql = $sql.')';
       $result_table = $this->connection->execute($sql)->fetchAll('assoc');
       $totalRow = $this->connection->execute('SELECT @__TotalRow__ as TotalRow')->fetchAll('assoc');
       return $result_table;
   }
   
   public function sql_execute($sql){
       $this->connection->execute($sql);
   }
   
   public function getProductsToFollow(){
       $products = $this->connection->execute('call Marketfollow_ProductsList("'.$this->market_name.'")')->fetchAll('assoc');
       return $products;
   }
   
    public function get($count = 100, $page = 0){
        $products = $this->connection->execute('call Products_Page("GetByPage", "'.$this->market_name.'", '.$count.','.$page.')')->fetchAll('assoc');
        return $products;
    }
    
    public function getPageCount($count = 100){
        return $this->connection->execute('call Products_Page("GetPageCount","'.$this->market_name.'",'.$count.',null)')->fetchAll('assoc')[0]['count'];
    }
    
}