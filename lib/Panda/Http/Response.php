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
 * @package   Panda.Http
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Panda\Http;

use Panda\Http\Container;

/**
 * Klasse Response
 *
 * @version ::VERSION::
 * @todo Dokumentieren
 */
class Response
{

    protected $_headers;
    protected $_cookies;
    protected $_session;
    protected $_body;

    public function __construct($headers = null, $cookies = null, $session = null, $body = null)
    {
        $this
                ->setHeaders($headers)
                ->setCookies($cookies)
                ->setSession($session)
                ->setBody($body);
    }

    public function flush()
    {
        $this->getHeaders()->flush();
        $this->getSession()->flush();
        $this->getCookies()->flush();
        $this->getBody()->flush();

        return $this;
    }

    /* ----------------------------------------------------- */
    /* ------------------ Response Header ------------------ */
    /* ----------------------------------------------------- */

    public function getHeaders()
    {
        if (!isset($this->_headers))
            $this->_initializeHeaders();

        return $this->_headers;
    }

    public function setHeaders($headers = null)
    {
        $this->_headers = $headers;

        return $this;
    }

    /* ----------------------------------------------------- */
    /* ------------------ Response Cookie ------------------ */
    /* ----------------------------------------------------- */

    public function getCookies()
    {
        if (!isset($this->_cookies))
            $this->_initializeCookies();

        return $this->_cookies;
    }

    public function setCookies($cookies = null)
    {
        $this->_cookies = $cookies;

        return $this;
    }

    /* ------------------------------------------------------ */
    /* ------------------ Response Session ------------------ */
    /* ------------------------------------------------------ */

    public function getSession()
    {
        if (!isset($this->_session))
            $this->_initialize_Session();

        return $this->_session;
    }

    public function setSession($session = null)
    {
        $this->_session = $session;

        return $this;
    }

    /* --------------------------------------------------- */
    /* ------------------ Response Body ------------------ */
    /* --------------------------------------------------- */

    public function getBody()
    {
        if (!isset($this->_body))
            $this->_initializeBody();

        return $this->_body;
    }

    public function setBody($body = null)
    {
        $this->_body = $body;

        return $this;
    }

    /* --------------------------------------------------------- */
    /* ------------------ Protected Functions ------------------ */
    /* --------------------------------------------------------- */

    protected function _initializeHeaders()
    {
        return $this->setHeaders(new Container\Headers);
    }

    protected function _initializeCookies()
    {
        return $this->setCookies(new Container\Cookies);
    }

    protected function _initializeSession()
    {
        return $this->setSession(new Container\Session);
    }

    protected function _initializeBody()
    {
        return $this->setBody(new Container\Body);
    }

}
