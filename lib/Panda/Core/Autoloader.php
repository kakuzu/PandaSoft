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
 * @package   Panda.Core
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Panda\Core;

/**
 * Klasse Autoloader
 *
 * @version ::VERSION::
 * @package Panda.Core
 */
class Autoloader {

    /**
     * Dateiendung für Klassendateien.
     *
     * @var string
     */
    protected $_fileExtension = '.php';

    /**
     * Include Pfad für Klassendateien.
     *
     * @var string
     */
    protected $_includePath;

    /**
     * Registrierter Namespace
     *
     * @var string
     */
    protected $_namespace;

    /**
     * Speichert die Namespace länge. (Bessere Performence)
     *
     * @var integer
     */
    protected $_namespaceLength;

    /**
     * Constructor
     *
     * @param string $ns Namespace.
     * @param string $includePath Includepfad zum Namepsace
     */
    public function __construct($ns = null, $includePath = null)
    {
        $this->_namespace = rtrim($ns, '\\') . '\\';
        $this->_namespaceLength = strlen($this->_namespace);
        $this->_includePath = $includePath;
    }

    /**
     * Setzen des Namespace.
     *
     * @param string $includePath
     * @return void
     */
    public function setIncludePath($includePath)
    {
        $this->_includePath = $includePath;
    }

    /**
     * Gibt den IncludePfad des Namespace zurück.
     *
     * @return string
     */
    public function getIncludePath()
    {
        return $this->_includePath;
    }

    /**
     * Setzen der Dateiendung für Klassen es Namespace
     *
     * @param string $fileExtension
     * @return void
     */
    public function setFileExtension($fileExtension)
    {
        $this->_fileExtension = $fileExtension;
    }

    /**
     * Gibt die gespeicherte Dateiendung für Klasse des >Namespace zurück
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->_fileExtension;
    }

    /**
     * Installiert eine neue Instanz des Panda\Core\Autoloader im spl_autoload stack.
     *
     * @return void
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * Entfernt die Instanz des Panda\Core\Autoloader im spl_autoload stack.
     *
     * @return void
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * Laden der Klasse / Interface
     *
     * @param string $className Name der zu ladenen Klasse.
     * @return boolean
     */
    public function loadClass($className)
    {
        if (substr($className, 0, $this->_namespaceLength) === $this->_namespace) {
            $path = $this->_includePath . DS . str_replace('\\', DS, $className) . $this->_fileExtension;
            if (file_exists($path)) {
                return require $path;
            }
        }
        return false;
    }

}
