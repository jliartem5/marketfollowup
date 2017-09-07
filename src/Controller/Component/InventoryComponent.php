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

class InventoryComponent extends Component
{
    private  $connection = null;
    
    public function initialize(array $config)
    {
      parent::initialize($config);
      $this->connection = ConnectionManager::get('default');
    }
    
    public function GetInventoryVariations(){
        $variations = $this->connection->execute("call Inventory_Variation('AllVariation')")->fetchAll('assoc');
        return $variations;
    }
   
    public function GetLocalInventory(){
        $dataTable = $this->connection->execute("SELECT E.*, M.Id as Match_Id, Inventory_Ebay, Inventory_Priceminister, Inventory_Cdiscount FROM minielectro.inventory_ebay E
                        INNER JOIN inventory_match M ON M.Inventory_Ebay = E.Id")->fetchAll("assoc");
        return $dataTable;
    }
     
    public function GetRemoteInventory(){
        /**
         * Create the service object.
         */
        $service = new Services\TradingService(
                ['siteId' => Constants\SiteIds::FR]
        );
        /**
         * Create the request object.
         */
        $request = new Types\GetMyeBaySellingRequestType();
        /**
         * An user token is required when using the Trading service.
         */
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = getenv('EBAY_AuthToken');
        /**
         * Request that eBay returns the list of actively selling items.
         * We want 10 items per page and they should be sorted in descending order by the current price.
         */
        $request->ActiveList = new Types\ItemListCustomizationType();
        $request->ActiveList->Include = true;
        $request->ActiveList->Pagination = new Types\PaginationType();
        $request->ActiveList->Pagination->EntriesPerPage = 100;
        $request->ActiveList->Sort = Enums\ItemSortTypeCodeType::C_CURRENT_PRICE_DESCENDING;
        $pageNum = 1;
        
        $result = array();
        
        do {
            $request->ActiveList->Pagination->PageNumber = $pageNum;
            /**
             * Send the request.
             */
            $response = $service->getMyeBaySelling($request);
            /**
             * Output the result of calling the service operation.
             */
            if (isset($response->Errors)) {
                foreach ($response->Errors as $error) {
                    printf(
                            "%s: %s\n%s\n\n", $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning', $error->ShortMessage, $error->LongMessage
                    );
                }
            }
            if ($response->Ack !== 'Failure' && isset($response->ActiveList)) {
                foreach ($response->ActiveList->ItemArray->Item as $item) {
                    /**
                     * Ignorer les annonces avec variations
                     **/
                    if (isset($item->Variations) == false){
                        $result[] = $item;
                    }
                }
            }
            $pageNum += 1;
        } while (isset($response->ActiveList) && $pageNum <= $response->ActiveList->PaginationResult->TotalNumberOfPages);
        
        return $result;
    }
    
}