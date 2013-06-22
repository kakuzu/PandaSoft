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
 * @package   Panda.Interfaces
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Panda\Interfaces;

/**
 * Interface Session
 *
 * @version ::VERSION::
 */
interface Session {

    public function close();

    public function destroy(string $session_id);

    public function gc(string $maxlifetime);

    public function open(string $save_path, string $name);

    public function read(string $session_id);

    public function write(string $session_id, string $session_data);

}
