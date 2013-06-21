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

namespace Panda\Tools;

/**
 * Klasse Hash
 *
 * @version ::VERSION::
 */
class Hash {

    /* -------------------------------------
     * Getter Funktionen
     */

    public static function get(array $data, $path)
    {
        if (empty($data)) {
            return null;
        }
        if (is_string($path) || is_numeric($path)) {
            $parts = explode('.', $path);
        } else {
            $parts = $path;
        }
        foreach ($parts as $key) {
            if (is_array($data) && isset($data[$key])) {
                $data = & $data[$key];
            } else {
                return null;
            }
        }
        return $data;
    }

    /* -------------------------------------
     * Setter Funktionen
     */

    public static function insert(array $data, $path, $values = null)
    {
        $tokens = explode('.', $path);
        if (strpos($path, '{') === false) {
            return static::_simpleOp('insert', $data, $tokens, $values);
        }

        $token = array_shift($tokens);
        $nextPath = implode('.', $tokens);
        foreach ($data as $k => $v) {
            if (static::_matchToken($k, $token)) {
                $data[$k] = static::insert($v, $nextPath, $values);
            }
        }
        return $data;
    }

    /* -------------------------------------
     * Protected Funktionen
     */

    protected static function _simpleOp($op, $data, $path, $values = null)
    {
        $_list = & $data;

        $count = count($path);
        $last = $count - 1;
        foreach ($path as $i => $key) {
            if (is_numeric($key) && intval($key) > 0 || $key === '0') {
                $key = intval($key);
            }
            if ($op === 'insert') {
                if ($i === $last) {
                    $_list[$key] = $values;
                    return $data;
                }
                if (!isset($_list[$key])) {
                    $_list[$key] = array();
                }
                $_list = & $_list[$key];
                if (!is_array($_list)) {
                    $_list = array();
                }
            } elseif ($op === 'remove') {
                if ($i === $last) {
                    unset($_list[$key]);
                    return $data;
                }
                if (!isset($_list[$key])) {
                    return $data;
                }
                $_list = & $_list[$key];
            }
        }
    }

    protected static function _matchToken($key, $token)
    {
        if ($token === '{n}') {
            return is_numeric($key);
        }
        if ($token === '{s}') {
            return is_string($key);
        }
        if (is_numeric($token)) {
            return ($key == $token);
        }
        return ($key === $token);
    }

}
