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
 * Klasse ServerBag
 *
 * @version ::VERSION::
 */
class ServerBag extends ParameterBag {

    /**
     * Gets the HTTP headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = array();
        foreach ($this->parameters as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            }
            // CONTENT_* are not prefixed with HTTP_
            elseif (in_array($key, array('CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'))) {
                $headers[$key] = $value;
            }
        }

        if ($this->has('PHP_AUTH_USER')) {
            $this->set('PHP_AUTH_USER', $this->get('PHP_AUTH_USER'));
            if ($this->has('PHP_AUTH_PW')) {
                $this->set('PHP_AUTH_PW', $this->get('PHP_AUTH_PW'));
            }
        } else {
            $authorizationHeader = null;
            if ($this->has('HTTP_AUTHORIZATION')) {
                $authorizationHeader = $this->get("HTTP_AUTHORIZATION");
            } elseif ($this->has('REDIRECT_HTTP_AUTHORIZATION')) {
                $authorizationHeader = $this->get("REDIRECT_HTTP_AUTHORIZATION");
            }

            if ((null !== $authorizationHeader) && (0 === stripos($authorizationHeader, 'basic'))) {
                $exploded = explode(':', base64_decode(substr($authorizationHeader, 6)));
                if (count($exploded) == 2) {
                    list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
                }
            }
        }

        if (isset($headers['PHP_AUTH_USER'])) {
            $headers['AUTHORIZATION'] = 'Basic ' . base64_encode($headers['PHP_AUTH_USER'] . ':' . $headers['PHP_AUTH_PW']);
        }

        return $headers;
    }

}
