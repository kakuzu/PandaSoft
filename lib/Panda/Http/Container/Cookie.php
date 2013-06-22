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
 * Klasse Cookie
 *
 * @version ::VERSION::
 * @todo Dokumentieren
 */
class Cookie
{

    const EXCEPTION_CODE_INVALID_COOKIE_EXPIRES = 1;

    protected $_name;
    protected $_value;
    protected $_expires;
    protected $_path;
    protected $_domain;
    protected $_secure;
    protected $_httpOnly;

    public function __construct($name, $value = '', $expires = null, $path = null, $domain = null, $secure = null, $http_only = null)
    {
        $this
                ->setName($name)
                ->setValue($value)
                ->setExpires($expires)
                ->setPath($path)
                ->setDomain($domain)
                ->setSecure($secure)
                ->setHttpOnly($http_only);
    }

    public function equals(Cookie $cookie)
    {
        return (
                $cookie->getName() === $this->getName() &&
                $cookie->getValue() === $this->getValue() &&
                $cookie->getExpires() === $this->getExpires() &&
                $cookie->getPath() === $this->getPath() &&
                $cookie->getDomain() === $this->getDomain() &&
                $cookie->getSecure() === $this->getSecure() &&
                $cookie->getHttpOnly() === $this->getHttpOnly()
                );
    }

    /* ------------------------------------------------- */
    /* ------------------ Cookie Name ------------------ */
    /* ------------------------------------------------- */

    public function getNname()
    {
        return $this->_name;
    }

    public function setName($name)
    {
        $this->_name = (string) $name;

        return $this;
    }

    /* -------------------------------------------------- */
    /* ------------------ Cookie Value ------------------ */
    /* -------------------------------------------------- */

    public function getValue()
    {
        return $this->_value;
    }

    public function setValue($value)
    {
        $this->_value = (string) $value;

        return $this;
    }

    /* ---------------------------------------------------- */
    /* ------------------ Cookie Expires ------------------ */
    /* ---------------------------------------------------- */

    public function getExpires()
    {
        return isset($this->_expires) ? $this->_expires : false;
    }

    public function setExpires($expires)
    {
        if (isset($expires) && !$expires instanceof \DateTime) {
            try {
                if ($expires instanceof \DateInterval) {
                    $dateTime = new \DateTime('now');
                    $expires = $dateTime->add($expires);
                } elseif (is_int($expires))
                    $expires = \DateTime::createFromFormat('U', $expires);
                else
                    $expires = new \DateTime($expires);
            } catch (\Exception $e) {
                throw new \InvalidArgumumentException("Invalid expires parameter. DateTime object, DateInterval object, timestamp integer or valid date string are valid argument types.", static::EXCEPTION_CODE_INVALID_COOKIE_EXPIRES, $e);
            }
        }

        $this->_expires = $expires;

        return $this;
    }

    /* ------------------------------------------------- */
    /* ------------------ Cookie Path ------------------ */
    /* ------------------------------------------------- */

    public function getPath()
    {
        return isset($this->_path) ? $this->_path : false;
    }

    public function setPath($path)
    {
        $this->_path = $path;

        return $this;
    }

    /* --------------------------------------------------- */
    /* ------------------ Cookie Domain ------------------ */
    /* --------------------------------------------------- */

    public function getDomain()
    {
        return isset($this->_domain) ? $this->_domain : false;
    }

    public function setDomain($domain)
    {
        $this->_domain = $domain;

        return $this;
    }

    /* --------------------------------------------------- */
    /* ------------------ Cookie Secure ------------------ */
    /* --------------------------------------------------- */

    public function getSecure()
    {
        return $this->_secure;
    }

    public function setSecure($secure)
    {
        if (isset($secure))
            $secure = (bool) $secure;

        $this->_secure = $secure;

        return $this;
    }

    /* ------------------------------------------------------ */
    /* ------------------ Cookie Http Only ------------------ */
    /* ------------------------------------------------------ */

    public function getHttpOnly()
    {
        return $this->_httpOnly;
    }

    public function setHttpOnly($httpOnly)
    {
        if (isset($httpOnly))
            $httpOnly = (bool) $httpOnly;

        $this->_httpOnly = $httpOnly;

        return $this;
    }

}
