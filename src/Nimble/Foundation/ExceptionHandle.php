<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\Foundation;

use ReflectionClass;
use Exception;

class ExceptionHandle
{
    /**
     * @var Exception
     */
    protected $exception;

    /**
     * @param  Exception $e
     */
    public function __construct(Exception $e)
    {
        $this->exception = $e;
    }

    public function render()
    {
        if ('cli' == PHP_SAPI) {
            $this->consoleRender();    
        } else {
            $this->httpRender();
        }
    }

    protected function consoleRender()
    {
        $arrMessage = $this->message();
        $arrOutput = [
            "[{$arrMessage['class']}] {$arrMessage['message']}",
            "{$arrMessage['file']}({$arrMessage['line']})",
        ];
        $maxLength = 0;
        foreach ($arrOutput as $output) {
            $tmpLen = strlen($output);
            if ($maxLength < $tmpLen) {
                $maxLength = $tmpLen;
            }
        }

        $arrFormatOutput   = [];
        $arrFormatOutput[] = "\033[41;37m ". str_repeat(" ", $maxLength) ." \033[0m";
        foreach ($arrOutput as $key => $output) {
            $suppLength = $maxLength - strlen($output);
            $arrFormatOutput[] = "\033[41;37m {$output}". str_repeat(" ", $suppLength) ." \033[0m";
        }
        $arrFormatOutput[] = "\033[41;37m ". str_repeat(" ", $maxLength) ." \033[0m";

        echo "\n";
        echo implode("\n", $arrFormatOutput);
        echo "\n\n";
    }

    protected function httpRender()
    {
        $trace   = implode("<br/>", $this->trace());
        $message = $this->message();
        $msg     = "{$message['message']}";
        $file    = "{$message['class']} in {$message['file']}:{$message['line']}";
        $exClass = $message['class'];

        $tpl = '
            <!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="utf-8">
                    <title>system exception occurred</title>
                    <style>body{font: normal 9pt "Verdana";color: #000;background: #fff;}html,body,div,span,h1,p,pre{border:2;outline:0;font-size:100%;vertical-align:baseline;background:transparent;margin:0;padding:0;}.container{margin: 1em 4em;}h1 {font: normal 18pt "Verdana";color: #f00;margin-bottom: .5em;}.message {color: #000;padding: 1em;font-size: 11pt;background: #f3f3f3;-webkit-border-radius: 10px;-moz-border-radius: 10px;border- radius:10px;margin-bottom: 1em;line-height: 160%;}.file {margin-bottom: 1em;font-weight: bold;}.traces{margin: 2em 0;} .traces p{border: 1px dashed #c00; padding:10px;line-height:1.5em;font-size:0.8em;}.copyright{color:     gray;font-size: 8pt;border-top: 1px solid #aaa;padding-top: 1em;margin-bottom: 1em;}</style>
                </head>
                <body>
                    <div class="container">
                        <h1>' . $exClass . '</h1>
                        <p class="message">'. $msg .'</p>
                        <div class="file">'. $file .'</div>
                        <div class="traces"><p>'. $trace .'</p></div>
                        <p class="copyright">'.date(DATE_ATOM).' <a title="官方网站" href="http://" target="_blank">Nimble</a><sup>0.9.9</sup></p>
                    </div>
                </body>
            </html>';

        echo $tpl;
    }

    /**
     * @return array
     */
    protected function trace()
    {
        $trace = $this->exception->getTraceAsString();
        $arrTrace = explode("\n", $trace);
        return array_filter($arrTrace);
    }

    /**
     * @return array
     */
    protected function message()
    {
        $refClass = new ReflectionClass($this->exception);
        $className = $refClass->getName();

        $arrMessage = [
            'message' => $this->exception->getMessage(),
            'code'    => $this->exception->getCode(),
            'file'    => $this->exception->getFile(),
            'line'    => $this->exception->getLine(),
            'class'   => $className,
        ];
        return $arrMessage;
    }
}

