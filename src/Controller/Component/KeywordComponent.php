<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\ConnectionManager;

class KeywordComponent extends Component
{
    private  $connection = null;

    public function initialize(array $config)
   {
       parent::initialize($config);
      $this->connection = ConnectionManager::get('default');
   }
   


   public function getAll(){
        $kw = $this->connection->execute('call Keywords(@cmd:="GetAll", @kw=null)')->fetchAll('assoc');
        return $kw;
    }
}