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
 * @version   1.0
 * @package   Panda.Interfaces
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Panda\Interfaces;

/**
 * Interface Clonable
 *
 * @version ::VERSION::
 * @package   Panda.Interfaces
 */
interface Clonable {

    /**
     * Erstellt eine Kopie der Klasseninstanz.
     */
    public function copy();

}
