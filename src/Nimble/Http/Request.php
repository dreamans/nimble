<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\Http;

class Request
{
    const METHOD_HEAD    = 'HEAD';
    const METHOD_GET     = 'GET';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';

    private $method;

    private $postJsonData = null;

    public function method()
    {
        if ($this->method === null) {
            $this->method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']): 'GET';
        }
        return $this->method;
    }

    public function scheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    public function isSecure()
    {
        $https = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : '';
        return !empty($https) && 'off' !== strtolower($https);
    }

    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ('XMLHttpRequest' == $_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    public function clientIp()
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        if (!$ip) {
            $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : '';
        }
        if (!$ip) {
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
        }
        return $ip;
    }

    public function request($key)
    {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
    }

    public function input($key)
    {
        $value = isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
        if (null === $value) {
            $value = $this->json($key); 
        }
        return $value;
    }

    public function get($key)
    {
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }

    public function post($key)
    {
        return isset($_POST[$key]) ? $_POST[$key] : null;
    }

    public function server($key)
    {
        $key = strtoupper($key);
        return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
    }

    public function postRawData()
    {
        $input = file_get_contents("php://input");
        return $input;
    }

    public function json($key)
    {
        if (null === $this->postJsonData) {
            $this->postJsonData = json_decode($this->postRawData(), true);
        }
        return isset($this->postJsonData[$key]) ? $this->postJsonData[$key] : null;
    }

    public function cookie($key)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
    }

    public function queryString()
    {
        $qs = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        return static::normalizeQueryString($qs);
    }

    public function host()
    {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']: '';
        if (!$host) {
            $host = isset($_SERVER['HOST']) ? $_SERVER['HOST']: '';
        }
        if (!$host) {
            $host = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME']: '';
        }
        if (!$host) {
            $host = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR']: '';
        }
        return $host;
    }

    public static function normalizeQueryString($qs)
    {
        if (!$qs) {
            return $qs;
        }
        foreach (explode('&', $qs) as $param) {
            if ('' === $param || '=' === $param[0]) {
                continue;
            }
            $keyValuePair = explode('=', $param, 2);
            $parts[] = isset($keyValuePair[1]) ?
                rawurlencode(urldecode($keyValuePair[0])).'='.rawurlencode(urldecode($keyValuePair[1])) :
                rawurlencode(urldecode($keyValuePair[0]));
            $order[] = urldecode($keyValuePair[0]);
        }
        array_multisort($order, SORT_ASC, $parts);

        return implode('&', $parts);
    }

    public function header($key)
    {
        $headerKey = strtoupper("http_{$key}");
        $headerKey = str_replace('-', '_', $headerKey);
        if (isset($_SERVER[$headerKey])) {
            return $_SERVER[$headerKey];
        }
        return null;
    }
}

