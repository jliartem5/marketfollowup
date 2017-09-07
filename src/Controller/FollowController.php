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

use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class FollowController extends AppController
{
	public function index(){
			
		
	}
    /**
     * Displays a view
     *
     * @return void|\Cake\Network\Response
     * @throws \Cake\Network\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\Network\Exception\NotFoundException When the view file could not
     *   be found or \Cake\View\Exception\MissingTemplateException in debug mode.
     */
    public function review(){
           $market = $this->request->getQuery("market");
           $page = $this->request->getQuery("page");
           if($page == null){
               $page = 0;
           }
           
           $pageInfo =  new \stdClass();
           $pageInfo->current = intval($page);
           
           $filterData = array();
           foreach($_GET as $key=>$value){
               if(strpos($key, 'Filter_') === 0){
                   $new_key = str_replace ('Filter_', '', $key);
                   $filterData[$new_key] = $value;
               }
           }
           $products = null;
           if(count($filterData) == 0){
               $products = $this->loadComponent($market)->get(100, $page);
               $pageInfo->total = $this->loadComponent($market)->getPageCount();
           }else{
               $totalrow = '';
               $products = $this->loadComponent($market)->getByFilter($filterData, 100, $page, $totalrow);
               $pageInfo->total = $totalrow[0]['TotalRow']/100;
           }
            
            // Pass variables into the view template context.
           $this->set(
               ['market'=>$market,
                'products'=>$products,
                'pageInfo'=>$pageInfo,
                'filter'=>$filterData
               ]
            );
    }
}
