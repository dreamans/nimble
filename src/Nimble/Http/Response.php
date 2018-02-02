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

use Nimble\Helper\Env;

class Response
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $headers = [];

    public function __construct()
    {
        $this->initRespHeaders();
    }

    /**
     * @param  string $content
     */
    public function content($content)
    {
        $this->content = $content;
    }

    /**
     * @param  string $name
     * @param  string $value
     * @param  int    $expire
     * @parah  string $path
     * @param  string $domain
     * @param  bool   $secure
     * @param  bool   $httpOnly
     *
     * @return bool
     */
    public function cookie($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httpOnly = false)
    {
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    public function respContent()
    {
        $this->responseHeader();
        $this->responseBody();
    }
    
    /**
     * @param  string $key
     * @param  string $value
     */
    public function header($key, $value)
    {
        $this->headers[$key] = $value;
    }

    private function initRespHeaders()
    {
        $defaultHeaders = [
            'Content-Type'  => 'charset=utf-8',
            'X-Powered-By'  => 'Nimble/' . Env::nimbleVersion(),
            'Cache-control' => 'private',
        ];
        foreach ($defaultHeaders as $key => $value) {
            $this->headers[$key] = $value;
        }
    }

    private function responseHeader()
    {
        if (!headers_sent()) {
            foreach($this->headers as $key => $value) {
                $header = "{$key}: {$value}";
                header($header);
            }
        }
    }

    private function responseBody()
    {
        echo $this->content;
    }
}

