<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $globals = [
        'before' => [
            // pakai filter auth untuk semua route KECUALI daftar di except
            'auth' => [
                'except' => [
                    'login',
                    'logout',
                    'auth/*',
                    'assets/*',
                    'css/*', 'js/*', 'images/*',
                    'videos/*',            // kalau kamu expose HLS statik
                ],
            ],
        ],
        'after' => [
            'toolbar',
        ],
    ];

    public array $methods = [];

    public array $filters = [];

    // ===== ALIASES =====
    public array $aliases = [
        'csrf'     => \CodeIgniter\Filters\CSRF::class,
        'toolbar'  => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot' => \CodeIgniter\Filters\Honeypot::class,

        // penting: alias 'auth' → AuthFilter kamu
        'auth'     => \App\Filters\AuthFilter::class,
    ];
}
