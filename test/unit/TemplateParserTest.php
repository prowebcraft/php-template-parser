<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use prowebcraft\template\Parser;

class TemplateParserTest extends TestCase
{

    /**
     * @param string $template
     * @param string $expected
     * @param array $variables
     * @param array $conditions
     * @param array|null $customTags
     * @dataProvider templateTestDataProvider
     */
    public function testTemplateParser(
        string $template,
        string $expected,
        array $variables,
        array $conditions = [],
        ?array $customTags = null,
    ): void {
        if ($customTags) {
            Parser::$openTag  = $customTags[0];
            Parser::$closeTag = $customTags[1];
        } else {
            Parser::$openTag  = '{{';
            Parser::$closeTag = '}}';
        }

        $parsed = Parser::parse($template, $variables, $conditions);
        self::assertEquals($expected, $parsed);
    }

    /**
     * Data provider for testTemplateParser
     *
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
                ],
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
                ['paid' => true],
            ],
            [
                'Thanks for your {{#paid}}paid {{/paid}}order.',
                'Thanks for your order.',
                [],
                ['paid' => false],
            ],
            [
                'We all live in a {{#is_yellow}}yellow{{else}}brown{{/is_yellow}} submarine.',
                'We all live in a brown submarine.',
                [
                    'yellow' => 'yellow',
                    'brown' => 'brown',
                ],
                ['is_yellow' => false],
            ],
            [
                'Hello {{#name}}{{name}}{{else}}Guest{{/name}}!',
                'Hello Elon!',
                ['name' => 'Elon'],
            ],
            [
                '{{#is_value_true}}True{{else}}False{{/is_value_true}}',
                'True',
                [],
                ['is_value_true' => true],
            ],
            [
                '{{#!is_value_true}}True{{else}}False{{/is_value_true}}',
                'False',
                [],
                ['is_value_true' => true],
            ],
            [
                '{{#is_value_false}}True{{/is_value_false}}',
                '',
                [],
                ['is_value_false' => null],
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
            [
                'Hello from {{#country=Maldives}}Maldives{{else}}Rest of the World{{/country}}!',
                'Hello from Maldives!',
                ['country' => 'Maldives'],
            ],
            [
                'Hello from {{#country=Italy}}Italy{{else}}Rest of the World{{/country}}!',
                'Hello from Rest of the World!',
                ['country' => 'Maldives'],
            ],
            [
                'Current date is {{date}}. Local version of date is {{date|d.m.Y}}',
                'Current date is 2022-03-30. Local version of date is 30.03.2022',
                [
                    'date' => static function (string $format = 'Y-m-d') {
                        return date($format, strtotime('30 march 2022'));
                    },
                ],
            ],
            [
                'Nested conditions: {{#firstname}}{{#firstname}}{{firstname}}{{lastname}}{{else}}{{firstname}}{{/firstname}}{{/firstname}}',
                'Nested conditions: John',
                ['firstname' => 'John'],
            ],
            [
                'Simple negative condition: {{#!firstname}}No firstname{{/firstname}}',
                'Simple negative condition: No firstname',
                [],
            ],
            [
                'Negative condition: {{#!firstname}}No firstname{{else}}has firstname - {{firstname}}{{/firstname}}',
                'Negative condition: has firstname - John',
                ['firstname' => 'John'],
            ],
            [
                'Unsupported return type: {{array}}',
                'Unsupported return type: ',
                [
                    'array' => ['Array'],
                ],
            ],
            [
                'Dot Access Array: {{car.model}} by {{car.vendor}}',
                'Dot Access Array: Supra by Toyota',
                [
                    'car' => [
                        'vendor' => 'Toyota',
                        'model' => 'Supra',
                    ],
                ],
            ],
            [
                'Changed open/close tag: {#user}{user.name}{/user}',
                'Changed open/close tag: Valentine',
                [
                    'user' => ['name' => 'Valentine'],
                ],
                [],
                ['{', '}'],
            ],
        ];
    }
}
