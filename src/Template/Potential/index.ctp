<?php

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Router;

?>

<?= $this->Html->script('potential.js'); ?>

<ul>
    <li class="page-item">  当前页: <?=$pageInfo['current']?></li>
    <li class="page-item">  总商品: <?=$pageInfo['total']?></li>
    <li class="page-item">  当前页商品: <?=$pageInfo['count']?></li>
</ul>

<nav aria-label="Page navigation example">
    <ul class="pagination">
        <li class="page-item <?php echo $pageInfo['current'] == 0 ? 'disabled' : ''; ?>"><a class="page-link " href="<?php

                echo Router::url([  'controller' => 'Potential','action' => 'index',
                                                'page' => $pageInfo['current']-1
                                                ]); 
              ?>">Previous</a></li>


        <li class="page-item "><a class="page-link " href="<?php 
                echo Router::url([ 'controller' => 'Potential','action' => 'index',
                                                'page' => $pageInfo['current']+1
                                                ]);  ?>">Next</a></li>
    </ul>
</nav>


<?php
    foreach($potentials as $index => $potential){
        
            $first_elem = current(current($potential));
            $json_img = json_decode(html_entity_decode($first_elem["Images"]));
            $img_url = null;

            if($json_img != null){
                foreach($json_img as $url=>$val){
                    $img_url = $url;
                    break;
                }
            }else{
                $img_regex = '~(http.*\.)(jpe?g|png|[tg]iff?|svg)~i';
                $matches = array();
                preg_match($img_regex, $first_elem["Images"], $matches);
                $img_url = $matches[0];
            }
        ?>
<div class="row" id='potential_<?=$index?>'>
    <div class="col-md-11">
        <div class="thumbnail" style="border: solid; border-color: cadetblue;">
            <button style='float:right;' class='btn btn-primary product_delete' button-state='off' id='delete_<?=$index?>' data-product-id='<?=$index?>'>
				<span style="font-size: 12px;" class="glyphicon glyphicon-trash" ></span>
				 删除
			</button>
            <div class="row">
                <img src="<?=$img_url?>" width="220" class="col-md-2">
                <div class="col-md-10">
                    <h3><a target="_blank" href="<?=$first_elem['URL_Aliexpress']?>"><?=$first_elem['Titre']?></a></h3>
                    <div>
                        <h4>Aliexpress价格: <b><?=$first_elem['Prix_Aliexpress']?> <?=$first_elem['Monnaie_Aliexpress']?></b></h4>
                    </div>
                </div>

            </div>
            <div class="caption">
                <div class="panel-group" id="accordion_<?=$index?>" role="tablist" aria-multiselectable="true">
<?php
        foreach($potential as $search_combinason => $potential_array){
?> 

                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="heading_<?=$index?>">
                            <h4 class="panel-title"  role="button" data-toggle="collapse" data-parent="#accordion_<?=$index?>" href="#collapse_<?=$index?>" aria-expanded="true" aria-controls="collapse_<?=$index?>">
                                <a>
                                    <?=$search_combinason?>
                                </a>
                                <a href="<?php

                                    echo Router::url([
                                                'controller' => 'Potential','action' => 'searchword',
                                                'searchword' => $search_combinason
                                                ]); 
                                    ?>" class='btn btn-primary filter_by_searchword' data-searchword='<?=$search_combinason?>'>
                                    <span  class="glyphicon glyphicon-book" ></span>
                                     搜索
                                    </a>
                            </h4>
                        </div>    
                        <div id="collapse_<?=$index?>" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading_<?=$index?>">
                            <div class="panel-body">
<?php
            $rowCounter = 0;
            foreach($potential_array as $index=> $potential_data){
                if($rowCounter == 0){
?>
                                <div class="row"> 
<?php
                }
?>                    
                                    <div class="col-md-3"> 
                                        <div class="thumbnail"> 
                                            <img width="200" src="<?='http://'.$potential_data['Images_Ebay']?>"> 
                                            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                            <div class="caption"> 
                                                <h5><a target="_blank" href='http://<?=$potential_data['URL_Ebay']?>'><?=str_replace("Cliquez sur ce lien pour", '',str_replace('title=','', $potential_data['Titre_Ebay']))?></a></h5> 
                                                <div>
                                                    <div>Ebay价格:<b><?=$potential_data['Prix_Ebay']?>€ </b> (寄费:<?=$potential_data['Livraison_Ebay_F']?> €) (<b style='color:red;'><?=round((float)($potential_data['Prix_Ebay']+$potential_data['Livraison_Ebay_F'])/$first_elem['Prix_Aliexpress'] * 100 ) . '%'?></b>)</div>
                                                    <div>Ebay已卖出:<b style='color:red;'><?=  $potential_data['Nb_Vendu_Ebay']?></b> 件</div>
                                                    <?php
                                                        if(strlen($potential_data['Localisation_Ebay'])>0){
                                                    ?>
                                                        <div>Ebay来源:<b style=''><?=  $potential_data['Localisation_Ebay']?></b></div>
                                                    <?php
                                                        }
                                                    ?>
                                                    
                                                </div>
                                                <p>
                                                    <!--<a href="#" class="btn btn-primary" role="button">Button</a> <a href="#" class="btn btn-default" role="button">Button</a>-->
                                                </p> 
                                            </div> 
                                        </div> 
                                    </div> 
<?php

                $rowCounter++;
                if($rowCounter == 3 || $index+1 == count($potential_array)){
                    $rowCounter = 0;
?>
                                </div>
<?php
                }
            }
?>
                            </div>
                        </div> 
                    </div>
                </div> 
<hr />

<?php
                    
        }
?>
            </div> 
        </div>
    </div>
</div>
<?php
        
    }
?>