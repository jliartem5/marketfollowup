<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use InvalidArgumentException;
use Priceminister\PriceministerClient;
use Priceminister\ProductListing;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PriceministerController extends AppController {
    public function testClientWithEmptyCredentials() {
        $this->expectException(InvalidArgumentException::class);
        new PriceministerClient();
    }

    public function index() {
        
        $client = new PriceministerClient('minielectro', getenv('Priceminister_Token'));
        $productListing = new ProductListing($client);
        $productListing->setParameter('kw', 'USB');
        $result = $productListing->request();
        debug(($result->getId()));
        debug(($result->getProducts()));
    }
    
    public function inventaire(){
        
        $client = new PriceministerClient('minielectro', getenv('Priceminister_Token'));
        $productListing = new \Priceminister\Inventaire($client);
        $result = $productListing->request();
    }

}
