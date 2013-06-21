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

use Panda\Http\Bag\ParameterBag;
use Panda\Http\File\UploadedFile;

/**
 * Klasse FileBag
 *
 * @version ::VERSION::
 */
class FileBag extends ParameterBag {

    private static $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');

    /**
     * Setzen eines Parameters.
     *
     * @param string $key   Parameter Key
     * @param mixed  $value Parameter Value
     */
    public function set($key, $value)
    {
        if (!is_array($value) && !$value instanceof UploadedFile) {
            throw new InvalidArgumentException('An uploaded file must be an array or an instance of UploadedFile.');
        }

        parent::set($key, $this->convertFileInformation($value));
    }

    /**
     * Konvertiert Hochgeladene Dateien in eine UploadedFile Instanz
     *
     * @param array|UploadedFile $file
     *
     * @return array
     */
    protected function convertFileInformation($file)
    {
        if ($file instanceof UploadedFile) {
            return $file;
        }

        $file = $this->fixPhpFilesArray($file);
        if (is_array($file)) {
            $keys = array_keys($file);
            sort($keys);

            if ($keys == self::$fileKeys) {
                if (UPLOAD_ERR_NO_FILE == $file['error']) {
                    $file = null;
                } else {
                    $file = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['error']);
                }
            } else {
                $file = array_map(array($this, 'convertFileInformation'), $file);
            }
        }

        return $file;
    }

    /**
     * Reparieren eines Fehlerhaften PHP $_FILES array.
     *
     * @param array $data
     *
     * @return array
     */
    protected function fixPhpFilesArray($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $keys = array_keys($data);
        sort($keys);

        if (self::$fileKeys != $keys || !isset($data['name']) || !is_array($data['name'])) {
            return $data;
        }

        $files = $data;
        foreach (self::$fileKeys as $k) {
            unset($files[$k]);
        }

        foreach (array_keys($data['name']) as $key) {
            $files[$key] = $this->fixPhpFilesArray(array(
                'error' => $data['error'][$key],
                'name' => $data['name'][$key],
                'type' => $data['type'][$key],
                'tmp_name' => $data['tmp_name'][$key],
                'size' => $data['size'][$key]
            ));
        }

        return $files;
    }

}
