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

namespace Panda\Interfaces;

/**
 * Interface Cookie
 *
 * @version ::VERSION::
 */
interface Cookie {

    public function equals(Cookie $cookie);

    public function name();

    public function set_name($name);

    public function value();

    public function set_value($value);

    public function expires();

    public function set_expires($expires);

    public function path();

    public function set_path($path);

    public function domain();

    public function set_domain($domain);

    public function secure();

    public function set_secure($secure);

    public function http_only();

    public function set_http_only($http_only);

}
