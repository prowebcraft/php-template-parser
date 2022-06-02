<?php

declare(strict_types=1);

namespace prowebcraft\template;

use Prowebcraft\Dot;

use function call_user_func_array;
use function explode;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function mb_substr;
use function preg_match;
use function preg_replace_callback;
use function sprintf;
use function str_contains;

class Parser
{
    /**
     * Parse template
     *
     * @param string $template
     * Template string, for ex.: "Hello, {{#firstname}}{{firstname}}{{else}}Guest{{/firstname}}!"
     * @param array  $variables
     * Hash-table with variables (keys) and values
     * @param array  $conditions
     * Optional conditions with boolean value or callback function
     *
     * @noinspection CallableParameterUseCaseInTypeContextInspection
     */
    public static function parse(string $template, array $variables, array $conditions = []): string
    {
        $variables = new Dot($variables);
        $pattern   = '/{{#(!?)([a-zA-Z_1-9]+)(=[^}}]+)?}}(.*){{(\/\2)}}|{{(.+?(?=}}))}}/msS';
        while (preg_match($pattern, $template)) {
            $template = (string) preg_replace_callback($pattern, static function ($match) use ($variables, $conditions) {
                $simpleVar        = $match[6] ?? null;
                $condition        = $match[2] ?? null;
                $conditionBody    = $match[4] ?? '';
                $conditionExtra   = $match[3] ?? null;
                $negative         = (bool) $match[1];
                $conditionEnclose = sprintf('{{/%s}}', $condition);
                $callBackParams   = [];
                if ($simpleVar && str_contains($simpleVar, '|')) {
                    [$simpleVar, $callBackParam] = explode('|', $simpleVar);
                    $callBackParams[]            = $callBackParam;
                }

                $overflow = null;
                if (! empty($condition) && (str_contains($conditionBody, $conditionEnclose))) {
                    [$conditionBody, $overflow] = explode($conditionEnclose, $conditionBody);
                    $overflow                  .= $conditionEnclose;
                }

                $return = '';
                if (! empty($condition)) {
                    if ((($value = ($conditions[$condition] ?? null)) !== null || ($value = ($variables[$condition] ?? null))) !== null) {
                        $value = (is_callable($value) ? (string) call_user_func_array($value, $callBackParams) : $value);
                        if ($conditionExtra) {
                            $result              = false;
                            $extraConditionValue = mb_substr($conditionExtra, 1);
                            switch ($conditionExtra[0]) {
                                case '=':
                                    $result = $value === $extraConditionValue;
                                    break;
                            }
                        } else {
                            $result = (bool) ($value);
                        }

                        if (preg_match('/(.*){{else}}(.*)/ms', $conditionBody, $condMatch)) {
                            // got extra condition block (else)
                            if ($negative) {
                                $return = $result ? $condMatch[2] : $condMatch[1];
                            } else {
                                $return = $result ? $condMatch[1] : $condMatch[2];
                            }
                        } elseif ($negative) {
                            $return = $result ? '' : $conditionBody;
                        } else {
                            $return = $result ? $conditionBody : '';
                        }
                    }
                } elseif (! empty($simpleVar)) {
                    if (($value = ($variables[$simpleVar] ?? null)) !== null) {
                        $return = is_callable($value) ? (string) call_user_func_array($value, $callBackParams) : $value;
                    }
                }

                // ignore unsupported return values
                if (is_object($return) || is_array($return)) {
                    $return = '';
                }

                if (is_string($overflow)) {
                    $return .= $overflow;
                }

                return (string) $return;
            }, $template);
        }

        return $template;
    }
}
