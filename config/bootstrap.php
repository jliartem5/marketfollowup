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
 * @since         0.10.8
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

// You can remove this if you are confident that your PHP version is sufficient.
if (version_compare(PHP_VERSION, '5.6.0') < 0) {
    trigger_error('Your PHP version must be equal or higher than 5.6.0 to use CakePHP.', E_USER_ERROR);
}

/*
 *  You can remove this if you are confident you have intl installed.
 */
if (!extension_loaded('intl')) {
    trigger_error('You must enable the intl extension to use CakePHP.', E_USER_ERROR);
}

/*
 * You can remove this if you are confident you have mbstring installed.
 */
if (!extension_loaded('mbstring')) {
    trigger_error('You must enable the mbstring extension to use CakePHP.', E_USER_ERROR);
}

/*
 * Configure paths required to find CakePHP + general filepath
 * constants
 */
require __DIR__ . '/paths.php';

/*
 * Bootstrap CakePHP.
 *
 * Does the various bits of setup that CakePHP needs to do.
 * This includes:
 *
 * - Registering the CakePHP autoloader.
 * - Setting the default application paths.
 */
require CORE_PATH . 'config' . DS . 'bootstrap.php';

use Cake\Cache\Cache;
use Cake\Console\ConsoleErrorHandler;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Core\Plugin;
use Cake\Database\Type;
use Cake\Datasource\ConnectionManager;
use Cake\Error\ErrorHandler;
use Cake\Log\Log;
use Cake\Mailer\Email;
use Cake\Network\Request;
use Cake\Utility\Inflector;
use Cake\Utility\Security;

/*
 * Read configuration file and inject configuration into various
 * CakePHP classes.
 *
 * By default there is only one configuration file. It is often a good
 * idea to create multiple configuration files, and separate the configuration
 * that changes from configuration that does not. This makes deployment simpler.
 */
try {
    Configure::config('default', new PhpConfig());
    Configure::load('app', 'default', false);
} catch (\Exception $e) {
    exit($e->getMessage() . "\n");
}

/*
 * Load an environment local configuration file.
 * You can use a file like app_local.php to provide local overrides to your
 * shared configuration.
 */
//Configure::load('app_local', 'default');

/*
 * When debug = true the metadata cache should only last
 * for a short time.
 */
if (Configure::read('debug')) {
    Configure::write('Cache._cake_model_.duration', '+2 minutes');
    Configure::write('Cache._cake_core_.duration', '+2 minutes');
}

/*
 * Set server timezone to UTC. You can change it to another timezone of your
 * choice but using UTC makes time calculations / conversions easier.
 */
date_default_timezone_set('UTC');

/*
 * Configure the mbstring extension to use the correct encoding.
 */
mb_internal_encoding(Configure::read('App.encoding'));

/*
 * Set the default locale. This controls how dates, number and currency is
 * formatted and sets the default language to use for translations.
 */
ini_set('intl.default_locale', Configure::read('App.defaultLocale'));

/*
 * Register application error and exception handlers.
 */
$isCli = PHP_SAPI === 'cli';
if ($isCli) {
    (new ConsoleErrorHandler(Configure::read('Error')))->register();
} else {
    (new ErrorHandler(Configure::read('Error')))->register();
}

/*
 * Include the CLI bootstrap overrides.
 */
if ($isCli) {
    require __DIR__ . '/bootstrap_cli.php';
}

/*
 * Set the full base URL.
 * This URL is used as the base of all absolute links.
 *
 * If you define fullBaseUrl in your config file you can remove this.
 */
if (!Configure::read('App.fullBaseUrl')) {
    $s = null;
    if (env('HTTPS')) {
        $s = 's';
    }

    $httpHost = env('HTTP_HOST');
    if (isset($httpHost)) {
        Configure::write('App.fullBaseUrl', 'http' . $s . '://' . $httpHost);
    }
    unset($httpHost, $s);
}

Cache::setConfig(Configure::consume('Cache'));
ConnectionManager::setConfig(Configure::consume('Datasources'));
Email::setConfigTransport(Configure::consume('EmailTransport'));
Email::setConfig(Configure::consume('Email'));
Log::setConfig(Configure::consume('Log'));
Security::salt(Configure::consume('Security.salt'));

/*
 * The default crypto extension in 3.0 is OpenSSL.
 * If you are migrating from 2.x uncomment this code to
 * use a more compatible Mcrypt based implementation
 */
//Security::engine(new \Cake\Utility\Crypto\Mcrypt());

/*
 * Setup detectors for mobile and tablet.
 */
Request::addDetector('mobile', function ($request) {
    $detector = new \Detection\MobileDetect();

    return $detector->isMobile();
});
Request::addDetector('tablet', function ($request) {
    $detector = new \Detection\MobileDetect();

    return $detector->isTablet();
});

/*
 * Enable immutable time objects in the ORM.
 *
 * You can enable default locale format parsing by adding calls
 * to `useLocaleParser()`. This enables the automatic conversion of
 * locale specific date formats. For details see
 * @link http://book.cakephp.org/3.0/en/core-libraries/internationalization-and-localization.html#parsing-localized-datetime-data
 */
Type::build('time')
    ->useImmutable();
Type::build('date')
    ->useImmutable();
Type::build('datetime')
    ->useImmutable();
Type::build('timestamp')
    ->useImmutable();

/*
 * Custom Inflector rules, can be set to correctly pluralize or singularize
 * table, model, controller names or whatever other string is passed to the
 * inflection functions.
 */
//Inflector::rules('plural', ['/^(inflect)or$/i' => '\1ables']);
//Inflector::rules('irregular', ['red' => 'redlings']);
//Inflector::rules('uninflected', ['dontinflectme']);
//Inflector::rules('transliteration', ['/å/' => 'aa']);

/*
 * Plugins need to be loaded manually, you can either load them one by one or all of them in a single call
 * Uncomment one of the lines below, as you need. make sure you read the documentation on Plugin to use more
 * advanced ways of loading plugins
 *
 * Plugin::loadAll(); // Loads all plugins at once
 * Plugin::load('Migrations'); //Loads a single plugin named Migrations
 *
 */

/*
 * Only try to load DebugKit in development mode
 * Debug Kit should not be installed on a production system
 */
if (Configure::read('debug')) {
    Plugin::load('DebugKit', ['bootstrap' => true]);
}

/*
*	Jian : Credentials for ebay seller
*
*/ 
putenv("Ebay_Debug=0");
putenv("EBAY_SDK_APP_ID=JianLI-MiniElec-PRD-88df3d054-0f1c4408");
putenv("EBAY_SDK_CERT_ID=PRD-8df3d054b584-88b9-4382-8807-5899");
putenv("EBAY_SDK_DEV_ID=63d91c83-63c5-48c8-b172-b55635290911"); 
putenv('EBAY_AuthToken=AgAAAA**AQAAAA**aAAAAA**oGmUWQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AFmYGmCpOHpAudj6x9nY+seQ**cdIDAA**AAMAAA**AbvlX2uIe+YEdRL18or0X2dL422VTd3GJNISnyOpgviKtNEmiAVAieploosFyKy9AFanUf7O90p6YrP7pAYOUCOVgGJfE9msKBFfGckXQA0yh6ADR2WvFPjHT/+zIDtUKXUhT/fMCj6RmBvKlRIQ3qZpjOkKlGY4sHJ0mb0++9K9GtXAtBGPeUA0KQVVs2wO916gKCBD2pSFIZtJbhd80Jl1nCYedA3tLUbq58QLmUoEzIrQ19TWZ3N0Q5USvDUS0oD28QKe42dc+4noY78OBTNgBK5CfX2EE333AqfNN5KR+cQaY7fh6OyiI96lxBjPiGofQjWKFQQiBJ+wELPOe/BTMSsf48I6isVyJnzIXh2zbcJnSaVVlzlUOWDwjx99vhgevCauPRzYRq0QYC9HUHLi03O/AMIL/r/G7nER+4zygZVnQZrQC5RRPh3XeRmBAviIJW0+ZAV8+AJOdP4Yly1VXvTP3NtG1A4EClzHh3TQgIzpy3uWd4Cp945YQbPETZuQrXMJ+g6AoXP93LkVns3vLDa33UYs2yytQmXAnyB3dvIQu/eCHaxmloldc3kWNwhrvnBJY/PcLFI6wdJkQeLq7E4+b88vN5W6E0ob0N3RAyE0ANA2BDA5NRvWLITMg+i0T+v2jyKbunDFZFdoKr2iGkkCPvBGm/FXO7A2VhijMNLb0lbaht6Cldx8Xsru2oCIM03yb57JciuZR0yNKYvv2YI1rJGLk1SesYow2gjLcToNGXLgEqcmfD7BKUL2');
putenv('EBAY_Sandbox_AuthToken=AgAAAA**AQAAAA**aAAAAA**FEucWQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wFk4GkAJOAoAmdj6x9nY+seQ**mE0EAA**AAMAAA**NWEcWJKTA9wt7Ikf8AmyCwqnRJolUe/1+gkBpbQ8KhNhndmQQsw3ctMAncoT1Abi34eA/soJa5jQVqI68QITwbPJZ/yyW+5bB4VVNUsGlOFHFXywtYQbbLvzE+JJ27fiCyo9qli8n5UPupA0vjO/IhKpyb3NFuJMUTGjsRRLhCcs7lGrpiTLP2WJDvNdDiO/kYhPuMqQD+g+TCXo6ltkTDvVSlAHJSUmgObxZdU5Gm4EfaPnfonfzwa6XENX6G7BCxVZPZnpWNBz4PWtU8OZi02GFVxo6N/roFN8ZjXEU/x9gaPynxgHFVROeGN6ZQSWERImGHUMmybcMAHOMZVX/XnjBx7gjxmw2Ga4IslwM0hzukBgRlrDSAAcU7EPMGRZGzmKMbzvBGDhAiCYFZQhjz820UREHkbJEBNpZ4KGkEu2xeAHWAGUh2RxwssTzDUff/VWh2SB+CYjewZP3ryPrTu24hGWcDh4LfYMI7riN+wGxkO2ZzqGTur4yry9behH0/Txtf46+ljefjMi5JKiPz31vaSEZg7v10pAUKmdVnpjWfVQ3zAef6epwdZMZzGp6t2s3WW3rlRbgaSCtrp9CkDXOBPov244j2Af8lCujEJDFvna6zJyTBg0vuwIk4FBOgHLV6d/iuYgsgXKjvYzSO9VUus14xcDmuxaj7EUqBVVIi7RtMVrpNgLT16YicmGSytLlQdkg1rbZkbxs5w+OMlbP4hELymw8LzuXC1dkAaP9i/eRFSrkZxSex3ndGUw');
putenv('Priceminister_Token=34193172c6fb4add899b073a3baf5953');