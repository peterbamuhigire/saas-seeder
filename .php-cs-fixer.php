<?php
declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src/Auth/DTO',
        __DIR__ . '/src/Auth/Security',
        __DIR__ . '/src/Auth/Services',
        __DIR__ . '/src/Database',
        __DIR__ . '/src/Http/RateLimit',
        __DIR__ . '/src/Http/Security',
        __DIR__ . '/src/Modules',
        __DIR__ . '/src/UI',
        __DIR__ . '/tests',
    ])
    ->exclude(['public/assets', 'vendor']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'single_quote' => true,
    ])
    ->setFinder($finder);
