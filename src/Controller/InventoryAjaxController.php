<?php

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\Datasource\ConnectionManager;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class InventoryAjaxController extends AppController {

    public function synchroniseInventoryEbay() {
        $this->viewBuilder()->setLayout('ajax');

        $datas = $this->getEbayInventory(true);

        $db = $this->loadComponent("Database");
        $result  = array();
        $connection = ConnectionManager::get('default');
        try{
            $connection->Execute('DELETE FROM inventory_ebay_temp');
            $db->BulkInsert($datas, "inventory_ebay_temp");
            $db->CallProcedure("Inventory_HandleTempTable");
            $result['result'] = 'success';
            
        }catch(\Exception $e){
            $result['result'] = 'failed';
            $result['errorMessage'] = $e->getMessage();
        }
        $this->set(['result'=>$result]);
    }
    
    public function finalizeInventory(){
        $this->viewBuilder()->setLayout('ajax');
        $db = $this->loadComponent("Inventory");
        $variations = $db->GetInventoryVariations();
        foreach($variations as $itemVariation){
            echo $itemVariation['Titre'].':'.'<br/>';
            //Ebay
            $ebay_variation = $itemVariation['RueDuCommerce_Inventory'] + $itemVariation['Priceminister_Inventory'] + $itemVariation['Cdiscount_Inventory'];
            echo 'Ebay variation :'.($ebay_variation);
            echo '<br/>';
            
            //Rue du commerce
            $rueducommerce_variation = $itemVariation['Ebay_Inventory'] + $itemVariation['Priceminister_Inventory'] + $itemVariation['Cdiscount_Inventory'];
            echo 'Rue du commerce variation :'.($rueducommerce_variation);
            echo '<br/>';
            
            //Priceminister
            $priceminister_variation = $itemVariation['RueDuCommerce_Inventory'] + $itemVariation['Ebay_Inventory'] + $itemVariation['Cdiscount_Inventory'];
            echo 'Priceminister variation :'.($priceminister_variation);
            echo '<br/>';
            
            //Cdiscount
            $cdiscount_variation = $itemVariation['RueDuCommerce_Inventory'] + $itemVariation['Ebay_Inventory'] + $itemVariation['Priceminister_Inventory'];
            echo 'Cdiscount variation :'.($cdiscount_variation);
            echo '<br/>';
            echo '<br/>';
        }
        
        $this->set([
            'result'=>'success'
        ]);
    }

    public function allEbayInventory() {

        $this->viewBuilder()->setLayout('ajax');
        $inventoryFrom = $this->params['url']['source'];

        $isRemote = true;
        if ($inventoryFrom == 'local' || $inventoryFrom == null) {
            $isRemote = false;
        }

        $this->viewBuilder()->setLayout('ajax');
        $datas = $this->getEbayInventory($isRemote);
        $json = json_encode($datas);
        $this->set([
            'inventory' => $json
        ]);
    }

    public function editMatch() {
        $this->viewBuilder()->setLayout('ajax');
        $result = array();
        try {
            $ebay_id = $this->request->data('Ebay_Id');
            $priceminister_id = $this->request->data('Priceminister_Annonce_Id');
            $cdiscount_id = $this->request->data('Cdiscount_Annonce_Id');
            $match_id = $this->request->data('Match_Id');
            $ebay_prix = $this->request->data('Ebay_Prix');
            $priceminister_prix = $this->request->data('Priceminister_Prix');
            $cdiscount_prix = $this->request->data('Cdiscount_Prix');
            $quantite = $this->request->data('Quantite');

            $db = $this->loadComponent("Database");

            $db->BulkUpdate(array(
                    [
                    'Inventory_Ebay' => $ebay_id,
                    'Inventory_Priceminister' => $priceminister_id,
                    'Inventory_Cdiscount' => $cdiscount_id
                ]
            ), "Id='$match_id'", 'inventory_match');


            $ebayComp = $this->loadComponent("Ebay");
            $ebayItem = $ebayComp->Get_LocalEbayInventory($ebay_id);
            
            if($ebayItem != null){
                if ($ebayItem['Prix'] != $ebay_prix || $quantite != $ebayItem['Quantite_Disponible']) {
                    
                    if(is_numeric($ebay_prix) && is_numeric($quantite)){
                        $ebay_prix = floatval($ebay_prix);
                        $quantite = intval($quantite);
                        $annonce_id = $ebayItem['Annonce_Id'];
                        
                        $result['revise_data'] = array(
                            'ebayPrix'=>$ebay_prix,
                            'quantite'=>$quantite,
                            'ebay_id'=>$ebay_id,
                            'annonce_id'=>$annonce_id
                        );
                        
                       //Mettre a jour la quantite/prix cote Ebay
                        $reviseResult = $ebayComp->Revise_FixedPriceItem($annonce_id, $ebay_prix, $quantite);

                        if($reviseResult == true){
                            //Mettre a jour la quantite/prix cote Bdd
                            $ebayComp->Revise_FixedPriceItem($annonce_id, $ebay_prix, $quantite, true);
                        }
                        else{
                            $result['Ebay_ReviseError'] = $reviseResult;
                            throw new \Exception('Revise Remote Ebay Item Error');
                        }
                    }else{
                        throw new \Exception('Ebay prix ou quantite is not numeric, ebay prix:'.$ebay_prix.' - quantite : '.$quantite);
                    }
                }
            }else{
                $result['Ebay_ReviseError'] = 'Cannot find local ebay item with id :'.$ebay_id;
                throw new \Exception('Revise Ebay Item Error');
            }

            $result['result'] = "success";
        } catch (\Exception $ext) {
            $result['result'] = 'failed';
            $result['errorMessage'] = $ext->getMessage();
        }

        $this->set([ 'result'=>$result]);
    }

    private function getEbayInventory($getRemote = true) {

        $inventory = $this->loadComponent("Inventory");
        $datas = array();
        if ($getRemote) {
            $items = $inventory->GetRemoteInventory();

            foreach ($items as $item) {
                $datas[] = array(
                    'Annonce_Id' => $item->ItemID,
                    'Image' => $item->PictureDetails->GalleryURL,
                    'Titre' => htmlspecialchars($item->Title, ENT_QUOTES),
                    'Quantite' => $item->Quantity,
                    'Quantite_Disponible' => $item->QuantityAvailable,
                    'Prix' => $item->SellingStatus->CurrentPrice->value
                );
            }
        } else {
            $datas = $inventory->GetLocalInventory();
        }
        return $datas;
    }

}
