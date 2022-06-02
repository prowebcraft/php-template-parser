# php-template-parser
Simple Template Parser with nested conditions

### Usage examples

To sign your request create signature instance

#### Signing request

```php
$template = <<<TPL
Hello, {{username}}!
Current date is {{date}}. Year is {{date|Y}}. 
You are on {{#is_paid}}paid{{else}}free{{/is_paid}} plan.
You have {{car.model}} by {{car.vendor}}
TPL;

$variables = [
    'username' => 'John',
    'date' => function (string $format = 'Y-m-d') {
        return date($format);
    },
    'car' => [
        'vendor' => 'Toyota',
        'model' => 'Supra',
    ]
];
$conditions = [
    'is_paid' => true
];
$parsedTemplate = \prowebcraft\template\Parser::parse($template, $variables, $conditions);
```