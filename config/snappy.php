<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Snappy PDF / Image Configuration
    |--------------------------------------------------------------------------
    |
    | This option contains settings for PDF generation.
    |
    | Enabled:
    |    
    |    Whether to load PDF / Image generation.
    |
    | Binary:
    |    
    |    The file path of the wkhtmltopdf / wkhtmltoimage executable.
    |
    | Timout:
    |    
    |    The amount of time to wait (in seconds) before PDF / Image generation is stopped.
    |    Setting this to false disables the timeout (unlimited processing time).
    |
    | Options:
    |
    |    The wkhtmltopdf command options. These are passed directly to wkhtmltopdf.
    |    See https://wkhtmltopdf.org/usage/wkhtmltopdf.txt for all options.
    |
    | Env:
    |
    |    The environment variables to set while running the wkhtmltopdf process.
    |
    */
    
    'pdf' => [
        'enabled' => true,
        'binary' => env('WKHTMLTOPDF_BINARY', '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf"'),
        'timeout' => false,
        'options' => [
            'enable-local-file-access' => true,
            'orientation'   => 'portrait',
            'encoding'      => 'UTF-8',
            'page-size'     => 'A4',
            'margin-top'    => 10,
            'margin-right'  => 10,
            'margin-bottom' => 10,
            'margin-left'   => 10,
            'enable-javascript' => true,
            'javascript-delay' => 1000,
            'no-stop-slow-scripts' => true,
            'enable-smart-shrinking' => true,
            'no-background' => false,
            'lowquality' => false,
            'footer-right' => 'Page [page] of [topage]',
            'footer-line' => true,
            'header-spacing' => 3,
            'footer-spacing' => 3,
        ],
        'env' => [],
    ],
    
    'image' => [
        'enabled' => true,
        'binary'  => env('WKHTMLTOIMAGE_BINARY', '"C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage"'),
        'timeout' => false,
        'options' => [
            'enable-local-file-access' => true,
        ],
        'env' => [],
    ],
]; 