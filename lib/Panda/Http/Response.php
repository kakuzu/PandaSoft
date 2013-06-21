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

use Panda\Http\Request;
use Panda\Http\Bag\ResponseHeaderBag;

/**
 * Klasse Response
 *
 * @version ::VERSION::
 */
class Response {

    /**
     * Response Header Code Map.
     *
     * Statuscode => Beschreibung
     *
     * @var array
     */
    public static $statusTexts = array(
        /**
         * 1xx – Informationen
         */
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        /**
         * 2xx – Erfolgreiche Operation
         */
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        /**
         * 3xx – Umleitung
         */
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        /**
         * 4xx – Client-Fehler
         */
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Reserved for WebDAV advanced collections expired proposal',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        /**
         * 5xx – Server-Fehler
         */
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    );

    /**
     * MimeType Map.
     *
     * Kürzel => Bezeichnung
     *
     * @var array
     */
    protected $_mimeTypes = array(
        'ai' => 'application/postscript',
        'bcpio' => 'application/x-bcpio',
        'bin' => 'application/octet-stream',
        'ccad' => 'application/clariscad',
        'cdf' => 'application/x-netcdf',
        'class' => 'application/octet-stream',
        'cpio' => 'application/x-cpio',
        'cpt' => 'application/mac-compactpro',
        'csh' => 'application/x-csh',
        'csv' => array('text/csv', 'application/vnd.ms-excel', 'text/plain'),
        'dcr' => 'application/x-director',
        'dir' => 'application/x-director',
        'dms' => 'application/octet-stream',
        'doc' => 'application/msword',
        'drw' => 'application/drafting',
        'dvi' => 'application/x-dvi',
        'dwg' => 'application/acad',
        'dxf' => 'application/dxf',
        'dxr' => 'application/x-director',
        'eot' => 'application/vnd.ms-fontobject',
        'eps' => 'application/postscript',
        'exe' => 'application/octet-stream',
        'ez' => 'application/andrew-inset',
        'flv' => 'video/x-flv',
        'gtar' => 'application/x-gtar',
        'gz' => 'application/x-gzip',
        'bz2' => 'application/x-bzip',
        '7z' => 'application/x-7z-compressed',
        'hdf' => 'application/x-hdf',
        'hqx' => 'application/mac-binhex40',
        'ico' => 'image/vnd.microsoft.icon',
        'ips' => 'application/x-ipscript',
        'ipx' => 'application/x-ipix',
        'js' => 'text/javascript',
        'latex' => 'application/x-latex',
        'lha' => 'application/octet-stream',
        'lsp' => 'application/x-lisp',
        'lzh' => 'application/octet-stream',
        'man' => 'application/x-troff-man',
        'me' => 'application/x-troff-me',
        'mif' => 'application/vnd.mif',
        'ms' => 'application/x-troff-ms',
        'nc' => 'application/x-netcdf',
        'oda' => 'application/oda',
        'otf' => 'font/otf',
        'pdf' => 'application/pdf',
        'pgn' => 'application/x-chess-pgn',
        'pot' => 'application/mspowerpoint',
        'pps' => 'application/mspowerpoint',
        'ppt' => 'application/mspowerpoint',
        'ppz' => 'application/mspowerpoint',
        'pre' => 'application/x-freelance',
        'prt' => 'application/pro_eng',
        'ps' => 'application/postscript',
        'roff' => 'application/x-troff',
        'scm' => 'application/x-lotusscreencam',
        'set' => 'application/set',
        'sh' => 'application/x-sh',
        'shar' => 'application/x-shar',
        'sit' => 'application/x-stuffit',
        'skd' => 'application/x-koan',
        'skm' => 'application/x-koan',
        'skp' => 'application/x-koan',
        'skt' => 'application/x-koan',
        'smi' => 'application/smil',
        'smil' => 'application/smil',
        'sol' => 'application/solids',
        'spl' => 'application/x-futuresplash',
        'src' => 'application/x-wais-source',
        'step' => 'application/STEP',
        'stl' => 'application/SLA',
        'stp' => 'application/STEP',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'swf' => 'application/x-shockwave-flash',
        't' => 'application/x-troff',
        'tar' => 'application/x-tar',
        'tcl' => 'application/x-tcl',
        'tex' => 'application/x-tex',
        'texi' => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'tr' => 'application/x-troff',
        'tsp' => 'application/dsptype',
        'ttf' => 'font/ttf',
        'unv' => 'application/i-deas',
        'ustar' => 'application/x-ustar',
        'vcd' => 'application/x-cdlink',
        'vda' => 'application/vda',
        'xlc' => 'application/vnd.ms-excel',
        'xll' => 'application/vnd.ms-excel',
        'xlm' => 'application/vnd.ms-excel',
        'xls' => 'application/vnd.ms-excel',
        'xlw' => 'application/vnd.ms-excel',
        'zip' => 'application/zip',
        'aif' => 'audio/x-aiff',
        'aifc' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'au' => 'audio/basic',
        'kar' => 'audio/midi',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'mp2' => 'audio/mpeg',
        'mp3' => 'audio/mpeg',
        'mpga' => 'audio/mpeg',
        'ogg' => 'audio/ogg',
        'ra' => 'audio/x-realaudio',
        'ram' => 'audio/x-pn-realaudio',
        'rm' => 'audio/x-pn-realaudio',
        'rpm' => 'audio/x-pn-realaudio-plugin',
        'snd' => 'audio/basic',
        'tsi' => 'audio/TSP-audio',
        'wav' => 'audio/x-wav',
        'asc' => 'text/plain',
        'c' => 'text/plain',
        'cc' => 'text/plain',
        'css' => 'text/css',
        'etx' => 'text/x-setext',
        'f' => 'text/plain',
        'f90' => 'text/plain',
        'h' => 'text/plain',
        'hh' => 'text/plain',
        'html' => array('text/html', '*/*'),
        'htm' => array('text/html', '*/*'),
        'm' => 'text/plain',
        'rtf' => 'text/rtf',
        'rtx' => 'text/richtext',
        'sgm' => 'text/sgml',
        'sgml' => 'text/sgml',
        'tsv' => 'text/tab-separated-values',
        'tpl' => 'text/template',
        'txt' => 'text/plain',
        'text' => 'text/plain',
        'xml' => array('application/xml', 'text/xml'),
        'avi' => 'video/x-msvideo',
        'fli' => 'video/x-fli',
        'mov' => 'video/quicktime',
        'movie' => 'video/x-sgi-movie',
        'mpe' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'qt' => 'video/quicktime',
        'viv' => 'video/vnd.vivo',
        'vivo' => 'video/vnd.vivo',
        'gif' => 'image/gif',
        'ief' => 'image/ief',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'pbm' => 'image/x-portable-bitmap',
        'pgm' => 'image/x-portable-graymap',
        'png' => 'image/png',
        'pnm' => 'image/x-portable-anymap',
        'ppm' => 'image/x-portable-pixmap',
        'ras' => 'image/cmu-raster',
        'rgb' => 'image/x-rgb',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'xbm' => 'image/x-xbitmap',
        'xpm' => 'image/x-xpixmap',
        'xwd' => 'image/x-xwindowdump',
        'ice' => 'x-conference/x-cooltalk',
        'iges' => 'model/iges',
        'igs' => 'model/iges',
        'mesh' => 'model/mesh',
        'msh' => 'model/mesh',
        'silo' => 'model/mesh',
        'vrml' => 'model/vrml',
        'wrl' => 'model/vrml',
        'mime' => 'www/mime',
        'pdb' => 'chemical/x-pdb',
        'xyz' => 'chemical/x-pdb',
        'javascript' => 'text/javascript',
        'json' => 'application/json',
        'form' => 'application/x-www-form-urlencoded',
        'file' => 'multipart/form-data',
        'xhtml' => array('application/xhtml+xml', 'application/xhtml', 'text/xhtml'),
        'xhtml-mobile' => 'application/vnd.wap.xhtml+xml',
        'rss' => 'application/rss+xml',
        'atom' => 'application/atom+xml',
        'amf' => 'application/x-amf',
        'wap' => array('text/vnd.wap.wml', 'text/vnd.wap.wmlscript', 'image/vnd.wap.wbmp'),
        'wml' => 'text/vnd.wap.wml',
        'wmlscript' => 'text/vnd.wap.wmlscript',
        'wbmp' => 'image/vnd.wap.wbmp',
    );

    /**
     * Response Header Protocol.
     *
     * @var string
     */
    protected $protocol = 'HTTP/1.1';

    /**
     * Response Header Statuscode.
     *
     * @var integer
     */
    protected $statusCode = 200;

    /**
     * Response Header Statustext.
     *
     * @var string
     */
    protected $statusText;

    /**
     * Response Body Content Type.
     *
     * @var string
     */
    protected $_contentType = 'text/html';

    /**
     * Vom Client genutzte Cookies.
     *
     * @var array
     */
    protected $_cookies = array();

    /**
     * Object mit Header Informationen.
     *
     * @var ResponseHeaderBag
     */
    public $headers;

    /**
     * Response Body Content.
     *
     * @var string
     */
    protected $content;

    /**
     * Response Body Charset.
     *
     * @var string
     */
    protected $charset = 'UTF-8';

    /**
     * Cache Richtlinien
     *
     * @var array
     */
    protected $_cacheDirectives = array();

    /**
     * Constructor.
     *
     * @param string  $content Response Content
     * @param integer $status  Response Statuscode
     * @param array   $headers Array mit Headern
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->headers = new ResponseHeaderBag($headers);
        $this->setContent($content);
        $this->setStatusCode($status);
        if (!$this->headers->has('Date')) {
            $this->setDate(new \DateTime(null, new \DateTimeZone('UTC')));
        }
    }

    /**
     * Response Factory Methode.
     *
     * @param string  $content Response Content
     * @param integer $status  Response Statuscode
     * @param array   $headers Array mit Headern
     *
     * @return Response
     */
    public static function create($content = '', $status = 200, $headers = array())
    {
        return new static($content, $status, $headers);
    }

    /**
     * Bildet die Requestausgabe vor dem Senden zum Clienten.
     *
     * @param Request $request Request Instanz
     *
     * @return Response The current response.
     */
    public function prepare(Request $request)
    {
        if ($this->isInformational() || in_array($this->statusCode, array(204, 304))) {
            $this->setContent(null);
        }

        if (!$this->headers->has('Content-Type')) {
            $format = $request->getRequestFormat();
            if (null !== $format && $mimeType = $request->getMimeType($format)) {
                $this->headers->set('Content-Type', $mimeType);
            }
        }

        if (strpos($this->_contentType, 'text/') === 0) {
            $this->headers->set('Content-Type', "{$this->_contentType}; charset={$this->charset}");
        } else {
            $this->headers->set('Content-Type', "{$this->_contentType}");
        }

        if ($this->headers->has('Transfer-Encoding')) {
            $this->headers->remove('Content-Length');
        }

        if ($request->isMethod('HEAD')) {
            $length = $this->headers->get('Content-Length');
            $this->setContent(null);
            if ($length) {
                $this->headers->set('Content-Length', $length);
            }
        }

        if ('HTTP/1.0' != $request->server->get('SERVER_PROTOCOL')) {
            $this->setProtocolVersion('1.1');
        }

        return $this;
    }

    /**
     * Senden den Vollständigen Response zum Clienten. Inkl. Header, Content.
     *
     * @return \Panda\Http\Response
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendContent($this->content);

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif ('cli' !== PHP_SAPI) {
            $previous = null;
            $obStatus = ob_get_status(1);
            while (($level = ob_get_level()) > 0 && $level !== $previous) {
                $previous = $level;
                if ($obStatus[$level - 1] && isset($obStatus[$level - 1]['del']) && $obStatus[$level - 1]['del']) {
                    ob_end_flush();
                }
            }
            flush();
        }

        return $this;
    }

    /**
     * Sendet den HTTP Header.
     *
     * @return \Panda\Http\Response
     */
    protected function sendHeaders()
    {
        if (headers_sent()) {
            return $this;
        }

        header(sprintf('HTTP/%s %s %s', $this->protocol, $this->statusCode, $this->statusText));
        foreach ($this->headers as $header => $values) {
            foreach ($values as $value) {
                header("{$header}: {$value}", false);
            }
        }

        foreach ($this->headers->getCookies() as $cookie) {
            setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        }
    }

    /**
     * Sendet den HTTP Content.
     *
     * @param string $content HTTP Response Body
     * @return \Panda\Http\Response
     */
    protected function sendContent($content)
    {
        echo $content;
        return $this;
    }

    /* ----------------------------------------------------------
     * ----------------------------------------------------------
     * Getter Funktionen
     * ----------------------------------------------------------
     * --------------------------------------------------------- */

    /**
     * Gibt den HTTP Content zurück.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Gibt den HTTP Status Code zurück.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setzen oder Ausgeben des Content Types.
     *
     * @param string|null $contentType
     * @return string|boolean
     */
    public function type($contentType = null)
    {
        if (is_null($contentType)) {
            return $this->_contentType;
        }
        if (is_array($contentType)) {
            $type = key($contentType);
            $defitition = current($contentType);
            $this->_mimeTypes[$type] = $defitition;
            return $this->_contentType;
        }
        if (isset($this->_mimeTypes[$contentType])) {
            $contentType = $this->_mimeTypes[$contentType];
            $contentType = is_array($contentType) ? current($contentType) : $contentType;
        }
        if (strpos($contentType, '/') === false) {
            return false;
        }
        return $this->_contentType = $contentType;
    }

    /**
     * Gibt den verwendetetn MimeTpye zurück.
     *
     * @param string $alias
     * @return boolean
     */
    public function getMimeType($alias)
    {
        if (isset($this->_mimeTypes[$alias])) {
            return $this->_mimeTypes[$alias];
        }
        return false;
    }

    /**
     * Gibt das Header datum als DateTime Instanz zurück.
     *
     * @return object
     */
    public function getDate()
    {
        return $this->headers->getDate('Date', new \DateTime());
    }

    /**
     * Gibt den Alias eines Content-Types zurück.
     *
     * @param string|array $ctype Content Type
     * @return string|null Alias oder Null
     */
    public function getContentAlias($ctype)
    {
        if (is_array($ctype)) {
            return array_map(array($this, 'mapType'), $ctype);
        }

        foreach ($this->_mimeTypes as $alias => $types) {
            if (is_array($types) && in_array($ctype, $types)) {
                return $alias;
            } elseif (is_string($types) && $types == $ctype) {
                return $alias;
            }
        }
        return null;
    }

    /**
     * Gibt den Charset zurück.
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Gibt das Maximale Alter vom Cache-Control zurück.
     *
     * @return integer|boolean
     */
    public function getMaxAge()
    {
        if ($this->headers->hasCacheControlDirective('s-maxage')) {
            return (int) $this->headers->getCacheControlDirective('s-maxage');
        }

        if ($this->headers->hasCacheControlDirective('max-age')) {
            return (int) $this->headers->getCacheControlDirective('max-age');
        }

        if (null !== $this->getExpires()) {
            return $this->getExpires()->format('U') - $this->getDate()->format('U');
        }

        return null;
    }

    /**
     * Gibt das Alter des Response zurück.
     *
     * @return integer Alter in Sekunden
     */
    public function getAge()
    {
        if (null !== $age = $this->headers->get('Age')) {
            return (int) $age;
        }

        return max(time() - $this->getDate()->format('U'), 0);
    }

    /**
     * Gibt das Verfallsdatum des Headers als DateTime INstanz zurück.
     *
     * @return \DateTime|null
     */
    public function getExpires()
    {
        try {
            return $this->headers->getDate('Expires');
        } catch (\RuntimeException $e) {
            return \DateTime::createFromFormat(DATE_RFC2822, 'Sat, 01 Jan 00 00:00:00 +0000');
        }
    }

    /**
     * Gibt ein Array mit vorhandenen Vary Headern zurück.
     *
     * @return type
     */
    public function getVary()
    {
        if (!$vary = $this->headers->getItem('Vary')) {
            return array();
        }

        return is_array($vary) ? $vary : preg_split('/[\s,]+/', $vary);
    }

    /**
     * Gibt die Zeit der letzten Header änderung als DateTime Instanz zurück.
     *
     * @return \DateTime
     */
    public function getLastModified()
    {
        return $this->headers->getDate('Last-Modified');
    }

    public function getTtl()
    {
        if (null !== $maxAge = $this->getMaxAge()) {
            return $maxAge - $this->getAge();
        }

        return null;
    }

    public function getEtag()
    {
        return $this->headers->getItem('ETag');
    }

    /* ----------------------------------------------------------
     * ----------------------------------------------------------
     * Setter Funktionen
     * ----------------------------------------------------------
     * --------------------------------------------------------- */

    /**
     * Setzen des HTTP Contents.
     *
     * @param string $content HTTP Content
     *
     * @return \Panda\Http\Response
     * @throws UnexpectedValueException
     */
    public function setContent($content)
    {
        if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable(array($content, '__toString'))) {
            throw new UnexpectedValueException('The Response content must be a string or object implementing __toString(), "' . gettype($content) . '" given.');
        }

        $this->content = (string) $content;
        return $this;
    }

    /**
     * Setzen des HTTP Status Codes/Texts.
     *
     * @param integer $code HTTP Status Code
     * @param string $text HTTP Status Text
     *
     * @return \Panda\Http\Response
     * @throws InvalidArgumentException
     */
    public function setStatusCode($code, $text = null)
    {

        $this->statusCode = $code = (int) $code;

        if ($this->isInvalid()) {
            throw new InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
        }

        if (null === $text) {
            $this->statusText = isset(self::$statusTexts[$code]) ? self::$statusTexts[$code] : '';

            return $this;
        }

        if (false === $text) {
            $this->statusText = '';
            return $this;
        }

        $this->statusText = $text;

        return $this;
    }

    /**
     * setzt den Charset.
     *
     * @param string $charset
     * @return \Panda\Http\Response
     */
    public function setCharset($charset = null)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Setzen der Cache Informationen.
     *
     * Verfügbare Optionen:
     * etag, last_modified, max_age, s_maxage, private, und public.
     *
     * @param array $options
     * @return \Panda\Http\Response
     * @throws \InvalidArgumentException
     */
    public function setCache(array $options)
    {
        if ($diff = array_diff(array_keys($options), array('etag', 'last_modified', 'max_age', 's_maxage', 'private', 'public'))) {
            throw new \InvalidArgumentException(sprintf('Response does not support the following options: "%s".', implode('", "', array_values($diff))));
        }

        if (isset($options['etag'])) {
            $this->setEtag($options['etag']);
        }

        if (isset($options['last_modified'])) {
            $this->setLastModified($options['last_modified']);
        }

        if (isset($options['max_age'])) {
            $this->setMaxAge($options['max_age']);
        }

        if (isset($options['s_maxage'])) {
            $this->setSharedMaxAge($options['s_maxage']);
        }

        if (isset($options['public'])) {
            if ($options['public']) {
                $this->setPublic();
            } else {
                $this->setPrivate();
            }
        }

        if (isset($options['private'])) {
            if ($options['private']) {
                $this->setPrivate();
            } else {
                $this->setPublic();
            }
        }

        return $this;
    }

    /**
     * Setzt die Header Zeitangaben.
     *
     * @param \DateTime $date
     * @return \Panda\Http\Response
     */
    public function setDate(\DateTime $date)
    {
        $date->setTimezone(new \DateTimeZone('UTC'));
        $this->headers->set('Date', $date->format('D, d M Y H:i:s') . ' GMT');

        return $this;
    }

    /**
     * Setzt das Maxiamle alter vom Cache-Control.
     *
     * @param integer $value
     * @return \Panda\Http\Response
     */
    public function setMaxAge($value)
    {
        $this->headers->addCacheControlDirective('max-age', $value);

        return $this;
    }

    /**
     * Setzen der Cache-Control s-maxage Anweisung.
     *
     * @param integer $value
     *
     * @return Response
     */
    public function setSharedMaxAge($value)
    {
        $this->setPublic();
        $this->headers->addCacheControlDirective('s-maxage', $value);

        return $this;
    }

    /**
     * Makiert den Respons Header als Abgelaufen.
     *
     * @return \Panda\Http\Response
     */
    public function expire()
    {
        if ($this->isFresh()) {
            $this->headers->set('Age', $this->getMaxAge());
        }

        return $this;
    }

    /**
     * Setzt das Verfallsdatum des Headers.
     *
     * @param \DateTime $date
     * @return \Panda\Http\Response
     */
    public function setExpires(\DateTime $date = null)
    {
        if (null === $date) {
            $this->headers->remove('Expires');
        } else {
            $date = clone $date;
            $date->setTimezone(new \DateTimeZone('UTC'));
            $this->headers->set('Expires', $date->format('D, d M Y H:i:s') . ' GMT');
        }

        return $this;
    }

    /**
     * Setzt einen Vary Header.
     *
     * @param string|array $headers
     * @param boolean $replace Vorhandenen Header überschreiben?
     * @return \Panda\Http\Response
     */
    public function setVary($headers, $replace = true)
    {
        $this->headers->set('Vary', $headers, $replace);

        return $this;
    }

    /**
     * Aktiviert die GZip Komprimierung.
     *
     * @return boolean
     */
    public function compress()
    {
        $compressionEnabled = ini_get("zlib.output_compression") !== '1' &&
            extension_loaded("zlib") &&
            (strpos(env('HTTP_ACCEPT_ENCODING'), 'gzip') !== false);
        return $compressionEnabled && ob_start('ob_gzhandler');
    }

    /**
     * Setzt den Passenden Respons Header zum Donwloadenden des Respons als Datei.
     *
     * @param string $filename
     */
    public function download($filename)
    {
        $this->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Setzt die Länge des HTTP header Contents.
     *
     * @param integer $bytes
     * @return \Panda\Http\Response
     */
    public function setLength($bytes = null)
    {
        if ($bytes !== null) {
            $this->headers->set('Content-Length', $bytes);
        }
        return $this;
    }

    /**
     * Setzt den Headerstatus auf 304 und entfernt alle nicht 304 Header.
     *
     * @return \Panda\Http\Response
     */
    public function setNotModified()
    {
        $this->setStatusCode(304);
        $this->setContent(null);

        foreach (array('Allow', 'Content-Encoding', 'Content-Language', 'Content-Length', 'Content-MD5', 'Content-Type', 'Last-Modified') as $header) {
            $this->headers->remove($header);
        }

        return $this;
    }

    public function setModified(\DateTime $date = null)
    {
        if (null === $date) {
            $this->headers->remove('Last-Modified');
        } else {
            $date = clone $date;
            $date->setTimezone(new \DateTimeZone('UTC'));
            $this->headers->set('Last-Modified', $date->format('D, d M Y H:i:s') . ' GMT');
        }

        return $this;
    }

    public function notModified()
    {
        $this->setStatusCode(304);
        $this->getContent('');
        $remove = array(
            'Allow',
            'Content-Encoding',
            'Content-Language',
            'Content-Length',
            'Content-MD5',
            'Content-Type',
            'Last-Modified'
        );
        foreach ($remove as $header) {
            $this->headers->remove($header);
        }
    }

    public function setTtl($seconds)
    {
        $this->setSharedMaxAge($this->getAge() + $seconds);

        return $this;
    }

    public function setClientTtl($seconds)
    {
        $this->setMaxAge($this->getAge() + $seconds);

        return $this;
    }

    public function setEtag($etag = null, $weak = false)
    {
        if (null === $etag) {
            $this->headers->remove('Etag');
        } else {
            if (0 !== strpos($etag, '"')) {
                $etag = '"' . $etag . '"';
            }

            $this->headers->set('ETag', (true === $weak ? 'W/' : '') . $etag);
        }

        return $this;
    }

    public function setPrivate()
    {
        $this->headers->removeCacheControlDirective('public');
        $this->headers->addCacheControlDirective('private');

        return $this;
    }

    public function setPublic()
    {
        $this->headers->addCacheControlDirective('public');
        $this->headers->removeCacheControlDirective('private');

        return $this;
    }

    /* ----------------------------------------------------------
     * ----------------------------------------------------------
     * Prüf Funktionen (isset, is, has)
     * ----------------------------------------------------------
     * --------------------------------------------------------- */

    /**
     * Gibt True zurück, sollte der Content Cachebar sein.
     *
     * @return boolean
     */
    public function isCacheable()
    {
        if (!in_array($this->statusCode, array(200, 203, 300, 301, 302, 404, 410))) {
            return false;
        }

        if ($this->headers->hasCacheControlDirective('no-store') || $this->headers->getCacheControlDirective('private')) {
            return false;
        }

        return $this->isValidateable() || $this->isFresh();
    }

    /**
     *
     * @param boolean $public
     * @param int $time
     * @return boolean
     */
    public function sharable($public = null, $time = null)
    {
        if ($public === null) {
            $public = array_key_exists('public', $this->_cacheDirectives);
            $private = array_key_exists('private', $this->_cacheDirectives);
            $noCache = array_key_exists('no-cache', $this->_cacheDirectives);
            if (!$public && !$private && !$noCache) {
                return null;
            }
            $sharable = $public || !($private || $noCache);
            return $sharable;
        }
        if ($public) {
            $this->_cacheDirectives['public'] = true;
            unset($this->_cacheDirectives['private']);
            $this->setSharedMaxAge($time);
        } else {
            $this->_cacheDirectives['private'] = true;
            unset($this->_cacheDirectives['public']);
            $this->setMaxAge($time);
        }
        if ($time == null) {
            $this->_setCacheControl();
        }
        return (bool) $public;
    }

    /**
     * Prüfung, ob es sich um einen Vary Header handelt.
     *
     * @return boolean
     */
    public function hasVary()
    {
        return null !== $this->headers->getItem('Vary');
    }

    /**
     * Prüfung, ob die Ausgabe Komprimiert wird.
     *
     * @return boolean
     */
    public function isOutputCompressed()
    {
        return strpos(env('HTTP_ACCEPT_ENCODING'), 'gzip') !== false && (ini_get("zlib.output_compression") === '1' || in_array('ob_gzhandler', ob_list_handlers()));
    }

    public function isNotModified(Request $request)
    {
        if (!$request->isMethodSafe()) {
            return false;
        }
        $lastModified = null;
        if ($request->headers->contains('If-Modified-Since')) {
            $lastModified = $request->headers->getItem('If-Modified-Since');
        }
        $notModified = false;
        if ($etags = $request->getEtags()) {
            $notModified = (in_array($this->getEtag(), $etags) || in_array('*', $etags)) && (!$lastModified || $this->headers->getItem('Last-Modified') == $lastModified);
        } elseif ($lastModified) {
            $notModified = $lastModified == $this->headers->getItem('Last-Modified');
        }

        if ($notModified) {
            $this->setNotModified();
        }

        return $notModified;
    }

    public function isFresh()
    {
        return $this->getTtl() > 0;
    }

    public function isValidateable()
    {
        return $this->headers->contains('Last-Modified') || $this->headers->contains('ETag');
    }

    public function mustRevalidate()
    {
        return $this->headers->hasCacheControlDirective('must-revalidate') || $this->headers->contains('proxy-revalidate');
    }

    public function isInvalid()
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    public function isInformational()
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    public function isSuccessful()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function isRedirection()
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    public function isClientError()
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    public function isServerError()
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    public function isOk()
    {
        return 200 === $this->statusCode;
    }

    public function isForbidden()
    {
        return 403 === $this->statusCode;
    }

    public function isNotFound()
    {
        return 404 === $this->statusCode;
    }

    public function isRedirect($location = null)
    {
        return in_array($this->statusCode, array(201, 301, 302, 303, 307, 308)) && (null === $location ? : $location == $this->headers->get('Location'));
    }

    public function isEmpty()
    {
        return in_array($this->statusCode, array(201, 204, 304));
    }

    /* ----------------------------------------------------------
     * ----------------------------------------------------------
     * Allgemeine Funktionen
     * ----------------------------------------------------------
     * --------------------------------------------------------- */

    /**
     * Deaktiviert das Caching.
     */
    public function disableCache()
    {
        $this->headers->set('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $this->headers->set('Last-Modified', gmdate("D, d M Y H:i:s") . " GMT");
        $this->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }

    /* ----------------------------------------------------------
     * ----------------------------------------------------------
     * Protected Funktionen
     * ----------------------------------------------------------
     * --------------------------------------------------------- */

    /**
     * Helper Funktion zum Setzen des Cache-Control Headers.
     */
    protected function _setCacheControl()
    {
        $control = '';
        foreach ($this->_cacheDirectives as $key => $val) {
            $control .= $val === true ? $key : sprintf('%s=%s', $key, $val);
            $control .= ', ';
        }
        $control = rtrim($control, ', ');
        $this->headers->set('Cache-Control', $control);
    }

    protected function _getUTCDate($time = null)
    {
        if ($time instanceof DateTime) {
            $result = clone $time;
        } elseif (is_integer($time)) {
            $result = new DateTime(date('Y-m-d H:i:s', $time));
        } else {
            $result = new DateTime($time);
        }
        $result->setTimeZone(new DateTimeZone('UTC'));
        return $result;
    }

    public function __toString()
    {
        return
            sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText) . "\r\n" .
            $this->headers . "\r\n" .
            $this->getContent();
    }

}
