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
class LoadfileController extends AppController {

    public function index() {


        $view_data = array();
        if ($this->request->session()->check('uResult')) {
            $view_data['result'] = $this->request->session()->read('uResult');
            $this->request->session()->delete('uResult');
        }

        $this->set($view_data);
    }

    /**
     * Displays a view
     *
     * @return void|\Cake\Network\Response
     * @throws \Cake\Network\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\Network\Exception\NotFoundException When the view file could not
     *   be found or \Cake\View\Exception\MissingTemplateException in debug mode.
     */
    public function upload() {
        $uploadDir = './file/' . basename($_FILES['file']['name']);
        $resultMsg = '';
        if (pathinfo($uploadDir, PATHINFO_EXTENSION) == 'xlsx') {
            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir)) {

                $ftp = $this->loadComponent('Ftp');
                if ($ftp->putFile($uploadDir, Configure::read('Ftp.UploadPath') . $_FILES['file']['name'])) {
                    $resultMsg = 'Success';
                } else {
                    $resultMsg = 'Failed';
                }
                unlink($uploadDir);
            } else {
                $resultMsg = 'Failed';
            }
        } else {
            $resultMsg = 'Format incorrect';
        }

        $this->request->session()->write('uResult', $resultMsg);
        $this->redirect(array(
            'controller' => 'loadfile', 'action' => 'index'
        ));
    }

}
