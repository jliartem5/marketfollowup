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
<?= $this->Html->script('jsgrid.js') ?>
<?= $this->Html->css('jsgrid.min.css') ?>
<?= $this->Html->css('jsgrid-theme.css') ?>
<?= $this->Html->script('inventory.js?v='.rand(10,1000)) ?>
<?= $this->Html->css('inventory.css') ?>
<?= $this->Html->script('plugin/spin.min.js') ?>

<div id="Modal_AddMatch" class="modal fade" tabindex="-1" role="dialog">
    <div class=" vertical-alignment-helper">
        <div class="modal-dialog" role="document" class="vertical-align-center" style="width: 850px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="#Modal_AddMatch" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">添加追踪</h4>
                </div>

                <div class="modal-body" id="AddMatch_ElementsBody">
                    <div class="row">
                        <div class="col-md-2">
                            <img src="/img/No-image-found.jpg" id="Ebay_Annonce_Image" width="90"/>
                        </div>
                        <div class="col-md-8">
                            <table style="    margin-bottom: 20px;">
                                <thead>
                                <td style="width: 150px;">Ebay价格</td>
                                <td style="width: 150px;">Priceminister价格</td>
                                <td style="width: 150px;">Cdiscount价格</td>
                                <td style="width: 150px;">Rue du commerce价格</td>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="number" id="AddMatch_Ebay_Price"  style="width: 100px;"/></td>
                                        <td><input type="number" id="AddMatch_Priceminister_Price"  style="width: 100px;"/></td>
                                        <td><input type="number" id="AddMatch_Cdiscount_Price"  style="width: 100px;"/></td>
                                        <td><input type="number" id="AddMatch_RueDuCommerce_Price"  style="width: 100px;"/></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div>
                                <table>
                                    <tbody>
                                        <tr>
                                            <td><label for="AddMatch_All_Stock">存货数量:</label></td>
                                            <td><input type="number" id="AddMatch_All_Stock"/></td>
                                        </tr>
                                        <tr>
                                            <td><label for="AddMatch_LastCheckDate">最后一次更新时间:</label></td>
                                            <td><span id="AddMatch_LastCheckDate"></span></td>
                                        </tr>
                                    </tbody>
                                </table>


                                <br/>

                            </div>
                        </div>
                    </div>
                    <hr/>
                    <div class="row">
                        <div class="col-md-12">
                            <div id="Ebay_AddMatch_Zone">
                                Ebay : <span id="Ebay_Id_Show">(None)</span>
                                <input type="text" id="AddMatch_Ebay_Inventory" style="width: 800px;" readonly="readonly"/>
                                <input type="hidden" id="Ebay_Id"/>
                            </div><br/>
                            <div id="Priceminister_AddMatch_Zone">
                                Priceminister :
                                <input type="text" id="AddMatch_Priceminister_Inventory"  style="width: 800px;"/>
                                <input type="hidden" id="Priceminister_Annonce_Id"/>
                            </div><br/>
                            <div id="Cdiscount_AddMatch_Zone">
                                Cdiscount :
                                <input type="text" id="AddMatch_Cdiscount_Inventory" style="width: 800px;" />
                                <input type="hidden" id="Cdiscount_Annonce_Id"/>
                            </div>
                            <div id="RueDuCommerce_AddMatch_Zone">
                                Cdiscount :
                                <input type="text" id="AddMatch_RueDuCommerce_Inventory" style="width: 800px;" />
                                <input type="hidden" id="RueDuCommerce_Annonce_Id"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-style="contract-overlay" id="AddMatch_Submit" ><span class="ladda-label">提交</span>></button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
</div><!-- /.modal -->
<div id="test_result"> </div>

<div>
    <div class="row">
        <div id="Match_Result">

        </div>

    </div>
</div>