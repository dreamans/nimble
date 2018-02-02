<?php

/*
 * This file is part of the Bubble package
 *
 * Copyright (c) 2017 <Storete Dev Team> All rights reserved
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\Http;

use Bubble\Framework\Http\Exception\NotFoundViewException;
use Nimble\Http\Exception\HttpRuntimeException;

class View
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param  string $path
     */
    public static function getViewObject($path)
    {
        return new View($path);
    }

    /**
     * @param  string $path
     */
    private function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param  string $tpl
     * @param  array  $vars
     *
     * @return string 
     */
    public function display($tpl, array $vars = [])
    {
        $tplFile = $this->parseViewFile($tpl);
        return $this->buildContent($tplFile, $vars);
    }

    /**
     * @param  string $tpl
     *
     * @return string tplpath
     */
    private function parseViewFile($tpl)
    {
        return $this->path . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $tpl) . '.php';
    }

    /**
     * @param  string $tplFile
     * @param  array  $vars
     *
     * @return string
     */
    private function buildContent($tplFile, array $vars)
    {
        return $this->includeViewFile($tplFile, $vars);
    }

    /**
     * @param  string $tplFile
     * @param  array  $vars
     *
     * @return string
     *
     * @throws HttpRuntimeException
     */
    private function includeViewFile($tplFile, array $vars = [])
    {
        if (!is_file($tplFile)) {
            throw new HttpRuntimeException(sprintf("View file \"%s\" not exists", $tplFile));
        }
        extract($vars, EXTR_OVERWRITE);
        ob_start();
        include $tplFile;
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}

