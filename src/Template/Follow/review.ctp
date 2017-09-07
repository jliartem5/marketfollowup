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

<div class="">
    <div class="col-xs-3">
        <form id="Filter_Form" method="get" >
            <input type="hidden" name="market" value="<?php echo $_GET['market']; ?>"
            <div class="row" id="Zone_Filter">
                
                <label for="Filter_Keyword">Keywords :</label>
                <div id="Filter_Keyword" style="margin-left: 20px; font-size: 13px;">
                    <div style="width: auto; height: 100px;" class="form-control" name="Keyword_Container" id="Keyword_Container">
                        <?php
                            if(is_null($filter) == false && array_key_exists('Keywords', $filter)){
                            echo '<span class="KW_SelectedItem label label-danger">'.implode('</span><span class="KW_SelectedItem label label-danger">', explode( ',', $filter['Keywords'] )).'</span>';
                            }
                        ?>
                    </div>
                    <input type="text"  class="typeahead form-control" placeholder="tape keyword here" id="Keyword_Search"/>
                </div>
                <label for="Zone_Order">Order by:</label>
                <?php
                    $selected_orderval = "0";
                    if(is_null($filter) == false && array_key_exists('orderBy', $filter)){
                        $selected_orderval = $filter['orderBy'];
                    }
                ?>
                <div id="Zone_Order"  style="margin-left: 20px;">
                    <label for="Order_SellCount_LastDays">Most popular for last... </label>
                    <select class="form-control" id="Order_SellCount_LastDays" name="Filter_orderBy">
                        <option value="0" <?php 
                            echo $selected_orderval == '0' ? 'selected' : '';
                        ?>>--</option>
                        <option value="LAST_2_Day" <?php 
                            echo $selected_orderval == 'LAST_2_Day' ? 'selected' : '';
                        ?>>48 hours</option>
                        <option value="LAST_7_Day" <?php 
                            echo $selected_orderval == 'LAST_7_Day' ? 'selected' : '';
                        ?>>7 jours</option>
                        <option value="LAST_14_Day" <?php 
                            echo $selected_orderval == 'LAST_14_Day' ? 'selected' : '';
                        ?>>14 jours</option>
                        <option value="LAST_30_Day" <?php 
                            echo $selected_orderval == 'LAST_30_Day' ? 'selected' : '';
                        ?>>30 jours</option>
                        <option value="LAST_60_Day" <?php 
                            echo $selected_orderval == 'LAST_60_Day' ? 'selected' : '';
                        ?>>60 jours</option>
                        <option value="LAST_180_Day" <?php 
                            echo $selected_orderval == 'LAST_180_Day' ? 'selected' : '';
                        ?>>180 jours</option>
                    </select>
                </div>

                <label for="Filter_Caractere">Caractere :</label>
                <div id="Filter_Caractere"  style="margin-left: 20px;">
                    <label for="Order_SellCount_LastDays">Select... </label>


                    <div id="Zone_Price"  style="margin-left: 20px;">
                        <label for="Filter_Price">Max Price </label>
                        <input type="text" class="form-control" placeholder="tape price here" 
                               value="<?php echo array_key_exists('maxPrice', $filter)?$filter['maxPrice']:'';?>"
                               id="Filter_maxPrice" name="Filter_maxPrice"/>
                    </div>
                </div>

                <input type="submit" title="Filter"/>
            </div>
        </form>
    </div>
    <div class="col-xs-9">
	<?php
		$filer_data = array();
		foreach($_GET as $key=>$val){
			if(strpos($key, 'Filter_') == 0){
				$filer_data[$key] = $val;
			}
		}
	?>
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <li class="page-item <?php echo $pageInfo->current == 0 ? 'disabled' : ''; ?>"><a class="page-link " href="<?php

                echo Router::url(array_merge($filer_data, [ 
                                                'controller' => 'Follow','action' => 'review',
                                                'page' => $pageInfo->current-1,
                                                'market'=>$market
                                                ])); 
              ?>">Previous</a></li>
            <?php for ($i=0; $i<$pageInfo->total; ++$i){ ?>
                <li class="page-item <?php echo $i == $pageInfo->current ? 'disabled':''; ?>"><a class="page-link " href="<?php 
                echo Router::url(array_merge($filer_data, [ 
                                                'controller' => 'Follow','action' => 'review',
                                                'page' => $i,
                                                'market'=>$market
                                                ])); ?>"><?= $i ?></a></li>
            <?php } ?>
                <li class="page-item <?php echo $pageInfo->current == $pageInfo->total-1 ? 'disabled':'' ?>"><a class="page-link " href="<?php 
                echo Router::url(array_merge($filer_data, [ 
                                                'controller' => 'Follow','action' => 'review',
                                                'page' => $pageInfo->current+1,
                                                'market'=>$market
                                                ]));  ?>">Next</a></li>
            </ul>
        </nav>

       <?php if (count($products) > 0): ?>

       <?php foreach ($products as $row) {?>
        <div  class="">
            <div class=".col-lg-9">
                <table class="table table-bordered" style="height: 150px; margin-bottom: 0px">
                    <td rowspan="5" style="width: 200px;">
                        <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                            <!-- Wrapper for slides -->
                            <div class="carousel-inner" role="listbox">
                                <div class="item active">
                                    <?php
                                    $json_img = json_decode(html_entity_decode($row["Images"]));
                                    if($json_img == null){
                                         echo $this->Html->image('No-image-found.jpg', array('width'=>'190px'));
                                    }else{
                                        $index=1;
                                        foreach($json_img as $url => $local) {
                                            if(strlen($local) > 0){
                                                 echo $this->Html->image(strtolower($_GET['market'])."/".$row["Id"].'/'.$index.'.jpg', array('width'=>'190px'));
                                            }
                                            else{
                                                echo "<img src='$url' width='190px'/>";
                                                
                                            }
                                        }
                                    }?>
                                </div>
                            </div>
                            <!-- Controls -->
                            <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
                                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
                                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    </td>
                    <tr style="height: 60px;">
                        <td colspan="5"  class="KW_Highlight_Target" style="font-size:16px; font-weight: bold;">

                            <a target="_blank" href="<?php echo $row['URL']; ?>">
                                <?php echo $row['Titre']; ?>
                            </a>
                        </td>
                    </tr>
                    <tr style="height:35px">
                        <td >
                            <span style="font-size: 15px;"><?php echo $row['Prix']; ?></span>
                            <br/>
                            <span style="font-size: 11px;">Livraison : <?php echo substr($row['Livraison'],0 ,20).(strlen($row['Livraison']) > 20?'...':''); ?></span>
                        </td>
                        <td style="font-size:12px;">
                                            <?php  
                                                echo array_key_exists('Progression_NbVendu', $row) ? '最新:'.$row['Progression_NbVendu']:'初始:'.$row['Nb_Vendu'];  
                                            ?>
                            <br/>
                                            <?php
                                                if(array_key_exists('Progession_Vendu', $row) ){
                                                    
                                                    echo '<b style="color:red;">+ '.$row['Progession_Vendu'].'</b> ('.$row["FirstFetchTime"].' - '.$row['LastFetchTime'].')';
                                                }
                                            ?>
                        </td>
                        <td style="font-size:12px;">
                                            <?php 
                                                echo array_key_exists('Progression_NbSuivi', $row) ? '最新:'.$row['Progression_NbSuivi']:'初始:'.$row['Nb_Suivi'];
                                            ?>
                            <br/>
                                            <?php
                                                if(array_key_exists('Progession_Suivi', $row)){
                                                    echo '<b style="color:red;">+ '.$row['Progession_Suivi'].'</b> ('.$row["FirstFetchTime"].' - '.$row['LastFetchTime'].')';
                                                }
                                            ?>

                        </td>
                        <td>

                            <a href="<?php echo $row['URL']; ?>">
                                <?php echo $this->Html->image('URL.png', array('width'=>'40px')) ?>
                            </a> 
                            <?php echo $this->Html->image('change.ico', array('width'=>'40px')) ?>

                            <span><?php echo $this->Html->image('detail.png', array('width'=>'40px', 'data-toggle'=>"tooltip", 'title'=>"-", 'data-placement'=>"left")) ?></span>
                        </td>
                        <td></td>
                    </tr>


                    <tr style="height:50px">
                        <td colspan="5" class="KW_Highlight_Source" style="font-size: 12px;">
                            <span class="KW_Item label label-primary"  > <?php echo implode('</span>  <span class="KW_Item label label-primary">  ' ,explode(',',$row['Keywords'])); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">Variables infos...</td>
                    </tr>
                </table>
            </div>
        </div>
       <?php } ?>
       <?php endif; ?>

    </div>

</div>

