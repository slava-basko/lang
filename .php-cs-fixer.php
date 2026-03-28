<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

return (new Config())
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
    // 💡 by default, Fixer looks for `*.php` files excluding `./vendor/` - here, you can groom this config
    ->setFinder(
        (new Finder())
            // 💡 root folder to check
            ->in(__DIR__)
            // 💡 additional files, eg bin entry file
            // ->append([__DIR__.'/bin-entry-file'])
            // 💡 folders to exclude, if any
            // ->exclude([/* ... */])
            // 💡 path patterns to exclude, if any
            // ->notPath([/* ... */])
            // 💡 extra configs
            // ->ignoreDotFiles(false) // true by default in v3, false in v4 or future mode
            // ->ignoreVCS(true) // true by default
    )
;
