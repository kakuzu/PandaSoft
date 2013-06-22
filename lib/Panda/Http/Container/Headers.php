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
 * @package   Panda.Http.Container
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Panda\Http\Container;

/**
 * Klasse Headers
 *
 * @version ::VERSION::
 * @todo Dokumentieren
 */
class Headers
{

    const EXCEPTION_CODE_HEADER_NOT_FOUND = 1;
    const EXCEPTION_CODE_HEADERS_ALREADY_SENT = 2;

    protected $_headers;
    protected $_headers_set;

    public function __construct()
    {
        $this->_headers = array();
        $this->_headers_set = array();
    }

    public function write($header, $value)
    {
        if ($this->isSent())
            throw new LogicException("Can't write header '{$header}'. Headers already sent.", static::EXCEPTION_CODE_HEADERS_ALREADY_SENT);

        header("{$header}: {$value}");

        return $this;
    }

    /* ------------------------------------------------------- */
    /* ------------------ Getter Funktionen ------------------ */
    /* ------------------------------------------------------- */

    public function get($header = null)
    {
        return isset($header) ? $this->getSingle($header) : $this->getAll();
    }

    public function getSingle($header)
    {

        if (!isset($this->_headers[$header]))
            throw new OutOfBoundsException("Header '{$header}' wasn't found.", static::EXCEPTION_CODE_HEADER_NOT_FOUND);

        return $this->_headers[$header];
    }

    public function getAll()
    {
        return $this->_headers;
    }

    /* ------------------------------------------------------- */
    /* ------------------ Setter Funktionen ------------------ */
    /* ------------------------------------------------------- */

    public function set($header, $value = null)
    {
        return isset($value) ? $this->setSingle($header, $value) : $this->setAll($header);
    }

    public function setSingle($header, $value)
    {
        $this->_headers[$header] = (string) $value;
        $this->_headers_set[] = $header;

        return $this;
    }

    public function setAll(array $headers)
    {
        foreach ($headers as $header => $value)
            $this->setSingle($header, $value);

        return $this;
    }

    /* ------------------------------------------------------ */
    /* ------------------ Flush Funktionen ------------------ */
    /* ------------------------------------------------------ */

    public function flush($header = null)
    {
        return isset($header) ? $this->flushSingle($header) : $this->flushAll();
    }

    public function flushSingle($header)
    {
        $this->write($header, $this->getSingle($header))->delete($header);

        return $this;
    }

    public function flushAll()
    {
        foreach ($this->_headers as $header => $value)
            $this->flushSingle($header);

        return $this;
    }

    /* ------------------------------------------------------- */
    /* ------------------ Delete Funktionen ------------------ */
    /* ------------------------------------------------------- */

    public function delete($header)
    {
        unset($this->_headers[$header]);

        return $this;
    }

    public function clear()
    {
        $this->_headers = array();

        return $this;
    }

    /* --------------------------------------------------------- */
    /* ------------------ Controll Funktionen ------------------ */
    /* --------------------------------------------------------- */

    public function isSent()
    {
        return headers_sent();
    }

    public function has($header)
    {
        return in_array($header, $this->_headers_set);
    }

}
