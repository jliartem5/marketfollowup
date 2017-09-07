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

class FtpComponent extends Component {

    private $connection = null;

    public function __construct() {

        $ftp_parameters = Configure::read('Ftp.FileUpload');
        $this->connection = ftp_connect($ftp_parameters['host']);
        ftp_login($this->connection, $ftp_parameters['user'], $ftp_parameters['password']);
    }

    public function fileList($path) {
        return ftp_nlist($this->connection, $path);
    }


    public function putFile($localFile, $remoteFile) {
        return ftp_put($this->connection, $remoteFile, $localFile, FTP_ASCII);
    }
    
    public function close(){
        ftp_close($this->connection);
    }
}
