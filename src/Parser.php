<?php

namespace prowebcraft\template;

class Parser
{

    /**
     * Parse template
     * @param string $template
     * Template string, for ex.: "Hello, {{#firstname}}{{firstname}}{{else}}Guest{{/firstname}}!"
     * @param array $variables
     * Hash-table with variables (keys) and values
     * @param array $conditions
     * Optional conditions with boolean value or callback function
     * @return string
     */
    public static function parse(string $template, array $variables, array $conditions = []): string
    {
        $pattern = '/{{#(!?)([a-zA-Z_]+)}}(.*){{(\/\2)}}|{{(.+?(?=}}))}}/msS';
        while (preg_match($pattern, $template)) {
            $template = (string)preg_replace_callback($pattern, static function ($match) use ($variables, $conditions) {
                $simpleVar = $match[5] ?? null;
                $condition = $match[2] ?? null;
                $conditionBody = $match[3] ?? '';
                $negative = (bool)$match[1];
                $conditionEnclose = "{{/$condition}}";
                $callBackParam = null;
                if ($simpleVar && str_contains($simpleVar, '|')) {
                    [$simpleVar, $callBackParam] = explode('|', $simpleVar);
                }

                $overflow = null;
                if (!empty($condition) && (mb_stripos($conditionBody, $conditionEnclose))) {
                    [$conditionBody, $overflow] = explode($conditionEnclose, $conditionBody);
                    $overflow .= $conditionEnclose;
                }
                $return = '';
                if (!empty($condition)) {
                    if ((($value = ($conditions[$condition] ?? null)) !== null || ($value = ($variables[$condition] ?? null))) !== null) {
                        $result = (bool)(is_callable($value) ? (string)$value($match, $callBackParam) : $value);
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
                } elseif (!empty($simpleVar)) {
                    if (($value = ($variables[$simpleVar] ?? null)) !== null) {
                        $return = is_callable($value) ? (string)$value($match, $callBackParam) : $value;
                    }
                }
                // ignore unsupported return values
                if (is_object($return) || is_array($return)) {
                    $return = '';
                }
                if (is_string($overflow)) {
                    $return .= $overflow;
                }

                return (string)$return;
            }, $template);
        }

        return $template;
    }

}