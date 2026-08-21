<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()->in('src');

$rules = [
    '@PHP71Migration' => true,
    '@PSR12'          => true,
    '@PhpCsFixer'     => true,
    'operator_linebreak' => [
        'position' => 'end',
    ]
];

$config = new PhpCsFixer\Config();

return $config->setRiskyAllowed(true)->setRules($rules)->setFinder($finder);
