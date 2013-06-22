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
 * @package   Panda.Http
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Panda\Http;

use Panda\Core\Config;

/**
 * Klasse Request
 *
 * @version ::VERSION::
 */
class Request {

    /**
     * Requeste Methods
     */

    const METHOD_GET = "GET";
    const METHOD_HEAD = "HEAD";
    const METHOD_POST = "POST";
    const METHOD_PUT = "PUT";
    const METHOD_DELETE = "DELETE";
    const METHOD_OPTIONS = "OPTIONS";
    const METHOD_TRACE = "TRACE";
    const METHOD_CONNECT = "CONNECT";

    /**
     * Request Protocols
     */
    const PROTOCOL_HTTP = "http";
    const PROTOCOL_HTTPS = "https";

    /**
     * Default Settings
     */
    const DEFAULT_PROTOCOL = "http";
    const DEFAULT_HOST = "localhost";
    const DEFAULT_PORT = 80;
    const DEFAULT_PATH = "/";

    /**
     * Exception Codes
     */
    CONST EXCEPTION_CODE_INVALID_PORT = 1;
    CONST EXCEPTION_CODE_HEADER_NOT_FOUND = 2;
    CONST EXCEPTION_CODE_INVALID_PARAMS = 3;
    CONST EXCEPTION_CODE_PARAM_NOT_FOUND = 4;
    CONST EXCEPTION_CODE_INVALID_PARAM = 5;
    CONST EXCEPTION_CODE_COOKIE_NOT_FOUND = 6;
    CONST EXCEPTION_CODE_SESSION_PARAM_NOT_FOUND = 7;

    protected $_method = null;
    protected $_protocol = null;
    protected $_host = null;
    protected $_port = null;
    protected $_path = null;
    protected $_headers = array();
    protected $_params = array();
    protected $_cookies = array();
    protected $_session = array();

    public static function createFromGloabals($base = true)
    {
        $method = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : null;
        $protocol = isset($_SERVER["SERVER_PROTOCOL"]) ? (strpos($_SERVER["SERVER_PROTOCOL"], "HTTPS") === false ? static::PROTOCOL_HTTP : static::PROTOCOL_HTTPS) : null;
        $host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : null;
        $port = isset($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : null;
        $path = isset($_SERVER["REQUEST_URI"]) ? parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) : null;
        $headers = static::_requestHeaders();
        $params = array();
        $cookies = $_COOKIE;
        $session = isset($_SESSION) ? $_SESSION : array();

        if ($base && isset($path)) {
            $path = "/" . trim($path, "/") . "/";

            if (strpos($path, $base_path) === 0) {
                if (false === ($path = substr($path, strlen($base_path)))) {
                    $path = "";
                }
            }
        }

        $request = new static($method, $protocol, $host, $port, $path, $headers, $params, $cookies, $session);
        if (isset($_GET))
            $request->setParams(static::METHOD_GET, $_GET);
        if (isset($_POST))
            $request->setParams(static::METHOD_POST, $_POST);

        return $request;
    }

    public function __construct($method = null, $protocol = null, $host = null, $port = null, $path = null, array $headers = array(), array $params = array(), array $cookies = array(), array $session = array())
    {
        $this->setMethod($method)
            ->setProtocol($protocol)
            ->setHost($host)
            ->setPort($port)
            ->setPath($path)
            ->setHeaders($headers)
            ->setParams($params)
            ->setCookies($cookies)
            ->setSession($session);
    }

    protected static function _requestHeaders()
    {
        if (function_exists("getallheaders") && false !== ($headers = getallheaders()))
            return $headers;
        else {
            $headers = array();
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, "HTTP_") === 0) {
                    $key = implode("-", array_map("ucfirst", explode("_", strtolower(substr($key, 5)))));
                    $headers[$key] = $value;
                }
            }

            return $headers;
        }
    }

    /* ---------------------------------------------------- */
    /* ------------------ Request Method ------------------ */
    /* ---------------------------------------------------- */

    public function getMethod()
    {
        return $this->issetMethod() ? $this->_method : static::METHOD_GET;
    }

    public function setMethod($method)
    {
        $this->_method = $method;

        return $this;
    }

    public function issetMethod()
    {
        return isset($this->_method);
    }

    /* ------------------------------------------------------ */
    /* ------------------ Request Protocol ------------------ */
    /* ------------------------------------------------------ */

    public function getProtocol()
    {
        return $this->issetProtocol() ? $this->_protocol : static::DEFAULT_PROTOCOL;
    }

    public function setProtocol($protocol)
    {
        $this->_protocol = $protocol;

        return $this;
    }

    public function issetProtocol()
    {
        return isset($this->_protocol);
    }

    /* -------------------------------------------------- */
    /* ------------------ Request Host ------------------ */
    /* -------------------------------------------------- */

    public function getHost()
    {
        return $this->issetHost() ? $this->_host : static::DEFAULT_HOST;
    }

    public function setHost($host)
    {
        $this->_host = $host;

        return $this;
    }

    public function issetHost()
    {
        return isset($this->_host);
    }

    /* -------------------------------------------------- */
    /* ------------------ Request Port ------------------ */
    /* -------------------------------------------------- */

    public function getPort()
    {
        return $this->issetPort() ? $this->_port : static::DEFAULT_PORT;
    }

    public function setPort($port)
    {
        if (isset($port)) {
            $port = (int) $port;
            if ($port < 1)
                throw new \InvalidArgumentException("Port must be greater than zero.", static::EXCEPTION_CODE_INVALID_PORT);
        }

        $this->_port = $port;

        return $this;
    }

    public function issetPort()
    {
        return isset($this->_port);
    }

    /* -------------------------------------------------- */
    /* ------------------ Request Path ------------------ */
    /* -------------------------------------------------- */

    public function getPath()
    {
        return $this->issetPath() ? $this->_path : static::DEFAULT_PATH;
    }

    public function setPath($path)
    {
        if (isset($path)) {
            $path = "/" . trim($path, "/");
            if (isset($path[1]) and pathinfo($path, PATHINFO_EXTENSION) === "")
                $path .= "/";
        }

        $this->_path = $path;

        return $this;
    }

    public function issetPath()
    {
        return isset($this->_path);
    }

    /* ------------------------------------------------------- */
    /* ------------------ Request Header(s) ------------------ */
    /* ------------------------------------------------------- */

    public function getHeaders()
    {
        return $this->_headers;
    }

    public function setHeaders(array $headers)
    {
        if (!isset($headers))
            $headers = array();

        $this->_headers = array();
        foreach ($headers as $header => $value)
            $this->setHeader($header, $value);

        return $this;
    }

    public function getHeader($header)
    {
        if (!isset($this->_headers[$header]))
            throw new \OutOfBoundsException("Header '{$header}' was not found.", static::EXCEPTION_CODE_HEADER_NOT_FOUND);

        return $this->_headers[$header];
    }

    public function setHeader($header, $value)
    {
        $this->_headers[$header] = $value;

        return $this;
    }

    public function deleteHeader($header)
    {
        unset($this->_headers[$header]);

        return $this;
    }

    /* ------------------------------------------------------ */
    /* ------------------ Request Param(s) ------------------ */
    /* ------------------------------------------------------ */

    public function getParams($method = null)
    {
        return isset($method) ? $this->getMethodParams($method) : $this->getCurrentMethodParams();
    }

    public function getMethodParams($method)
    {
        return isset($this->_params[$method]) ? $this->_params[$method] : array();
    }

    public function getCurrentMethodParams()
    {
        return $this->getMethodParams($this->method());
    }

    public function setParams($method, $params = null)
    {
        if (!isset($params))
            list($method, $params) = array(null, $method);

        if (!is_array($params))
            throw new \BadMethodCallException("Params must be an array.", static::EXCEPTION_CODE_INVALID_PARAMS);

        return isset($method) ? $this->setMethodParams($method, $params) : $this->setCurrentMethodParams($params);
    }

    public function setMethodParams($method, array $params)
    {
        $this->_params[$method] = array();

        foreach ($params as $param => $value)
            $this->setMethodParam($method, $param, $value);

        return $this;
    }

    public function setCurrentMethodParams(array $params)
    {
        return $this->setMethodParams($this->getMethod(), $params);
    }

    /* --------------------------------------------------- */
    /* ------------------ Request Param ------------------ */
    /* --------------------------------------------------- */

    public function getParam($method, $param = null)
    {
        if (!isset($param))
            list($method, $param) = array(null, $param);

        return isset($method) ? $this->getMethodParam($method, $param) : $this->getCurrentMethodParam($param);
    }

    public function getMethodParam($method, $param)
    {
        if (!isset($this->_params[$method]) or !isset($this->_params[$method][$param]))
            throw new \OutOfBoundsException("Param '{$param}' wasnt't found for method '{$method}'.", static::EXCEPTION_CODE_PARAM_NOT_FOUND);

        return $this->_params[$method][$param];
    }

    public function getCurrentMethodParam($param)
    {
        return $this->getMethodParam($this->getMethod(), $param);
    }

    public function setParam($method, $param, $value = null)
    {
        $arg_count = func_num_args();
        if ($arg_count < 2)
            throw new \BadMethodCallException("At minimum param and value is required.", static::EXCEPTION_CODE_INVALID_PARAM);

        if ($arg_count === 2)
            list($method, $param, $value) = array(null, $method, $param);

        return isset($method) ? $this->setMethodParam($method, $param, $value) : $this->setCurrentMethodParam($param, $value);
    }

    public function setMethodParam($method, $param, $value)
    {
        if (!isset($this->_params[$method]))
            $this->_params[$method] = array();

        $this->_params[$method][$param] = $value;

        return $this;
    }

    public function setCurrentMethodParam($param, $value)
    {
        return $this->setMethodParam($this->getMethod(), $param, $value);
    }

    public function deleteParam($method, $param = null)
    {
        if (!isset($param))
            list($method, $param) = array(null, $param);

        return isset($method) ? $this->deleteMethodParam($method, $param) : $this->deleteCurrentMethodParam($param);
    }

    public function deleteMethodParam($method, $param)
    {
        if (isset($this->_params[$method]))
            unset($this->_params[$method][$param]);

        return $this;
    }

    public function deleteCurrentMethodParam($param)
    {
        return $this->deleteMethodParam($this->getMethod(), $param);
    }

    /* ------------------------------------------------------- */
    /* ------------------ Request Cookie(s) ------------------ */
    /* ------------------------------------------------------- */

    public function getCookies()
    {
        return $this->_cookies;
    }

    public function setCookies(array $cookies)
    {
        $this->_cookies = array();

        foreach ($cookies as $cookie => $value)
            $this->setCookie($cookie, $value);

        return $this;
    }

    public function cookie($cookie)
    {
        if (!isset($this->_cookies[$cookie]))
            throw new \OutOfBoundsException("Cookie '{$cookie}' wasn't found.", static::EXCEPTION_CODE_COOKIE_NOT_FOUND);

        return $this->_cookies[$cookie];
    }

    public function setCookie($cookie, $value)
    {
        $this->_cookies[$cookie] = $value;
    }

    public function deleteCookie($cookie)
    {
        unset($this->_cookies[$cookie]);

        return $this;
    }

    /* ----------------------------------------------------- */
    /* ------------------ Request Session ------------------ */
    /* ----------------------------------------------------- */

    public function getSession()
    {
        return $this->_session;
    }

    public function setSession(array $session)
    {
        $this->_session = array();

        foreach ($session as $param => $value)
            $this->setSessionParam($param, $value);

        return $this;
    }

    public function getSessionParam($param)
    {
        if (!isset($this->_session[$param]))
            throw new \OutOfBoundsException("Session param '{$param}' wasn't found.", static::EXCEPTION_CODE_SESSION_PARAM_NOT_FOUND);

        return $this->_session[$param];
    }

    public function setSessionParam($param, $value)
    {
        $this->_session[$param] = $value;
    }

    public function deleteSessionParam($param)
    {
        unset($this->_session[$param]);

        return $this;
    }

    /* --------------------------------------------------------- */
    /* ------------------ Protected Functions ------------------ */
    /* --------------------------------------------------------- */

}
