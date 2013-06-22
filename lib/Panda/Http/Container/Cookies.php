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
 * Klasse Cookies
 *
 * @version ::VERSION::
 * @todo Dokumentieren
 */
class Cookies
{

    const EXCEPTION_CODE_COOKIE_NOT_FOUND = 1;
    const EXCEPTION_CODE_HEADERS_ALREADY_SENT = 2;

    protected $_cookies = array();

    public function __construct()
    {

    }

    public function dispatchCookie($name, $value = "", $expires = null, $path = null, $domain = null, $secure = null, $http_only = null)
    {
        return new Cookie($name, $value, $expires, $path, $domain, $secure, $http_only);
    }

    public function write($cookie)
    {
        $name = $cookie->getName();
        $value = $cookie->getValue();
        $expire = $cookie->getExpire();
        $expire = (false === $expire) ? 0 : $expire->getTimestamp();
        $path = $cookie->path();
        if (false === $path) {
            $path = "/";
        }

        $domain = $cookie->getDomain();
        if (false === $domain) {
            $domain = null;
        }
        $secure = $cookie->getSecure();
        $http_only = $cookie->getHttpOnly();

        if (headers_sent())
            throw new \LogicException("Can't set cookie {$name}. Headers already sent.", static::EXCEPTION_CODE_HEADERS_ALREADY_SENT);

        setcookie($name, $value, $expire, $path, $domain, $secure, $http_only);

        return $this;
    }

    /* ------------------------------------------------------- */
    /* ------------------ Getter Funktionen ------------------ */
    /* ------------------------------------------------------- */

    public function get($cookie = null)
    {
        return isset($cookie) ? $this->getSingle($cookie) : $this->getAll();
    }

    public function getAll()
    {
        return $this->_cookies;
    }

    public function getSingle($cookie)
    {
        return ($cookie instanceof Cookie) ? $this->getSingleCookie($cookie) : $this->getSingleName($cookie);
    }

    public function getSingleCookie(Cookie $cookie)
    {
        foreach ($this->_cookies as $c)
            if ($cookie->getEquals($c))
                return $c;

        throw new \OutOfBoundsException("Cookie not found.", static::EXCEPTION_CODE_COOKIE_NOT_FOUND);
    }

    public function getSingleName($name)
    {
        $name = (string) $name;

        foreach ($this->_cookies as $cookie)
            if ($cookie->getName() === $name)
                return $cookie;

        throw new \OutOfBoundsException("Cookie '{$name}' wasn't found.", static::EXCEPTION_CODE_COOKIE_NOT_FOUND);
    }

    /* ------------------------------------------------------- */
    /* ------------------ Setter Funktionen ------------------ */
    /* ------------------------------------------------------- */

    public function set(Cookie $cookie)
    {
        $this->_cookies[] = $cookie;

        return $this;
    }

    /* ------------------------------------------------------ */
    /* ------------------ Flush Funktionen ------------------ */
    /* ------------------------------------------------------ */

    public function flush($cookie = null)
    {
        return isset($cookie) ? $this->flushSingle($cookie) : $this->flushAll();
    }

    public function flushAll()
    {
        foreach ($this->_cookies as $cookie)
            $this->write($cookie);

        $this->_cookies = array();

        return $this;
    }

    public function flushSingle($cookie)
    {
        $cookie = $this->getSingle($cookie);
        $this->write($cookie);

        foreach ($this->_cookies as $i => $c)
            if ($c === $cookie)
                unset($this->_cookies[$i]);

        return $this;
    }

    /* ------------------------------------------------------- */
    /* ------------------ Delete Funktionen ------------------ */
    /* ------------------------------------------------------- */

    public function delete($cookie, $deleteWrittenCookie = true)
    {
        try {
            $c = $this->get($cookie);
            $this->deleteUnwrittenCookie($c);
        } catch (\OutOfBoundsException $e) {
            if ($deleteWrittenCookie) {
                $this->deleteWrittenCookie($cookie);
            }
        }

        return $this;
    }

    public function deleteUnwrittenCookie($cookie)
    {
        $cookie = $this->getCookie($cookie);

        foreach ($this->_cookies as $i => $c)
            if ($c === $cookie)
                unset($this->_cookies[$i]);

        return $this;
    }

    public function deleteWrittenCookie($cookie)
    {
        return ($cookie instanceof Cookie) ? $this->deleteWrittenCookieCookie($cookie) : $this->deleteWrittenCookieName($cookie);
    }

    public function deleteWrittenCookieCookie(Cookie $cookie)
    {
        $cookie->setExpires(1);
        $this->set($cookie);

        return $this;
    }

    public function deleteWrittenCookieName($name)
    {
        return $this->deleteWrittenCookieCookie($this->dispatchCookie($name));
    }

    public function clear()
    {
        $this->_cookies = array();

        return $this;
    }

}
