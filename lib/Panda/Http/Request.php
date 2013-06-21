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

use Panda\Interfaces;
use Panda\Tools\Hash;

/**
 * Request bildet die Schnittstelle zwischen HTTP-Request und Framework.
 *
 * @version ::VERSION::
 * @package   Panda.Http
 */
class Request implements \ArrayAccess {

    const HEADER_CLIENT_IP = 'client_ip';
    const HEADER_CLIENT_HOST = 'client_host';
    const HEADER_CLIENT_PROTO = 'client_proto';
    const HEADER_CLIENT_PORT = 'client_port';

    /**
     * Dispatched Request Parameter
     *
     * @var array
     */
    public $params = array(
        'plugin' => null,
        'controller' => null,
        'action' => null,
        'pass' => array(),
    );

    /**
     * Request Daten
     *
     * @var array
     */
    public $data = array();

    /**
     * Enthält alle $_SERVER Inhalte.
     *
     * @var object ServerBag
     */
    public $server;

    /**
     * Enthält alle Header Informationen.
     *
     * @var object HeaderBag
     */
    public $headers;

    /**
     * Enthält Optionale Request Inhalte.
     *
     * @var object ParameterBag
     */
    public $attributes;

    /**
     * Enthält alle $_GET Inhalte
     *
     * @var object ParameterBag
     */
    public $query = array();

    /**
     * Enhält alle $_COOKIE Inhalte
     *
     * @var object ParameterBag
     */
    public $cookies;

    /**
     * Enthält alle $_FILES
     *
     * @var object FileBag
     */
    public $files;

    /**
     * Proxyserver verwenden?
     *
     * @var boolean
     */
    protected static $trustProxy = false;

    /**
     * Erlaube Proxyserver
     *
     * @var array
     */
    protected static $trustedProxies = array();

    /**
     * Header Vars für Erlaubte Proxy
     *
     * @var array
     */
    protected static $trustedHeaders = array(
        self::HEADER_CLIENT_IP => 'X_FORWARDED_FOR',
        self::HEADER_CLIENT_HOST => 'X_FORWARDED_HOST',
        self::HEADER_CLIENT_PROTO => 'X_FORWARDED_PROTO',
        self::HEADER_CLIENT_PORT => 'X_FORWARDED_PORT',
    );

    /**
     * Detectors Map zum Ermitteln der Request Methode und Client Infos.
     *
     * @var array
     */
    protected $_detectors = array(
        'get' => array('env' => 'REQUEST_METHOD', 'value' => 'GET'),
        'post' => array('env' => 'REQUEST_METHOD', 'value' => 'POST'),
        'put' => array('env' => 'REQUEST_METHOD', 'value' => 'PUT'),
        'delete' => array('env' => 'REQUEST_METHOD', 'value' => 'DELETE'),
        'head' => array('env' => 'REQUEST_METHOD', 'value' => 'HEAD'),
        'options' => array('env' => 'REQUEST_METHOD', 'value' => 'OPTIONS'),
        'ssl' => array('env' => 'HTTPS', 'value' => 1),
        'ajax' => array('env' => 'HTTP_X_REQUESTED_WITH', 'value' => 'XMLHttpRequest'),
        'flash' => array('env' => 'HTTP_USER_AGENT', 'pattern' => '/^(Shockwave|Adobe) Flash/'),
        'mobile' => array('env' => 'HTTP_USER_AGENT', 'options' => array(
                'Android', 'AvantGo', 'BlackBerry', 'DoCoMo', 'Fennec', 'iPod', 'iPhone', 'iPad',
                'J2ME', 'MIDP', 'NetFront', 'Nokia', 'Opera Mini', 'Opera Mobi', 'PalmOS', 'PalmSource',
                'portalmmm', 'Plucker', 'ReqwirelessWeb', 'SonyEricsson', 'Symbian', 'UP\\.Browser',
                'webOS', 'Windows CE', 'Windows Phone OS', 'Xiino'
            )),
        'requested' => array('param' => 'requested', 'value' => 1)
    );

    /**
     * Request Uri.
     *
     * @var string
     */
    protected $requestUri;

    /**
     * Body Content
     *
     * @var string
     */
    protected $content;

    /**
     * Erlaubte Sprachen.
     *
     * @var array
     */
    protected $languages;

    /**
     * Erlaubte Charsets
     *
     * @var array
     */
    protected $charsets;

    /**
     * Erlaubte Content Typen.
     *
     * @var array
     */
    protected $contentTypes;

    /**
     * Request Path Info
     *
     * @var string
     */
    protected $pathInfo;

    /**
     * Request Basis URL.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Request Basis Pfad.
     *
     * @var string
     */
    protected $basePath;

    /**
     * @var string
     */
    protected $method;

    /**
     * Request Methode (GET, POST ...)
     *
     * @var string
     */
    protected $format;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $defaultLocale = 'de';

    /**
     * HTTP request Formate
     *
     * @var array
     */
    protected static $formats = array();

    /**
     * Nutzt $_GET, $_POST, $_FILES und $_COOKIE zum Initialisieren der Request Klasse.
     *
     * @return Panda\Http\Request
     */
    public static function createInstance()
    {
        $request = new static($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);
        if ($request->headers->has('CONTENT_TYPE')) {
            if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded') && in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))
            ) {
                parse_str($request->getContent(), $data);
                $request->data = $data;
            }
        }

        return $request;
    }

    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        $this->server = new Bag\ServerBag($server);
        $this->headers = new Bag\HeaderBag($this->server->getHeaders());
        $this->data = $request;
        $this->query = new Bag\ParameterBag($query);
        $this->attributes = new Bag\ParameterBag($attributes);
        $this->cookies = new Bag\ParameterBag($cookies);
        $this->files = new Bag\FileBag($files);

        $this->content = $content;
        $this->languages = null;
        $this->charsets = null;
        $this->acceptableContentTypes = null;
        $this->pathInfo = null;
        $this->requestUri = null;
        $this->baseUrl = null;
        $this->basePath = null;
        $this->method = null;
        $this->format = null;
    }

    /* -------------------------------------
     * Getter Funktionen
     */

    /**
     * Gibt die Request Uri ohne Basis Pfad und Query-String zurück.
     *
     * Example: /controller/action
     *
     * @return string
     */
    public function getRequestUrl()
    {
        $uri = $this->getRequestUri();
        $base = $this->getBaseUrl();

        if (strlen($base) > 0 && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }
        if (strpos($uri, '?') !== false) {
            list($uri) = explode('?', $uri, 2);
        }
        if (empty($uri) || $uri == '/' || $uri == '//') {
            return '/';
        }
        return $uri;
    }

    /**
     * Gibt eine Liste mit erlaubten Proxy-Servern zurück.
     *
     * @return array Array mit erlaubten Proxy-Servern
     */
    public static function getTrustedProxies()
    {
        return self::$trustedProxies;
    }

    /**
     * Gibt Allgemeine Request Informationen zurück.
     *
     * Durchsucht $request, $attributes und $query Informationen
     *
     * @param string $key
     * @param string $default
     * @return type
     */
    public function get($key, $default = null)
    {
        $value = null;
        if (isset($this->request[$key])) {
            $value = $this->request[$key];
        } elseif ($this->attributes->has($key)) {
            $value = $this->attributes->get($default);
        } elseif ($this->query->has($key)) {
            $value = $this->query->get($key);
        }
        return $value;
    }

    /**
     * Gibt die Session Informationen zurück.
     *
     * @return type
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Gibt die Client IP zurück.
     *
     * @param boolean $toLong Client IP als Long IP ausgeben?
     *
     * @return string
     */
    public function getClientIp()
    {
        $ip = $this->server->get('REMOTE_ADDR');

        if (!self::$trustProxy) {
            return $ip;
        }

        if (!self::$trustedHeaders[self::HEADER_CLIENT_IP] || !$this->headers->has(self::$trustedHeaders[self::HEADER_CLIENT_IP])) {
            return $ip;
        }

        $clientIps = array_map('trim', explode(',', $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_IP])));
        $clientIps[] = $ip;

        $trustedProxies = self::$trustProxy && !self::$trustedProxies ? array($ip) : self::$trustedProxies;
        $clientIps = array_diff($clientIps, $trustedProxies);

        return array_pop($clientIps);
    }

    /**
     * Gibt dne Aktuellen Scriptnamen zurück.
     *
     * @return string
     */
    public function getScriptName()
    {
        return $this->server->get('SCRIPT_NAME', $this->server->get('ORIG_SCRIPT_NAME', ''));
    }

    /**
     * Gibt den Request Body Inhalt zurück.
     *
     * @param boolean $fromeInput Inalt aus php://input lesen?
     * @return string
     * @throws LogicException
     */
    public function getContent($fromInput = false)
    {
        if (false === $this->content || (true === $asResource && null !== $this->content)) {
            throw new LogicException('getContent() can only be called once when using the resource return type.');
        }

        if (true === $fromInput) {
            $this->content = false;

            return fopen('php://input', 'rb');
        }

        if (null === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    /**
     * Gibt den Referer zurück.
     *
     * @param boolean $local Ohne Hostname?
     * @return string
     */
    public function getReferer($local = false)
    {
        $ref = env('HTTP_REFERER');
        if ($this->trustProxy && env('HTTP_X_FORWARDED_HOST')) {
            $ref = env('HTTP_X_FORWARDED_HOST');
        }

        $base = '';
        if (defined('FULL_BASE_URL')) {
            $base = FULL_BASE_URL . $this->webroot;
        }
        if (!empty($ref) && !empty($base)) {
            if ($local && strpos($ref, $base) === 0) {
                $ref = substr($ref, strlen($base));
                if ($ref[0] != '/') {
                    $ref = '/' . $ref;
                }
                return $ref;
            } elseif (!$local) {
                return $ref;
            }
        }
        return '/';
    }

    /**
     * Gibt die Request Methode zurück.
     *
     * @return string
     */
    public function getMethod()
    {
        if (null === $this->method) {
            $this->method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));

            if ('POST' === $this->method) {
                if ($this->headers->has('X-HTTP-METHOD-OVERRIDE')) {
                    $this->method = strtoupper($this->headers->get('X-HTTP-METHOD-OVERRIDE'));
                } elseif (self::$httpMethodParameterOverride) {
                    $this->method = strtoupper($this->request['_method'] = $this->query->get('_method', 'POST'));
                }
            }
        }

        return $this->method;
    }

    /**
     * Gibt den Request MimeType zurück.
     *
     * @param string $format Mime Format
     * @return string|boolean
     */
    public function getMimeType($format)
    {
        if (null === static::$formats) {
            static::_initializeFormats();
        }

        return isset(static::$formats[$format]) ? static::$formats[$format][0] : null;
    }

    /**
     * Gibt den Format des MimeTypes zurück
     * @param string $mimeType
     * @return string|null
     */
    public function getMimeFormat($mimeType)
    {
        if (false !== $pos = strpos($mimeType, ';')) {
            $mimeType = substr($mimeType, 0, $pos);
        }

        if (null === static::$formats) {
            static::initializeFormats();
        }

        foreach (static::$formats as $format => $mimeTypes) {
            if (in_array($mimeType, (array) $mimeTypes)) {
                return $format;
            }
        }
        return null;
    }

    /**
     * Gibt das Request Format zurück.
     *
     * @param string $default
     * @return string
     */
    public function getRequestFormat($default = 'html')
    {
        if (null === $this->format) {
            $this->format = $this->get('_format', $default);
        }

        return $this->format;
    }

    /**
     * Gibt den Content Type des Requests zurück.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->getMimeFormat($this->headers->get('CONTENT_TYPE'));
    }

    /**
     * Gibt die Aktuelle Locale zurück..
     *
     * @return string
     */
    public function getLocale()
    {
        return null === $this->locale ? $this->defaultLocale : $this->locale;
    }

    /**
     * Gibt die Etags zurück.
     *
     * @return array ETags
     */
    public function getETags()
    {
        return preg_split('/\s*,\s*/', $this->headers->get('if_none_match'), null, PREG_SPLIT_NO_EMPTY);
    }

    public function getPreferredLanguage(array $locales = null)
    {
        $preferredLanguages = $this->getLanguages();

        if (empty($locales)) {
            return isset($preferredLanguages[0]) ? $preferredLanguages[0] : null;
        }

        if (!$preferredLanguages) {
            return $locales[0];
        }

        $preferredLanguages = array_values(array_intersect($preferredLanguages, $locales));

        return isset($preferredLanguages[0]) ? $preferredLanguages[0] : $locales[0];
    }

    public function getLanguages()
    {
        if (null !== $this->languages) {
            return $this->languages;
        }

        $languages = AcceptHeader::fromString($this->headers->get('Accept-Language'))->all();
        $this->languages = array();
        foreach (array_keys($languages) as $lang) {
            if (strstr($lang, '-')) {
                $codes = explode('-', $lang);
                if ($codes[0] == 'i') {
                    if (count($codes) > 1) {
                        $lang = $codes[1];
                    }
                } else {
                    for ($i = 0, $max = count($codes); $i < $max; $i++) {
                        if ($i == 0) {
                            $lang = strtolower($codes[0]);
                        } else {
                            $lang .= '_' . strtoupper($codes[$i]);
                        }
                    }
                }
            }

            $this->languages[] = $lang;
        }

        return $this->languages;
    }

    public function getCharsets()
    {
        if (null !== $this->charsets) {
            return $this->charsets;
        }

        return $this->charsets = array_keys(AcceptHeader::fromString($this->headers->get('Accept-Charset'))->all());
    }

    public function getAcceptableContentTypes()
    {
        if (null !== $this->acceptableContentTypes) {
            return $this->acceptableContentTypes;
        }
        return $this->acceptableContentTypes = array_keys(AcceptHeader::fromString($this->headers->get('Accept'))->all());
    }

    public function data($key, $value = null)
    {
        if ($value !== null) {
            $this->data = Hash::insert($this->data, $key, $value);
            return $this;
        }
        return Hash::get($this->data, $key);
    }

    /**
     * Gibt den Request Pfad zurück ohne Domain.
     *
     * http://example.tld/mysite              gibt '' zurück
     * http://example.tld/mysite/about        gibt '/about' zurück
     * http://example.tld/mysite/about?var=1  gibt '/about' zurück
     *
     * @return string Request Pfad i.e. /about aus der Url http://example.tld/mysite/about
     */
    public function getPathInfo()
    {
        if (null === $this->pathInfo) {
            $this->pathInfo = $this->_preparePathInfo();
        }

        return $this->pathInfo;
    }

    /**
     * Gibt das Root Verzeichnnis des Requests zurück.
     * Ohne Domain und PathInfos.
     *
     * http://example.tld/index.php         gibt '' zurück
     * http://example.tld/index.php/page    gibt '' zurück
     * http://example.tld/web/index.php     gibt '/web' zurück
     *
     * @return string Basis Verzeichnis i.e. /web aus der Url http://example.tld/web/index.php
     */
    public function getBasePath()
    {
        if (null === $this->basePath) {
            $this->basePath = $this->_prepareBasePath();
        }

        return $this->basePath;
    }

    /**
     * Gibt die Basis Request URL zurück.
     *
     * @return string i.e. example.tld
     */
    public function getBaseUrl()
    {
        if (null === $this->baseUrl) {
            $this->baseUrl = $this->_prepareBaseUrl();
        }

        return $this->baseUrl;
    }

    /**
     * Gibt das Request Scheme zurück.
     * @return string https oder http
     */
    public function getScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * Gibt den verwendeten Port des Requests zurück.
     *
     * @return int
     */
    public function getPort()
    {
        if (self::$trustProxy && self::$trustedHeaders[self::HEADER_CLIENT_PORT] && $port = $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_PORT])) {
            return $port;
        }

        return $this->server->get('SERVER_PORT');
    }

    /**
     * Gibt den PHP_AUTH Usernamen zurück.
     *
     * @return string|null
     */
    public function getUser()
    {
        return $this->server->get('PHP_AUTH_USER');
    }

    /**
     * Gibt das PHP_AUTH Password zurück.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->server->get('PHP_AUTH_PW');
    }

    /**
     * Gibt die PHP_AUTH User Infos zurück.
     *
     * @return string PHP_AUTH Username + Password
     */
    public function getUserInfo()
    {
        $userinfo = $this->getUser();

        $pass = $this->getPassword();
        if ('' != $pass) {
            $userinfo .= ":$pass";
        }

        return $userinfo;
    }

    /**
     * Gibt dne Hostnamen zurück.
     *
     * @return string
     */
    public function getHttpHost()
    {
        $scheme = $this->getScheme();
        $port = $this->getPort();

        if (('http' == $scheme && $port == 80) || ('https' == $scheme && $port == 443)) {
            return $this->getHost();
        }

        return $this->getHost() . ':' . $port;
    }

    /**
     * Gibt die Request Uri zurück.
     *
     * @return string
     */
    public function getRequestUri()
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->_prepareRequestUri();
        }

        return $this->requestUri;
    }

    /**
     * Gibt das Request Scheme inkl Hostname zurück.
     *
     * @return string
     */
    public function getSchemeAndHttpHost()
    {
        return $this->getScheme() . '://' . $this->getHttpHost();
    }

    /**
     * Gibt die Request Uri zurück.
     *
     * @return string
     */
    public function getUri()
    {
        if (null !== $qs = $this->getQueryString()) {
            $qs = '?' . $qs;
        }

        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $this->getPathInfo() . $qs;
    }

    /**
     * Gibt den Übergebenen Pfad inkl. Request Scheme und BaseUrl zurück.
     *
     * @param string $path
     * @return string
     */
    public function getUriForPath($path)
    {
        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $path;
    }

    /**
     * Gibt den Hostnamen zurück.
     *
     * @return string
     * @throws UnexpectedValueException
     */
    public function getHost()
    {
        if (self::$trustProxy && self::$trustedHeaders[self::HEADER_CLIENT_HOST] && $host = $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_HOST])) {
            $elements = explode(',', $host);

            $host = $elements[count($elements) - 1];
        } elseif (!$host = $this->headers->get('HOST')) {
            if (!$host = $this->server->get('SERVER_NAME')) {
                $host = $this->server->get('SERVER_ADDR', '');
            }
        }

        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));
        if ($host && !preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $host)) {
            throw new UnexpectedValueException('Invalid Host');
        }

        return $host;
    }

    /**
     * Gibt den Request Query String zurück.
     *
     * @return string|null
     */
    public function getQueryString()
    {
        $qs = static::normalizeQueryString($this->server->get('QUERY_STRING'));

        return '' === $qs ? null : $qs;
    }

    /* -------------------------------------
     * Setter Funktionen
     */

    /**
     * Setzt die Erlaubten Proxy-Server.
     *
     * @param array $proxies Liste mit erlaubten Proxy-Servern
     */
    public static function setTrustedProxies(array $proxies)
    {
        self::$trustedProxies = $proxies;
        self::$trustProxy = $proxies ? true : false;
    }

    /**
     * Fügt dem Request einen Erlaubten Header hinzu.
     *
     * @param string $key Header Name
     * @param string $value Header Inhalt
     * @throws InvalidArgumentException
     */
    public static function setTrustedHeaderName($key, $value)
    {
        if (!array_key_exists($key, self::$trustedHeaders)) {
            throw new InvalidArgumentException(sprintf('Unable to set the trusted header name for key "%s".', $key));
        }

        self::$trustedHeaders[$key] = $value;
    }

    /**
     * Erlauben des Überschreiben von Header Informationen.
     */
    public static function enableHttpMethodParameterOverride()
    {
        self::$httpMethodParameterOverride = true;
    }

    /**
     * Setzen einer Session.
     *
     * @param \Panda\Http\SessionInterface $session
     */
    public function setSession(Interfaces\Session $session)
    {
        $this->session = $session;
    }

    /**
     * Setzt ein Format für den MimeType.
     *
     * @param type $format
     * @param type $mimeTypes
     */
    public function setFormat($format, $mimeTypes)
    {
        if (null === static::$formats) {
            static::initializeFormats();
        }

        static::$formats[$format] = is_array($mimeTypes) ? $mimeTypes : array($mimeTypes);
    }

    /**
     * Setzten der Standart Locale.
     *
     * @param string $locale
     */
    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;

        if (null === $this->locale) {
            $this->_setPhpDefaultLocale($locale);
        }
    }

    /**
     * Setzten der Locale.
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->_setPhpDefaultLocale($this->locale = $locale);
    }

    /**
     * Setzen des Request Formats.
     *
     * @param string $format
     */
    public function setRequestFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Setzen der Request Method.
     *
     * @param string $method POST, GET usw
     */
    public function setMethod($method)
    {
        $this->method = null;
        $this->server->set('REQUEST_METHOD', $method);
    }

    /**
     * Fügt $params den Aktuellen Params hinzu.
     *
     * @param array $params
     * @return void \Panda\Http\Request
     */
    public function addParams($params)
    {
        $this->params = array_merge($this->params, (array) $params);
        return $this;
    }

    /* -------------------------------------
     * Isset Funktionen
     */

    /**
     * Prüfung, Enthält der Aktuelle Request Session Informationen
     * aus einem Vorherigen Request?
     *
     * @return boolean
     */
    public function hasPreviousSession()
    {
        // the check for $this->session avoids malicious users trying to fake a session cookie with proper name
        return $this->hasSession() && $this->cookies->has($this->session->getName());
    }

    /**
     * Prüfung, eine Session vorhanden?
     *
     * @return boolean
     */
    public function hasSession()
    {
        return null !== $this->session;
    }

    /**
     * Prüfung, Request Methode sicher?
     *
     * @return boolean
     */
    public function isMethodSafe()
    {
        return in_array($this->getMethod(), array('GET', 'HEAD'));
    }

    /**
     * Prüfung, ob die Aktuelle Request Method = $method ist.
     *
     * @param string $method GET, POST usw
     * @return boolean
     */
    public function isMethod($method)
    {
        return $this->getMethod() === strtoupper($method);
    }

    /**
     * Prüft ob Request Contnet Type $type Erlaubt ist.
     *
     * @param string $type
     * @return boolean
     */
    public function accepts($type = null)
    {
        $accepts = explode(",", $this->headers->get('Accept'));
        foreach ($accepts as $accept) {
            if ($this->getFormat($accept) === $type) {
                return true;
            }
        }
        return false;
    }

    /**
     * no-cache Aktive?
     *
     * @return Boolean
     */
    public function isNoCache()
    {
        return $this->headers->hasCacheControlDirective('no-cache') || 'no-cache' == $this->headers->get('Pragma');
    }

    public function isXmlHttpRequest()
    {
        return 'XMLHttpRequest' == $this->headers->get('X-Requested-With');
    }

    /**
     * Prüfung, auf sichere Verbindung (https)
     *
     * @return boolean
     */
    public function isSecure()
    {
        if (self::$trustProxy && self::$trustedHeaders[self::HEADER_CLIENT_PROTO] && $proto = $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_PROTO])) {
            return in_array(strtolower($proto), array('https', 'on', '1'));
        }
        if (!$this->server->has('HTTPS')) {
            return false;
        }
        return 'on' == strtolower($this->server->get('HTTPS')) || 1 == $this->server->get('HTTPS');
    }

    /**
     * Prüfung, Request Methode = $type?
     *
     * @param string $type
     * @return boolean
     */
    public function is($type)
    {
        $type = strtolower($type);
        if (!isset($this->_detectors[$type])) {
            return false;
        }
        $detect = $this->_detectors[$type];
        if (isset($detect['env'])) {
            if (isset($detect['value'])) {
                if ($this->server->has($detect['env'])) {
                    return $this->server->get($detect['env']) == $detect['value'];
                }
            }
            if (isset($detect['pattern'])) {
                return (bool) preg_match($detect['pattern'], env($detect['env']));
            }
            if (isset($detect['options'])) {
                $pattern = '/' . implode('|', $detect['options']) . '/i';
                return (bool) preg_match($pattern, env($detect['env']));
            }
        }
        if (isset($detect['param'])) {
            $key = $detect['param'];
            $value = $detect['value'];
            return isset($this->params[$key]) ? $this->params[$key] == $value : false;
        }
        if (isset($detect['callback']) && is_callable($detect['callback'])) {
            return call_user_func($detect['callback'], $this);
        }
        return false;
    }

    public function onlyAllow($methods)
    {
        if (!is_array($methods)) {
            $methods = func_get_args();
        }
        foreach ($methods as $method) {
            if ($this->is($method)) {
                return true;
            }
        }
        $allowed = strtoupper(implode(', ', $methods));
        $e = new MethodNotAllowedException();
        $e->responseHeader('Allow', $allowed);
        throw $e;
    }

    /* -------------------------------------
     * Helper Funktionen
     */

    /**
     * Normalisiert den übergebenen Query-String.
     *
     * @param string $qs
     * @return string
     */
    public static function normalizeQueryString($qs)
    {
        if ('' == $qs) {
            return '';
        }

        $parts = array();
        $order = array();

        foreach (explode('&', $qs) as $param) {
            if ('' === $param || '=' === $param[0]) {
                continue;
            }

            $keyValuePair = explode('=', $param, 2);
            $parts[] = isset($keyValuePair[1]) ?
                rawurlencode(urldecode($keyValuePair[0])) . '=' . rawurlencode(urldecode($keyValuePair[1])) :
                rawurlencode(urldecode($keyValuePair[0]));
            $order[] = urldecode($keyValuePair[0]);
        }

        array_multisort($order, SORT_ASC, $parts);

        return implode('&', $parts);
    }

    /* -------------------------------------
     * Protected Funktionen
     */

    protected function _formatGet($query)
    {
        $url = $this->getRequestUrl();
        unset($query['/' . str_replace('.', '_', urldecode($url))]);
        if (strpos($url, '?') !== false) {
            list(, $querystr) = explode('?', $url);
            parse_str($querystr, $queryArgs);
            $query += $queryArgs;
        }
        return $query;
    }

    protected function _setPhpDefaultLocale($locale)
    {
        // if either the class Locale doesn't exist, or an exception is thrown when
        // setting the default locale, the intl module is not installed, and
        // the call can be ignored:
        try {
            if (class_exists('Locale', false)) {
                Locale::setDefault($locale);
            }
        } catch (\Exception $e) {

        }
    }

    /**
     * Bildet die Request Uri.
     *
     * @return string
     */
    protected function _prepareRequestUri()
    {
        $requestUri = '';
        if ($this->headers->has('X_ORIGINAL_URL') && false !== stripos(PHP_OS, 'WIN')) {
            $requestUri = $this->headers->get('X_ORIGINAL_URL');
        } elseif ($this->headers->has('X_REWRITE_URL') && false !== stripos(PHP_OS, 'WIN')) {
            $requestUri = $this->headers->get('X_REWRITE_URL');
        } elseif ($this->server->has('IIS_WasUrlRewritten') && $this->server->get('IIS_WasUrlRewritten') == '1' && $this->server->get('UNENCODED_URL') != '') {
            $requestUri = $this->server->get('UNENCODED_URL');
        } elseif ($this->server->has('REQUEST_URI')) {
            $requestUri = $this->server->get('REQUEST_URI');
            $schemeAndHttpHost = $this->getSchemeAndHttpHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
        } elseif ($this->server->has('ORIG_PATH_INFO')) {
            $requestUri = $this->server->get('ORIG_PATH_INFO');
            if ('' != $this->server->get('QUERY_STRING')) {
                $requestUri .= '?' . $this->server->get('QUERY_STRING');
            }
        }

        return $requestUri;
    }

    /**
     * Bildet die BaseUrl des Requests
     *
     * @return string
     */
    protected function _prepareBaseUrl()
    {
        $filename = basename($this->server->get('SCRIPT_FILENAME'));
        if (basename($this->server->get('SCRIPT_NAME')) === $filename) {
            $baseUrl = dirname($this->server->get('PHP_SELF'));
            if (basename($baseUrl) === 'public') {
                $baseUrl = dirname($baseUrl);
            }
        } elseif (basename($this->server->get('PHP_SELF')) === $filename) {
            $baseUrl = $this->server->get('PHP_SELF');
        } elseif (basename($this->server->get('ORIG_SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->server->get('ORIG_SCRIPT_NAME');
        } else {
            $path = $this->server->get('PHP_SELF', '');
            $file = $this->server->get('SCRIPT_FILENAME', '');
            $segs = explode('/', trim($file, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/' . $seg . $baseUrl;
                ++$index;
            } while (($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos));
        }

        $requestUri = $this->getRequestUri();

        if ($baseUrl && false !== $prefix = $this->_getUrlencodedPrefix($requestUri, $baseUrl)) {
            return $prefix;
        }

        if ($baseUrl && false !== $prefix = $this->_getUrlencodedPrefix($requestUri, dirname($baseUrl))) {
            return rtrim($prefix, '/');
        }

        $truncatedRequestUri = $requestUri;
        if (($pos = strpos($requestUri, '?')) !== false) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
            return '';
        }

        if ((strlen($requestUri) >= strlen($baseUrl)) && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0))) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        return rtrim($baseUrl, '/');
    }

    /**
     * Bilden den Request BasePath.
     *
     * @return string base path
     */
    protected function prepareBasePath()
    {
        $filename = basename($this->server->get('SCRIPT_FILENAME'));
        $baseUrl = $this->getBaseUrl();
        if (empty($baseUrl)) {
            return '';
        }

        if (basename($baseUrl) === $filename) {
            $basePath = dirname($baseUrl);
        } else {
            $basePath = $baseUrl;
        }

        if ('\\' === DIRECTORY_SEPARATOR) {
            $basePath = str_replace('\\', '/', $basePath);
        }

        return rtrim($basePath, '/');
    }

    /**
     * Setzt die PathInfos zusammen.
     *
     * @return string path info
     */
    protected function _preparePathInfo()
    {
        $baseUrl = $this->getBaseUrl();
        if (null === ($requestUri = $this->getRequestUri())) {
            return '/';
        }

        $pathInfo = '/';
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if ((null !== $baseUrl) && (false === ($pathInfo = substr($requestUri, strlen($baseUrl))))) {
            return '/';
        } elseif (null === $baseUrl) {
            return $requestUri;
        }

        return (string) $pathInfo;
    }

    /**
     * Returns the prefix as encoded in the string when the string starts with
     * the given prefix, false otherwise.
     *
     * @param string $string The urlencoded string
     * @param string $prefix The prefix not encoded
     *
     * @return string|false The prefix as it is encoded in $string, or false
     */
    protected function _getUrlencodedPrefix($string, $prefix)
    {
        if (0 !== strpos(rawurldecode($string), $prefix)) {
            return false;
        }

        $len = strlen($prefix);

        if (preg_match("#^(%[[:xdigit:]]{2}|.){{$len}}#", $string, $match)) {
            return $match[0];
        }

        return false;
    }

    /**
     * Initialisiert die HTTP request Formate.
     */
    protected static function _initializeFormats()
    {
        static::$formats = array(
            'html' => array('text/html', 'application/xhtml+xml'),
            'txt' => array('text/plain'),
            'js' => array('application/javascript', 'application/x-javascript', 'text/javascript'),
            'css' => array('text/css'),
            'json' => array('application/json', 'application/x-json'),
            'xml' => array('text/xml', 'application/xml', 'application/x-xml'),
            'rdf' => array('application/rdf+xml'),
            'atom' => array('application/atom+xml'),
            'rss' => array('application/rss+xml'),
        );
    }

    /* -------------------------------------
     * Array Zugriff
     */

    /**
     * Array Zugriff auf Request Params, $this->query oder $this->data
     *
     * @param string $key Params Key | 'url' | 'date'
     * @return string|boolean
     */
    public function offsetGet($key)
    {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }
        if ($key == 'url') {
            return $this->query;
        }
        if ($key == 'data') {
            return $this->data;
        }
        return null;
    }

    /**
     * Array Set Zugriff auf Request Params.
     *
     * @param string $key Params Key
     * @param string $value Params Value
     */
    public function offsetSet($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * Array unset() Zugriff auf Request Params.
     *
     * @param string $key Params Key
     */
    public function offsetUnset($key)
    {
        unset($this->params[$key]);
    }

    /**
     * Array isset() Zugriff auf Request Params.
     *
     * @param string $key Params Key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return isset($this->params[$key]);
    }

    /* -------------------------------------
     * Magische Funktionen
     */

    /**
     * Get Zugriff auf $params.
     *
     * @param string $name
     * @return string|null
     */
    public function __get($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return null;
    }

    /**
     * isset() Zugriff auf $params.
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->params[$name]);
    }

}
