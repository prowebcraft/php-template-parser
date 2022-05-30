<?php

use PHPUnit\Framework\TestCase;
use prowebcraft\template\Parser;

class TemplateParserTest extends TestCase
{

    /**
     * @param string $template
     * @param string $expected
     * @param array $variables
     * @param array $conditions
     * @return void
     * @dataProvider templateTestDataProvider
     */
    public function testTemplateParser(string $template, string $expected, array $variables, array $conditions = []): void
    {
        $parsed = Parser::parse($template, $variables, $conditions);
        self::assertEquals($expected, $parsed);
    }

    /**
     * Data provider for testTemplateParser
     * @return array[]
     */
    public function templateTestDataProvider(): array
    {
        return [
            [
                'Hello, {{firstname}}! Your order #{{Order Id}} is confirmed',
                'Hello, John Doe! Your order #12345-67-89 is confirmed',
                [
                    'firstname' => 'John Doe',
                    'Order Id' => '12345-67-89',
                ]
            ],
            [
                'Current date is {{current_date}}',
                'Current date is 2022-01-01',
                [
                    'current_date' => function($a, $b) {
                        return '2022-01-01';
                    },
                ]
            ],
            [
                'This is {{unknown}} variable',
                'This is  variable',
                [],
            ],
            [
                'Thanks for your {{#paid}}paid {{/paid}}order.',
                'Thanks for your paid order.',
                [],
                [
                    'paid' => true,
                ]
            ],
            [
                'Thanks for your {{#paid}}paid {{/paid}}order.',
                'Thanks for your order.',
                [],
                [
                    'paid' => false,
                ]
            ],
            [
                'We all live in a {{#is_yellow}}yellow{{else}}brown{{/is_yellow}} submarine.',
                'We all live in a brown submarine.',
                [
                    'yellow' => 'yellow',
                    'brown' => 'brown',
                ],
                [
                    'is_yellow' => false,
                ]
            ],
            [
                'Hello {{#name}}{{name}}{{else}}Guest{{/name}}!',
                'Hello Elon!',
                [
                    'name' => 'Elon',
                ],
            ],
            [
                '{{#is_value_true}}True{{else}}False{{/is_value_true}}',
                'True',
                [],
                [
                    'is_value_true' => true
                ]
            ],
            [
                '{{#!is_value_true}}True{{else}}False{{/is_value_true}}',
                'False',
                [],
                [
                    'is_value_true' => true
                ]
            ],
            [
                '{{#is_value_false}}True{{/is_value_false}}',
                '',
                [],
                [
                    'is_value_false' => null
                ]
            ],
            [
                'Nested conditions: {{#false}}false{{else}}{{#true}}{{name}}{{/true}}{{/false}}.',
                'Nested conditions: John.',
                [
                    'false' => false,
                    'true' => true,
                    'name' => 'John',
                ],
            ],
        ];
    }
}
