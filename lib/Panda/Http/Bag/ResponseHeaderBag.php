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

namespace Panda\Http\Bag;

use Panda\Datastore\Cookie;

/**
 * Klasse ResponseHeaderBag
 *
 * @version ::VERSION::
 */
class ResponseHeaderBag extends HeaderBag {

    const COOKIES_FLAT = 'flat';
    const COOKIES_ARRAY = 'array';
    const DISPOSITION_ATTACHMENT = 'attachment';
    const DISPOSITION_INLINE = 'inline';

    /**
     * Array mit Cache-Control Informationen.
     *
     * @var array
     */
    protected $computedCacheControl = array();

    /**
     * Array mit Client Cookie Daten.
     *
     * @var array
     */
    protected $cookies = array();

    /**
     * Constructor.
     *
     * @param array $headers Array mit Headern
     */
    public function __construct(array $headers = array())
    {
        parent::__construct($headers);
        if (!isset($this->headers['cache-control'])) {
            $this->set('cache-control', '');
        }
    }

    /**
     * Ersetzt alle vorhandenen Header mit $headers.
     *
     * @param array $headers Array mit HTTP Headern
     */
    public function replace(array $headers = array())
    {
        parent::replace($headers);
        if (!isset($this->headers['cache-control'])) {
            $this->set('cache-control', '');
        }
    }

    /**
     * Setzen eines Headers.
     *
     * @param string       $key     Header Key
     * @param string|array $values  Header Value oder Array mit Values
     * @param Boolean      $replace Vorhandenen gleichen Header überschreiben?
     */
    public function set($key, $values, $replace = true)
    {
        parent::set($key, $values, $replace);
        if (in_array(strtr(strtolower($key), '_', '-'), array('cache-control', 'etag', 'last-modified', 'expires'))) {
            $computed = $this->computeCacheControlValue();
            $this->headers['cache-control'] = array($computed);
            $this->computedCacheControl = $this->parseCacheControl($computed);
        }
    }

    /**
     * Entfernt einen Header
     *
     * @param string $key HTTP Header Name
     */
    public function remove($key)
    {
        parent::remove($key);
        if ('cache-control' === strtr(strtolower($key), '_', '-')) {
            $this->computedCacheControl = array();
        }
    }

    /**
     * Überprüfen ob Cache-Control Richtlinie $key vorhanden ist.
     *
     * @param string $key
     * @return boolean
     */
    public function hasCacheControlDirective($key)
    {
        return array_key_exists($key, $this->computedCacheControl);
    }

    /**
     * Ausgeben einer Cache-Control Richtlinie.
     *
     * @param string $key
     * @return string|boolean
     */
    public function getCacheControlDirective($key)
    {
        return array_key_exists($key, $this->computedCacheControl) ? $this->computedCacheControl[$key] : null;
    }

    /**
     * Cookie Setzen.
     *
     * @param Cookie $cookie
     */
    public function setCookie(Cookie $cookie)
    {
        $this->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
    }

    /**
     * Cookie entfernen.
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     */
    public function removeCookie($name, $path = '/', $domain = null)
    {
        if (null === $path) {
            $path = '/';
        }

        unset($this->cookies[$domain][$path][$name]);

        if (empty($this->cookies[$domain][$path])) {
            unset($this->cookies[$domain][$path]);

            if (empty($this->cookies[$domain])) {
                unset($this->cookies[$domain]);
            }
        }
    }

    /**
     * Cookie Inhalt zurückgeben.
     *
     * @param string $format
     *
     * @throws \InvalidArgumentException When the $format is invalid
     *
     * @return array
     */
    public function getCookies($format = self::COOKIES_FLAT)
    {
        if (!in_array($format, array(self::COOKIES_FLAT, self::COOKIES_ARRAY))) {
            throw new \InvalidArgumentException(sprintf('Format "%s" invalid (%s).', $format, implode(', ', array(self::COOKIES_FLAT, self::COOKIES_ARRAY))));
        }

        if (self::COOKIES_ARRAY === $format) {
            return $this->cookies;
        }

        $flattenedCookies = array();
        foreach ($this->cookies as $path) {
            foreach ($path as $cookies) {
                foreach ($cookies as $cookie) {
                    $flattenedCookies[] = $cookie;
                }
            }
        }

        return $flattenedCookies;
    }

    /**
     * Cookie löschen.
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     *
     * @api
     */
    public function clearCookie($name, $path = '/', $domain = null)
    {
        $this->setCookie(new Cookie($name, null, 1, $path, $domain));
    }

    /**
     * Generates a HTTP Content-Disposition field-value.
     *
     * @param string $disposition      One of "inline" or "attachment"
     * @param string $filename         A unicode string
     * @param string $filenameFallback A string containing only ASCII characters that
     *                                 is semantically equivalent to $filename. If the filename is already ASCII,
     *                                 it can be omitted, or just copied from $filename
     *
     * @return string A string suitable for use as a Content-Disposition field-value.
     *
     * @throws \InvalidArgumentException
     * @see RFC 6266
     */
    public function makeDisposition($disposition, $filename, $filenameFallback = '')
    {
        if (!in_array($disposition, array(self::DISPOSITION_ATTACHMENT, self::DISPOSITION_INLINE))) {
            throw new \InvalidArgumentException(sprintf('The disposition must be either "%s" or "%s".', self::DISPOSITION_ATTACHMENT, self::DISPOSITION_INLINE));
        }

        if ('' == $filenameFallback) {
            $filenameFallback = $filename;
        }

        if (!preg_match('/^[\x20-\x7e]*$/', $filenameFallback)) {
            throw new \InvalidArgumentException('The filename fallback must only contain ASCII characters.');
        }

        if (false !== strpos($filenameFallback, '%')) {
            throw new \InvalidArgumentException('The filename fallback cannot contain the "%" character.');
        }

        if (false !== strpos($filename, '/') || false !== strpos($filename, '\\') || false !== strpos($filenameFallback, '/') || false !== strpos($filenameFallback, '\\')) {
            throw new \InvalidArgumentException('The filename and the fallback cannot contain the "/" and "\\" characters.');
        }

        $output = sprintf('%s; filename="%s"', $disposition, str_replace('"', '\\"', $filenameFallback));

        if ($filename !== $filenameFallback) {
            $output .= sprintf("; filename*=utf-8''%s", rawurlencode($filename));
        }

        return $output;
    }

    /**
     * Returns the calculated value of the cache-control header.
     *
     * This considers several other headers and calculates or modifies the
     * cache-control header to a sensible, conservative value.
     *
     * @return string
     */
    protected function computeCacheControlValue()
    {
        if (!$this->cacheControl && !$this->contains('ETag') && !$this->contains('Last-Modified') && !$this->contains('Expires')) {
            return 'no-cache';
        }

        if (!$this->cacheControl) {
            return 'private, must-revalidate';
        }

        $header = $this->getCacheControlHeader();
        if (isset($this->cacheControl['public']) || isset($this->cacheControl['private'])) {
            return $header;
        }

        if (!isset($this->cacheControl['s-maxage'])) {
            return $header . ', private';
        }

        return $header;
    }

    /**
     * Object to String Konvertierung.
     *
     * @return string
     */
    public function __toString()
    {
        $cookies = '';
        foreach ($this->getCookies() as $cookie) {
            $cookies .= 'Set-Cookie: ' . $cookie . "\r\n";
        }

        return parent::__toString() . $cookies;
    }

}
