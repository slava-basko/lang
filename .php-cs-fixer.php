<?php

// https://cs.symfony.com/doc/rules/index.html

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

return (new Config())
    ->setUsingCache(false)
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        '@auto' => true,
        'modernize_strpos' => false,
        'single_blank_line_at_eof' => true,
        'native_function_invocation' => [
            'include' => [
                '@internal',
            ],
            'scope' => 'all',
        ],
        'trailing_comma_in_multiline' => false,
        'modifier_keywords' => [
            'elements' => ['method', 'property'],
        ],
    ])
    ->setFinder(
        (new Finder())
            ->in(__DIR__)
    )
;
