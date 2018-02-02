<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\Autoload;

class ClassLoader
{
    private $psr4Prefix = [];

    private $psr4PrefixLength = [];

    private $psr4Map = [];

    public function setPrefix($nsPre, array $pathPre)
    {
        $this->psr4Prefix[$nsPre] = $pathPre;
        $this->psr4PrefixLength[$nsPre[0]][$nsPre] = strlen($nsPre);
    }

    public function register()
    {
        spl_autoload_register([$this, 'loadClass'], true, true);
    }

    public function loadClass($className)
    {
        $classFile = $this->transNameSpaceToFilePath($className);
        if ($classFile) {
            include $classFile;
        }
    }

    private function transNameSpaceToFilePath($className)
    {
        if (isset($this->psr4Map[$className])) {
            return $this->psr4Map[$className];
        }
        $localPath = strtr($className, '\\', DIRECTORY_SEPARATOR) . '.php';
        if (isset($this->psr4PrefixLength[$localPath[0]])) {
            foreach($this->psr4PrefixLength[$localPath[0]] as $nsPre => $length) {
                if (0 === strpos($className, $nsPre)) {
                    foreach($this->psr4Prefix[$nsPre] as $path) {
                        $file = $path . DIRECTORY_SEPARATOR . substr($localPath, $length);
                        if (is_file($file)) {
                            return $file;
                        }
                    }
                }
            }
        }

        $this->psr4Map[$className] = false;
        return false;
    }
}

