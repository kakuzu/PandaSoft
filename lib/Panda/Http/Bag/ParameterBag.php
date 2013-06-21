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
 * Klasse ParameterBag
 *
 * @version ::VERSION::
 */
class ParameterBag implements \IteratorAggregate, \Countable {

    /**
     * Parameter Speicher.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Constructor.
     *
     * @param array $parameters Array mit Parametern
     */
    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    /**
     * Gibt alle Parameter zurück.
     *
     * @return array Array mit allen Parametern
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * Gibt alle Parameter Array KEYs zurück
     *
     * @return array Array mit Parameter KEYs
     */
    public function keys()
    {
        return array_keys($this->parameters);
    }

    /**
     * Ersetzt alle Parameter mit neuen Parametern
     *
     * @param array $parameters Array mit Parametern
     */
    public function replace(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    /**
     * Hinzufügen von Parametern..
     *
     * @param array $parameters Array mit Parametern
     */
    public function add(array $parameters = array())
    {
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    /**
     * Gibt einen Speziellen Parameter zurück $default
     *
     * @param string  $path    Parameter Key
     * @param mixed   $default Default rückgabewert
     * @param boolean $deep    Tiefere Parameter Pfade durchsuchen?
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($path, $default = null, $deep = false)
    {
        if (!$deep || false === $pos = strpos($path, '[')) {
            return array_key_exists($path, $this->parameters) ? $this->parameters[$path] : $default;
        }

        $root = substr($path, 0, $pos);
        if (!array_key_exists($root, $this->parameters)) {
            return $default;
        }

        $value = $this->parameters[$root];
        $currentKey = null;
        for ($i = $pos, $c = strlen($path); $i < $c; $i++) {
            $char = $path[$i];

            if ('[' === $char) {
                if (null !== $currentKey) {
                    throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "[" at position %d.', $i));
                }

                $currentKey = '';
            } elseif (']' === $char) {
                if (null === $currentKey) {
                    throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "]" at position %d.', $i));
                }

                if (!is_array($value) || !array_key_exists($currentKey, $value)) {
                    return $default;
                }

                $value = $value[$currentKey];
                $currentKey = null;
            } else {
                if (null === $currentKey) {
                    throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "%s" at position %d.', $char, $i));
                }

                $currentKey .= $char;
            }
        }

        if (null !== $currentKey) {
            throw new \InvalidArgumentException(sprintf('Malformed path. Path must end with "]".'));
        }

        return $value;
    }

    /**
     * Setzen eines Parameters.
     *
     * @param string $key   Parameter Key
     * @param mixed  $value Parameter Value
     */
    public function set($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Überprüfen ob $key Parameter vorhanden ist.
     *
     * @param string $key Parameter Key
     *
     * @return boolean
     */
    public function has($key)
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Entfernt einen Speziellen Parameter.
     *
     * @param string $key Parameter Key
     */
    public function remove($key)
    {
        unset($this->parameters[$key]);
    }

    /**
     * Gibt die Alphabetischen Inhalte der $key Value zurück.
     *
     * @param string  $key     Parameter Key
     * @param mixed   $default Default rückgabewert
     * @param boolean $deep    Tiefere Parameterpfade durchsuchen?
     *
     * @return string Gefilterte Value des Parameter Keys
     */
    public function getAlpha($key, $default = '', $deep = false)
    {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default, $deep));
    }

    /**
     * Gibt die Alphabetischen und Numerischen Inhalte der $key Value zurück.
     *
     * @param string  $key     Parameter Key
     * @param mixed   $default Default rückgabewert
     * @param boolean $deep    Tiefere Parameterpfade durchsuchen?
     *
     * @return string Gefilterte Value des Parameter Keys
     */
    public function getAlnum($key, $default = '', $deep = false)
    {
        return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default, $deep));
    }

    /**
     * Gibt die Numerischen Inhalte der $key Value zurück.
     *
     * @param string  $key     Parameter Key
     * @param mixed   $default Default rückgabewert
     * @param boolean $deep    Tiefere Parameterpfade durchsuchen?
     *
     * @return string Gefilterte Value des Parameter Keys
     */
    public function getDigits($key, $default = '', $deep = false)
    {
        // we need to remove - and + because they're allowed in the filter
        return str_replace(array('-', '+'), '', $this->filter($key, $default, $deep, FILTER_SANITIZE_NUMBER_INT));
    }

    /**
     * Gibt den Inhalt die Value des Parameter Keys als Int zurück
     *
     * @param string  $key     Parameter Key
     * @param mixed   $default Default rückgabewert
     * @param boolean $deep    Tiefere Parameterpfade durchsuchen?
     *
     * @return string Gefilterte Value des Parameter Keys
     */
    public function getInt($key, $default = 0, $deep = false)
    {
        return (int) $this->get($key, $default, $deep);
    }

    /**
     * Key Filter.
     *
     * @param string  $key     Key.
     * @param mixed   $default Default = null.
     * @param boolean $deep    Default = false.
     * @param integer $filter  FILTER_* constant.
     * @param mixed   $options Filter options.
     *
     * @see http://php.net/manual/en/function.filter-var.php
     *
     * @return mixed
     */
    public function filter($key, $default = null, $deep = false, $filter = FILTER_DEFAULT, $options = array())
    {
        $value = $this->get($key, $default, $deep);
        if (!is_array($options) && $options) {
            $options = array('flags' => $options);
        }

        if (is_array($value) && !isset($options['flags'])) {
            $options['flags'] = FILTER_REQUIRE_ARRAY;
        }

        return filter_var($value, $filter, $options);
    }

    /**
     * Gibt einen iterator mit den Parametern zurück.
     *
     * @return \ArrayIterator Eine \ArrayIterator Instanz
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * Gibt die Anzahl der Vorhandenen Parameter zurück..
     *
     * @return int The number of parameters
     */
    public function count()
    {
        return count($this->parameters);
    }

}
