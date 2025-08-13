<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public $aliases = [
		'csrf'     => \CodeIgniter\Filters\CSRF::class,
		'toolbar'  => \CodeIgniter\Filters\DebugToolbar::class,
		'honeypot' => \CodeIgniter\Filters\Honeypot::class,
	
		// Tambahan untuk auth
		'auth'     => \App\Filters\AuthFilter::class,
	];

    public array $globals = [
        'before' => [
            // 'csrf',  // aktifkan kalau mau
        ],
        'after' => [
            'toolbar',
        ],
    ];

    public array $methods = [];

    public array $filters = [
        // contoh kalau nanti kamu punya 'auth' filter buatan sendiri:
        // 'auth' => [
        //     'before' => [
        //         '*'  // semua dilindungi,
        //     ],
        //     'except' => [
        //         '/', 'login', 'login/*', 'logout',
        //         'assets/*', 'css/*', 'js/*', 'images/*', 'videos/*'
        //     ]
        // ],
    ];
}
