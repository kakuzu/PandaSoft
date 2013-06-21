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
 * @package   Panda
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Panda;

require PANDA . 'shared.php';

if (!class_exists('Panda\Core\Config')) {
    require PANDA . 'Core' . DS . 'Autoloader.php';
    $loader = new \Panda\Core\Autoloader('Panda', CORE_PATH);
    $loader->register();
}