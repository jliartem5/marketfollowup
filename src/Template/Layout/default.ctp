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

$cakeDescription = 'CakePHP: the rapid development php framework';
?>
<!DOCTYPE html>
<html>
    <head>
    <?= $this->Html->charset() ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
        </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css('bootstrap.css') ?>
    <?= $this->Html->css('marketfollow.css') ?>
    <?= $this->Html->css('jquery-ui.css') ?>
    <?= $this->Html->css('plugin/notyf.min.css') ?>
    <?= $this->Html->script('jquery-3.2.1.min.js') ?>
    <?= $this->Html->script('jquery.cookie.js') ?>
    <?= $this->Html->script('jquery-ui.min.js') ?>
    <?= $this->Html->script('bootstrap.js') ?>
    <?= $this->Html->script('plugin/typeahead.js') ?>
    <?= $this->Html->script('plugin/notyf.min.js') ?>
    <?= $this->Html->script('keyword.js') ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
    </head>
    <body>
        <!--
        <nav class="top-bar expanded" data-topbar role="navigation">
            <ul class="title-area large-3 medium-4 columns">
                <li class="name">
                    <h1><a href=""><?= $this->fetch('title') ?></a></h1>
                </li>
            </ul>
            <div class="top-bar-section">
                <ul class="right">
                    <li><a target="_blank" href="http://book.cakephp.org/3.0/">Documentation</a></li>
                    <li><a target="_blank" href="http://api.cakephp.org/3.0/">API</a></li>
                </ul>
            </div>
        </nav>
        -->
    <nav class="navbar navbar-default">
      <div class="container-fluid">
        <div class="navbar-header">
          <a class="navbar-brand" href="#">Market Followup</a>
        </div>
        <ul class="nav navbar-nav">
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">Market
            <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="/follow/review?market=Ebay">Ebay</a></li>
            </ul>
          </li>
          <li><a href="/potential">Potential</a></li>
          <li><a href="/store/inventory">Invetory</a></li>
        </ul>
      </div>
    </nav>
    <?= $this->Flash->render() ?>
        <div class="container-fluid clearfix">
        <?= $this->fetch('content') ?>
        </div>
        <footer>
        </footer>
    </body>
</html>
