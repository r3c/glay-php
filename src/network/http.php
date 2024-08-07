<?php

namespace Glay\Network;

class HTTP
{
    const FAILURE = 0;
    const SUCCESS = 200;
    const REDIRECT_PERMANENT = 301;
    const REDIRECT_FOUND = 302;
    const REDIRECT_PROXY = 305;
    const REDIRECT_TEMPORARY = 307;

    public $cookies;
    public $connect_timeout;
    public $headers;
    public $http_version;
    public $location_follow;
    public $location_max;
    public $proxy;
    public $size_max;
    public $timeout;
    public $useragent;

    public static $default_cookies = array();
    public static $default_connect_timeout = null;
    public static $default_headers = array();
    public static $default_http_version = CURL_HTTP_VERSION_1_1;
    public static $default_location_follow = false;
    public static $default_location_max = null;
    public static $default_proxy = null;
    public static $default_size_max = null;
    public static $default_timeout = null;
    public static $default_useragent = null;

    public static function code(int $code, string | null $data = null, array $headers = array()): HTTPResponse
    {
        return new HTTPResponse($code, $headers, $data);
    }

    public static function data(string | null $data, array $headers = array()): HTTPResponse
    {
        return new HTTPResponse(self::SUCCESS, $headers, $data);
    }

    public static function go(string $url, int $code = self::REDIRECT_FOUND): HTTPResponse
    {
        return new HTTPResponse($code, array('Location' => (string)URI::here()->combine($url)), null);
    }

    public function __construct()
    {
        $this->cookies = self::$default_cookies;
        $this->connect_timeout = self::$default_connect_timeout;
        $this->headers = self::$default_headers;
        $this->http_version = self::$default_http_version;
        $this->location_follow = self::$default_location_follow;
        $this->location_max = self::$default_location_max;
        $this->proxy = self::$default_proxy;
        $this->size_max = self::$default_size_max;
        $this->timeout = self::$default_timeout;
        $this->useragent = self::$default_useragent;
    }

    public function header(string $name, string | null $value = null)
    {
        $this->headers[strtolower($name)] = $name . ($value !== null ? ': ' . $value : '');
    }

    public function query(string $method, string $url, array | string | null $body = null): HTTPResponse
    {
        if (preg_match('#^https?://#', $url) !== 1) {
            return self::code(self::FAILURE, 'Invalid URL');
        }

        $handle = curl_init();
        $method = strtoupper($method);

        if (count($this->cookies) > 0) {
            curl_setopt($handle, CURLOPT_COOKIE, implode('; ', array_map(function ($key, $value) {
                return rawurlencode($key) . '=' . rawurlencode($value);
            }, array_keys($this->cookies), array_values($this->cookies))));
        }

        if ($this->connect_timeout !== null) {
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT_MS, $this->connect_timeout);
        }

        if (count($this->headers) > 0) {
            curl_setopt($handle, CURLOPT_HTTPHEADER, array_values($this->headers));
        }

        if ($this->location_max !== null) {
            curl_setopt($handle, CURLOPT_MAXREDIRS, $this->location_max);
        }

        if ($this->proxy !== null) {
            curl_setopt($handle, CURLOPT_PROXY, $this->proxy);
        }

        if ($this->size_max !== null) {
            curl_setopt($handle, CURLOPT_PROGRESSFUNCTION, function ($handle, $size, $downloaded) {
                return $downloaded > $this->size_max ? 1 : 0;
            });
        }

        if ($this->timeout !== null) {
            curl_setopt($handle, CURLOPT_TIMEOUT_MS, $this->timeout);
        }

        if ($this->useragent !== null) {
            curl_setopt($handle, CURLOPT_USERAGENT, $this->useragent);
        }

        if ($body !== null) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
        }

        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, $this->location_follow);
        curl_setopt($handle, CURLOPT_HEADER, true);
        curl_setopt($handle, CURLOPT_HTTP_VERSION, $this->http_version);
        curl_setopt($handle, CURLOPT_NOBODY, $method === 'HEAD');
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_TCP_NODELAY, false);
        curl_setopt($handle, CURLOPT_URL, $url);

        $output = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        curl_close($handle);

        if ($output === false) {
            return self::code(self::FAILURE, curl_error($handle));
        }

        $headers = array();
        $offset = strpos($output, "\r\n\r\n");

        if ($offset === false) {
            $offset = strlen($output);
        }

        foreach (explode("\r\n", substr($output, 0, $offset)) as $header) {
            $fragments = explode(':', $header, 2);
            $headers[trim($fragments[0])] = count($fragments) > 1 ? trim($fragments[1]) : '';
        }

        return new HTTPResponse($code, $headers, (string)substr($output, $offset + 4));
    }
}

class HTTPResponse
{
    private static $messages = array(
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        410 => 'Gone',
        500 => 'Internal Server Error',
        501 => 'Not Implemented'
    );

    public $code;
    public $data;
    public $headers;

    public function __construct(int $code, array $headers, string | null $data)
    {
        $this->code = (int)$code;
        $this->data = $data;
        $this->headers = array_change_key_case((array)$headers);
    }

    public function header(string $name, string | null $default = null): string
    {
        $key = strtolower($name);

        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }

        return $default;
    }

    public function send(): void
    {
        if ($this->code !== HTTP::SUCCESS) {
            if (isset(self::$messages[$this->code])) {
                $status_text = ' ' . self::$messages[$this->code];
            } else {
                $status_text = '';
            }

            header($_SERVER['SERVER_PROTOCOL'] . ' ' . $this->code . $status_text, true, $this->code);
        }

        if ($this->headers !== null) {
            foreach ($this->headers as $name => $value) {
                header(ucwords($name, '-') . ': ' . $value);
            }
        }

        if ($this->data !== null) {
            echo $this->data;
        }
    }
}
