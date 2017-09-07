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

class PotentialComponent extends Component {

    private $connection = null;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->connection = ConnectionManager::get('default'); 
    }
    
    public function deletePotential($product){
        
        $this->connection->execute('call Potentiel_AliexpressEbay("Delete_Product", '.$product.' , -1)');
        
    }
	
	public function deleteBySearchComposite($searchcompo){
        $this->connection->execute("call Potentiel_AliexpressEbay('Delete_Searchcompo_".$searchcompo."', -1 , -1)");
	
	}

    public function getPotential($page, &$product_count=0,  &$totalProduct=0) {
        $potentials = $this->connection->execute('call Potentiel_AliexpressEbay("Get_Potentials", -1 , ' . $page . ')')->fetchAll('assoc');
        $totalProduct = $this->connection->execute('select @__TotalRow__ as totalRow')->fetchAll('assoc')[0]['totalRow'];
        
        $result =  $this->mergeResult($potentials);
        $product_count = count($result) ;
        return $result;
    }

    public function getBySearchword($searchword){
        $potentials = $this->connection->execute("call Potentiel_SearchComposite('Get_PotentialBySearchComposit', '" . $searchword . "')")->fetchAll('assoc');
        return $this->mergeResult($potentials);
       
    }
    
    private function mergeResult($sqlResult){
        
         //Restructurer la table : devenir array a 3 dimensions
        $result = array();
        $aliexpress_id = null;
        $search_composite = null;
        
        foreach ($sqlResult as $potential) {
            $cur_id = $potential['Id_Product_Aliexpress'];
 
            if ($cur_id != $aliexpress_id) {
                $result[$cur_id] = array();
            }
            
            $cur_search = $potential['Search_Composite'];

            if (array_key_exists($cur_search, $result[$cur_id]) == false) {
                $result[$cur_id][$cur_search] = array();
            }
            
            array_push($result[$cur_id][$cur_search], $potential);
            
            $aliexpress_id = $cur_id;
            $search_composite = $cur_search;
        }
        
        return $result;
        
    }
}
