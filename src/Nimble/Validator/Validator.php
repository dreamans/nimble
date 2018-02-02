<?php

/*
 * This file is part of the Nimble package
 *
 * (c) Dreamans <dreamans@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nimble\Validator;

use Nimble\Foundation\Container;
use Nimble\Validator\Exception\ValidatorException;

class Validator
{
    /**
     * @var bool
     */
    public $validate = true;

    /**
     * @var array
     */
    public $message = [];

    /**
     * @var string
     */
    public $firstMessage = 'success';

    /**
     * @var array
     */
    private $validReg = [];

    /**
     * @var array
     */
    private $validMsg = [];
    
    /**
     * @param  array $validReg
     * @param  array $validMsg
     *
     *   $validReg = [
     *       [ attribute1, value1, rule1|rule2... ]
     *       [ attribute2, value2, rule1|rule2... ]
     *       ...
     *   ];
     *
     *   $validMsg = [
     *       ':rule1' => ':attribute message',
     *       ':rule2' => ':attribute message',
     *   ];
     */
    public static function make(array $validReg, array $validMsg)
    {
        return new Validator($validReg, $validMsg);
    }

    /**
     * @param  array $validReg
     * @param  array $validMsg
     */
    public function __construct(array $validReg, array $validMsg)
    {
        $this->validReg = $validReg;
        $this->validMsg = $validMsg;

        $this->checkValid();
    }

    /**
     * @throws ValidatorException::invalidArgument
     */
    private function checkValid()
    {
        foreach ($this->validReg as $index => $valid) {
            if (count($valid) != 3) {
                throw ValidatorException::invalidArgument(sprintf('Expected 3 items in $validReg[%s] but found %s items', $indexi + 1, count($valid)));
            }
            list($field, $value, $rule) = $valid;

            $arrValidRule = $this->formatValidRule($rule);
            $validStatus = $this->makeRule($arrValidRule, $value);

            if($validStatus) {
                $this->makeMessage($validStatus, $field);
            }
        }
    }

    /**
     * @param  string $rule
     *
     * @return array
     */
    private function formatValidRule($rule)
    {
        $arrRule = [];
        if (!$rule) {
            return $arrRule;
        }
        $arrTmpRule = explode('|', $rule);
        foreach($arrTmpRule as $ru) {
            preg_match("/([a-z]+)(\[(.+)\])?/", $ru, $match);
            $iRule = isset($match[1]) ? $match[1]: null;
            $iPara = isset($match[3]) ? $match[3]: null;
            $arrRule[] = [
                'method' => $iRule,
                'param'  => $iPara,
                'key'    => $ru,
            ];
        }
        return $arrRule;
    }

    /**
     * @param  array $arrRule
     * @param  mixed $value
     *
     * @return array
     *
     * @throws ValidatorException::badMethodCall
     */
    private function makeRule(array $arrRule, $value)
    {
        $retRule = [];
        foreach($arrRule as $r) {
            $method = ucfirst(strtolower($r['method'])) . 'Rule';
            $ruleClass = "Nimble\\Validator\\Rule\\{$method}";
            if (!method_exists($ruleClass, 'rule')) {
                throw ValidatorException::badMethodCall("rule method '{$method}' not exists");
            }
            $status = call_user_func_array("{$ruleClass}::rule", [$value, $r['param']]);
            $retRule[$r['key']] = $status;
        }
        return $retRule;
    }

    /**
     * @param  array  $ruleResult
     * @param  string $field
     */
    private function makeMessage(array $ruleResult, $field)
    {
        foreach ($ruleResult as $rule => $status) {
            if ($status) {
                continue;
            }
            $errorMessage = ":attribute {$rule} validate failed";
            if (isset($this->validMsg[$rule])) {
                $errorMessage = $this->validMsg[$rule];
            } elseif (isset($this->validMsg['other'])) {
                $errorMessage = $this->validMsg['other'];
            }

            $this->validate = false;
            $this->message[$field] = str_replace(':attribute', $field, $errorMessage);
            if ('success' === $this->firstMessage) {
                $this->firstMessage = $this->message[$field];
            }
        }
    }
}

