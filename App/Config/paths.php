<?php

/**
 * PandaSoft PHP Framework
 *
 * Copyright (C) 2013 FoxyNet
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must contain the LICENCE.txt.
 *
 * @copyright (c) 2013, FoxyNet
 * @link      http://board.foxynet.de PandaSoft Support Forum
 * @version   ::VERSION::
 * @package   App.config
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Config;

/**
 * Shoutcode für DIRECTORY_SEPARATOR.
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * Haupt Arbeitsverzeichnis.
 */
define('ROOT', dirname(dirname(__DIR__)));

/**
 * Verzeichnnisname des Application Ordners.
 */
define('APP_DIRNAME', basename(dirname(__DIR__)));

/**
 * Verzeichnisname des Ordners für Öffentliche Dateien.
 * (index.php | css,js,img Ordner usw)
 */
define('WWW_DIRNAME', 'www');

/**
 * Pfad, Application Ordner.
 */
define('APP', ROOT . DS . APP_DIRNAME . DS);

/**
 * Pfad, WWW Ordner.
 */
define('WWW_ROOT', APP . WWW_DIRNAME . DS);

/**
 * Pfad, Öffentliche StyleSheets.
 */
define('CSS', WWW_ROOT . 'css' . DS);

/**
 * Pfad, Öffentliche JavaScripts.
 */
define('JS', WWW_ROOT . 'js' . DS);

/**
 * Pfad, Öffentliche Bilder.
 */
define('IMAGES', WWW_ROOT . 'img' . DS);

/**
 * Pfad, Temporäre Dateien.
 */
define('TMP', APP . 'tmp' . DS);

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    define('PANDA_CORE_INCLUDE_PATH', dirname(__DIR__) . '/vendor/pandasoft');
} else {
    define('PANDA_CORE_INCLUDE_PATH', ROOT . DS . 'lib');
}

/**
 * Pfad, Panda Framework.
 */
define('CORE_PATH', PANDA_CORE_INCLUDE_PATH . DS);
define('PANDA', CORE_PATH . 'Panda' . DS);