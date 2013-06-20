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
 * @package   Panda.Core
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Panda\Core;

/**
 * Config | Einstellungen des Frameworks speichern, lesen, löschen.
 *
 * @version ::VERSION::
 * @package Panda.Core
 */
class Config extends Object {

    /**
     * Speichert alle Verfügbaren Config Einträge.
     *
     * @var array
     */
    protected static $_settings = array(
        'debug' => false
    );

    /**
     * Speicher Einstellungen in der Konfiguration
     *
     * @param mixed $config
     * @param mixed $value
     * @return boolean
     */
    public static function set($config, $value = null)
    {
        if (!is_array($config)) {
            $config = array($config => $value);
        }

        foreach ($config as $name => $value) {
            $settings = &static::$_settings;
            foreach (explode('.', $name) as $key) {
                $settings = &$settings[$key];
            }
            $settings = $value;
            unset($settings);
        }
        return true;
    }

    /**
     * Liest Einstellungen aus der Aktuellen Konfiguration.
     *
     * @param string $var
     * @return null
     */
    public static function get($var = null)
    {
        if ($var === null) {
            return static::$_settings;
        }
        if (isset(static::$_settings[$var])) {
            return static::$_settings[$var];
        }
        $settings = &static::$_settings;
        foreach (explode('.', $var) as $key) {
            if (isset($settings[$key])) {
                $settings = &$settings[$key];
            } else {
                return null;
            }
        }
        return $settings;
    }

    /**
     * Löscht einzelne Einstellungen in der Aktuellen Konfiguration.
     *
     * @param type $var
     */
    public static function delete($var = null)
    {
        $keys = explode('.', $var);
        $last = array_pop($keys);
        $settings = &static::$_settings;
        foreach ($keys as $key) {
            $settings = &$settings[$key];
        }
        unset($settings[$last]);
    }

}
