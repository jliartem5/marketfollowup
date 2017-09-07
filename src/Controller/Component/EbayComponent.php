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
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Trading\Services;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;

class EbayComponent extends Component {

    private $connection = null;
    private $market_name = 'ebay';

    public function initialize(array $config) {
        parent::initialize($config);
        $this->connection = ConnectionManager::get('default');
    }

    public function Get_LocalEbayInventory($id) {
        $result = $this->connection->execute("SELECT * FROM Inventory_ebay Where Id = '" . $id . "'")->fetchAll('assoc');
        if (count($result) > 0) {
            return $result[0];
        } else {
            return null;
        }
    }

    public function Revise_FixedPriceItem($annonceId, $prix, $quantite, $isLocal = false) {

        $errors = array();
        if ($isLocal) {
            try {
                $this->connection->execute("UPDATE Inventory_ebay SET Quantite_Disponible = '$quantite', Prix='$prix' WHERE Annonce_Id='$annonceId'");
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        } else {
            $siteId = Constants\SiteIds::FR;
            $service = new Services\TradingService([
                'siteId' => $siteId
            ]);
            /**
             * Create the request object.
             */
            $request = new Types\ReviseFixedPriceItemRequestType();
            /**
             * An user token is required when using the Trading service.
             */
            $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
            $request->RequesterCredentials->eBayAuthToken = getenv('EBAY_AuthToken');

            $item = new Types\ItemType();
            /**
             * Tell eBay which item we are revising.
             */
            $item->ItemID = $annonceId;
            if ($quantite != null) {
                $item->Quantity = $quantite;
            }
            if ($prix != null) {
                $item->StartPrice = new Types\AmountType(['value' => $prix]);
            }

            /**
             * Finish the request object.
             */
            $request->Item = $item;
            /**
             * Send the request.
             */
            $response = $service->reviseFixedPriceItem($request);

            /**
             * Output the result of calling the service operation.
             */
            if (isset($response->Errors)) {
                foreach ($response->Errors as $error) {
                    $errors[] = $error;
                }
            }

            if ($response->Ack !== 'Failure') {
                return true;
            }
        }
        return $errors;
    }

	
   public function sql_execute($sql){
       $this->connection->execute($sql);
   }
	
	
    public function getProductsToFollow() {
        $products = $this->connection->execute('call Marketfollow_ProductsList("' . $this->market_name . '")')->fetchAll('assoc');
        return $products;
    }

    public function get($count = 100, $page = 0) {
        $products = $this->connection->execute('call Products_Page("GetByPage", "' . $this->market_name . '", ' . $count . ',' . $page . ')')->fetchAll('assoc');
        return $products;
    }

    public function getPageCount($count = 100) {
        return $this->connection->execute('call Products_Page("GetPageCount","' . $this->market_name . '",' . $count . ',null)')->fetchAll('assoc')[0]['count'];
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

}
