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

use InvalidArgumentException;
use RuntimeException;

/**
 * @author Dreamans <dreamans@163.com>
 */
class Configure
{
    /**
     * The absolute path to the configuration file
     *
     * @var string 
     */
    private $cfgPath;

    /**
     * Save the value from the configure file
     *
     * @var array
     */
    private $configValue = [];

    /**
     * Configure key-value pair
     *
     * @var array
     */
    private $configKeyValue = [];

    /**
     * @param  string $cfgPath
     */
    public function __construct($cfgPath)
    {
        $this->cfgPath = $cfgPath;
    }

    /**
     * @param  string $key
     *
     * @return mixed
     *
     * @throws InvalidArgumentException When key is empty
     */
    public function getConfig($key)
    {
        if (!$key) {
            throw new InvalidArgumentException('Invalid config key');
        }
        if (!isset($this->configKeyValue[$key])) {
            $configValue = $this->getConfigKeyValue($key);
            $this->configKeyValue[$key] = $configValue;
        }
        return $this->configKeyValue[$key];
    }

    /**
     * @param  string $fileKey
     * @param  array  $arrKeyValue
     */
    public function setConfig($fileKey, array $arrKeyValue)
    {
        if (!isset($this->configValue[$fileKey])) {
            return $this->configValue[$fileKey] = $arrKeyValue;
        }
        $this->configValue[$fileKey] = array_merge($this->configValue[$fileKey], $arrKeyValue);
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    private function getConfigKeyValue($key)
    {
        $fileCfgKey = $this->formatConfigKey($key);

        $arrKeyValue = $this->loadConfigFileValue($fileCfgKey['file_name']);

        if (!$fileCfgKey['key']) {
            return $arrKeyValue;
        }

        $tmpValue = $arrKeyValue;
        foreach($fileCfgKey['key'] as $k) {
            if (!isset($tmpValue[$k])) {
                return null;
            }
            $tmpValue = $tmpValue[$k];
        }
        return $tmpValue;
    }

    /**
     * @param  string $fileName
     *
     * @return array
     *
     * @throws RuntimeException If file not exists
     */
    private function loadConfigFileValue($fileName)
    {
        if (isset($this->configValue[$fileName])) {
            return $this->configValue[$fileName];
        }
        $file = $this->cfgPath. DIRECTORY_SEPARATOR . $fileName . '.php';
        if (!is_file($file)) {
            throw new RuntimeException(sprintf('Config file "%s" not exists', $file));
        }
        $this->configValue[$fileName] = include $file;

        return $this->configValue[$fileName];
    }

    /**
     * @param  string $key
     *
     * @return array
     */
    private function formatConfigKey($key)
    {
        $arrKey = array_filter(explode('.', $key));
        $fileName = array_shift($arrKey);
        return [
            'file_name' => $fileName,
            'key'       => $arrKey,
        ];
    }
}

