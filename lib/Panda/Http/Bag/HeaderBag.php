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

/**
 * Klasse HeaderBag
 *
 * @version ::VERSION::
 */
class HeaderBag implements \IteratorAggregate, \Countable {

    /**
     * Speichert HTTP Header.
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Speichert Header Caching Informationen.
     *
     * @var array
     */
    protected $cacheControl = array();

    /**
     * Constructor.
     *
     * @param array $headers Array mit HTTP Headern
     *
     * @api
     */
    public function __construct(array $headers = array())
    {
        $this->cacheControl = array();
        $this->headers = array();
        foreach ($headers as $key => $values) {
            $this->set($key, $values);
        }
    }

    /**
     * Gibt die vorhandenen HTTP Header als String zurück.
     *
     * @return string
     */
    public function __toString()
    {
        if (!$this->headers) {
            return '';
        }

        $max = max(array_map('strlen', array_keys($this->headers))) + 1;
        $content = '';
        ksort($this->headers);
        foreach ($this->headers as $name => $values) {
            $name = implode('-', array_map('ucfirst', explode('-', $name)));
            foreach ($values as $value) {
                $content .= sprintf("%-{$max}s %s\r\n", $name . ':', $value);
            }
        }

        return $content;
    }

    /**
     * Gibt alle vorhandenen HTTP Header zurück.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Gibt die HTTP Header Array KEYs zurück.
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->headers);
    }

    /**
     * Ersetzt alle vorhandenen Header mit $headers.
     *
     * @param array $headers Array mit HTTP Headern
     */
    public function replace(array $headers = array())
    {
        $this->headers = array();
        $this->add($headers);
    }

    /**
     * Fügt neue HTTP Header hinzu.
     *
     * @param array $headers Array mit HTTP Headern
     */
    public function add(array $headers)
    {
        foreach ($headers as $key => $values) {
            $this->set($key, $values);
        }
    }

    /**
     * Gibt einen Speziellen Header zurück.
     *
     * @param string  $key     Header Name
     * @param mixed   $default Defaulte Ausgabe.
     * @param Boolean $first   nur ersten Header?
     *
     * @return string|array Array mit allen Headern oder string mit dem ersten Header
     */
    public function get($key, $default = null, $first = true)
    {
        $key = strtr(strtolower($key), '_', '-');

        if (!array_key_exists($key, $this->headers)) {
            if (null === $default) {
                return $first ? null : array();
            }

            return $first ? $default : array($default);
        }

        if ($first) {
            return count($this->headers[$key]) ? $this->headers[$key][0] : $default;
        }

        return $this->headers[$key];
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
        $key = strtr(strtolower($key), '_', '-');

        $values = array_values((array) $values);

        if (true === $replace || !isset($this->headers[$key])) {
            $this->headers[$key] = $values;
        } else {
            $this->headers[$key] = array_merge($this->headers[$key], $values);
        }

        if ('cache-control' === $key) {
            $this->cacheControl = $this->parseCacheControl($values[0]);
        }
    }

    /**
     * Prüft ob $key Header vorhanden ist.
     *
     * @param string $key HTTP Header Key
     *
     * @return boolean
     */
    public function has($key)
    {
        return array_key_exists(strtr(strtolower($key), '_', '-'), $this->headers);
    }

    /**
     * Entfernt einen Header
     *
     * @param string $key HTTP Header Name
     */
    public function remove($key)
    {
        $key = strtr(strtolower($key), '_', '-');

        unset($this->headers[$key]);

        if ('cache-control' === $key) {
            $this->cacheControl = array();
        }
    }

    /**
     * Gibt den HTTP Header als DateTime zurück.
     *
     * @param string    $key     HTTP Header Name
     * @param \DateTime $default Header Value
     *
     * @return null|\DateTime Gefilterte Value des Headers
     *
     * @throws \RuntimeException
     */
    public function getDate($key, \DateTime $default = null)
    {
        if (null === $value = $this->get($key)) {
            return $default;
        }

        if (false === $date = \DateTime::createFromFormat(DATE_RFC2822, $value)) {
            throw new \RuntimeException(sprintf('The %s HTTP header is not parseable (%s).', $key, $value));
        }

        return $date;
    }

    /**
     * Hinzufügen einer Cache-Control Richtlinie
     *
     * @param string $key
     * @param string $value
     */
    public function addCacheControlDirective($key, $value = true)
    {
        $this->cacheControl[$key] = $value;

        $this->set('Cache-Control', $this->getCacheControlHeader());
    }

    /**
     * Überprüfen ob Cache-Control Richtlinie $key vorhanden ist.
     *
     * @param string $key
     * @return boolean
     */
    public function hasCacheControlDirective($key)
    {
        return array_key_exists($key, $this->cacheControl);
    }

    /**
     * Ausgeben einer Cache-Control Richtlinie.
     *
     * @param string $key
     * @return string|boolean
     */
    public function getCacheControlDirective($key)
    {
        return array_key_exists($key, $this->cacheControl) ? $this->cacheControl[$key] : null;
    }

    /**
     * Entfernen einer Cache-Control Richtlinie.
     *
     * @param string $key
     */
    public function removeCacheControlDirective($key)
    {
        unset($this->cacheControl[$key]);

        $this->set('Cache-Control', $this->getCacheControlHeader());
    }

    /**
     * Gobt einen iterator für die Header zurück.
     *
     * @return \ArrayIterator \ArrayIterator Instanz
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->headers);
    }

    /**
     * Gibt die Anzahl vorhandener Header zurück..
     *
     * @return int
     */
    public function count()
    {
        return count($this->headers);
    }

    /**
     * Auslesen eines Cache-Control HTTP Headers.
     *
     * @return string
     */
    protected function getCacheControlHeader()
    {
        $parts = array();
        ksort($this->cacheControl);
        foreach ($this->cacheControl as $key => $value) {
            if (true === $value) {
                $parts[] = $key;
            } else {
                if (preg_match('#[^a-zA-Z0-9._-]#', $value)) {
                    $value = '"' . $value . '"';
                }

                $parts[] = "$key=$value";
            }
        }

        return implode(', ', $parts);
    }

    /**
     * Parst einen Cache-Control HTTP Header.
     *
     * @param string $header
     *
     * @return array
     */
    protected function parseCacheControl($header)
    {
        $cacheControl = array();
        preg_match_all('#([a-zA-Z][a-zA-Z_-]*)\s*(?:=(?:"([^"]*)"|([^ \t",;]*)))?#', $header, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $cacheControl[strtolower($match[1])] = isset($match[2]) && $match[2] ? $match[2] : (isset($match[3]) ? $match[3] : true);
        }

        return $cacheControl;
    }

}
