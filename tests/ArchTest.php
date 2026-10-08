<?php

declare(strict_types=1);
use Pest\ArchPresets\Php;

// Pest 2 (the PHP 8.1 lane) has no arch presets, so they only run on Pest 3+;
// the explicit rules below cover the same ground on every lane.
if (class_exists(Php::class)) {
    arch()->preset()->php();

    arch()->preset()->security();
}

arch('it will not use debugging functions')
    ->expect(['dd', 'ddd', 'dump', 'ray', 'die', 'exit', 'var_dump', 'var_export', 'print_r', 'debug_zval_dump', 'debug_print_backtrace', 'phpinfo', 'env'])
    ->each->not->toBeUsed();

arch('it will not use insecure functions')
    ->expect(['eval', 'exec', 'shell_exec', 'system', 'passthru', 'popen', 'proc_open', 'create_function', 'unserialize', 'extract', 'md5', 'sha1', 'uniqid', 'rand', 'mt_rand', 'assert'])
    ->each->not->toBeUsed();

arch('the package source declares strict types')
    ->expect('Laranex\LaravelMyanmarNRC')
    ->toUseStrictTypes();
