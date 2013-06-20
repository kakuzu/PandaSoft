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
 * @version   0.1-dev
 * @package   Panda
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Panda\Core;

use Panda\Interfaces;

/**
 * Klasse Object
 *
 * @version ::VERSION::
 */
class Object implements \Serializable, Interfaces\Clonable, Interfaces\Equatable, Interfaces\Formattable {

    public function serialize()
    {
        return serialize($this);
    }

    public function unserialize($serialized)
    {
        return unserialize($serialized);
    }

    public function copy()
    {
        return clone($this);
    }

    public function equals($obj)
    {
        return ($this === $obj);
    }

    public function toString()
    {
        return get_class($this);
    }

    /**
     * Stopt die ausführung des Scripts. Wrapper Funktion für exit();
     *
     * @param integer|string $status siehe http://php.net/exit für verfügbare Codes.
     * @return void
     */
    protected function quit($status = 0)
    {
        exit($status);
    }

}
