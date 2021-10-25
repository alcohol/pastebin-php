<?php

declare(strict_types=1);

$header = <<<'EOF'
    (c) Rob Bast <rob.bast@gmail.com>

    For the full copyright and license information, please view
    the LICENSE file that was distributed with this source code.
    EOF;

$finder = new PhpCsFixer\Finder();
$config = new PhpCsFixer\Config('paste.robbast.nl');

$finder
    ->in(__DIR__)
    ->exclude(['docker', 'var', 'vendor'])
    ->append([
        'bin/console',
        '.php_cs.dist',
    ])
;

return $config
    ->setRules([
        '@PSR1' => true,
        '@PSR2' => true,
        '@DoctrineAnnotation' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        // additionally
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'concat_space' => false,
        'header_comment' => ['header' => $header],
        'no_unused_imports' => false,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'phpdoc_align' => false,
        'phpdoc_order' => true,
        'phpdoc_summary' => false,
        'simplified_null_return' => false,
        'ternary_to_null_coalescing' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
