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

require __DIR__ . DIRECTORY_SEPARATOR . 'paths.php';

require PANDA . 'bootstrap.php';

use Panda\Http\Request;

$request = Request::createInstance();
echo '<pre>' . print_r($request, 1) . '</pre>';