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

// Helpers defined only by laravel/framework (Illuminate/Foundation/helpers.php); the
// package requires standalone illuminate/* components, so it must not call them.
arch('it only calls helpers that the illuminate/* components define')
    ->expect('Laranex\LaravelMyanmarNRC')
    ->not->toUse([
        '__', 'abort', 'abort_if', 'abort_unless', 'action', 'app', 'app_path', 'asset', 'auth',
        'back', 'base_path', 'bcrypt', 'broadcast', 'broadcast_if', 'broadcast_unless', 'cache',
        'config', 'config_path', 'context', 'cookie', 'csrf_field', 'csrf_token', 'database_path',
        'decrypt', 'defer', 'dispatch', 'dispatch_sync', 'encrypt', 'event', 'fake', 'info',
        'lang_path', 'logger', 'logs', 'method_field', 'mix', 'now', 'old', 'policy',
        'precognitive', 'public_path', 'redirect', 'report', 'report_if', 'report_unless',
        'request', 'rescue', 'resolve', 'resource_path', 'response', 'route', 'secure_asset',
        'secure_url', 'session', 'storage_path', 'to_action', 'to_route', 'today', 'trans',
        'trans_choice', 'uri', 'url', 'validator', 'view',
    ]);
