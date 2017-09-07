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
<div>
    <div class="Zone_Current_Files">

    </div>
    
    <form enctype="multipart/form-data"  class="Zone_Current_FileState form" method="post" action="<?php echo Router::url([ 
                                                'controller' => 'Loadfile','action' => 'upload']); ?>">
        <label class="control-label">Select File</label>
        <div >
            <input style="display: inline-block;" id="file" name="file" type="file" class="file" data-show-preview="false">
            <input style="display: inline-block;" type="submit" />
        </div>
    </form>
    <?php
        if(isset($result)){
            echo "<span class='label label-primary'>$result</span>";
        }
    ?>
    <div class="Zone_LoadFile">
        
    </div>
    
</div>
