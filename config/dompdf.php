<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings for DomPDF - Used for PDF Export Fallback
    |--------------------------------------------------------------------------
    |
    | These are the configuration settings used when falling back to DomPDF
    | when wkhtmltopdf is not available.
    |
    */

    /**
     * Set the root HTML element font size, used for relative font sizes
     */
    'font_size_converter' => 'pt',

    /**
     * Set the root font-family
     */
    'default_font' => 'dejavu sans',

    /**
     * Font cache directory
     */
    'font_cache' => storage_path('fonts/'),

    /**
     * Log output file (very useful for debugging)
     */
    'log_output_file' => null,

    /**
     * Enable remote file access
     */
    'enable_remote' => true,

    /**
     * Enable CSS float
     */
    'enable_css_float' => true,

    /**
     * Enable inline PHP
     */
    'enable_php' => false,

    /**
     * Enable inline Javascript
     */
    'enable_javascript' => true,

    /**
     * Enable remote javascript
     */
    'enable_remote_javascript' => true,

    /**
     * Use the more-than-experimental HTML5 Lib (Default is false)
     */
    'enable_html5_parser' => true,

    /**
     * Enable the PDF caching.
     * Uses Laravel cache driver set in config/cache.php
     */
    'enable_caching' => true,

    /**
     * PDF target DPI
     */
    'dpi' => 150,

    /**
     * Paper size for the PDF. Required for header height computations.
     *
     * @see https://github.com/dompdf/dompdf/blob/master/src/Adapter/CPDF.php#L45-L87
     */
    'paper_size' => 'a4',

    /**
     * Paper orientation for the PDF
     */
    'paper_orientation' => 'portrait',

    /**
     * Enable font subsetting.
     */
    'enable_font_subsetting' => true,

    /**
     * Set PDF compression.
     */
    'pdf_compression' => true,

    /**
     * Set PDF back compat.
     */
    'pdf_back_compat' => false,

    /**
     * The temporary directory for dompdf fonts.
     */
    'temp_dir' => sys_get_temp_dir(),

    /**
     * Canvas DPI setting
     * This setting determines the default DPI setting for images and fonts.
     */
    'canvas_dpi' => 96,

    /**
     * Maximum image size (in pixels)
     * Larger images will be reduced in size
     */
    'max_image_size' => [1600, 1600],

    /**
     * Character encoding
     */
    'character_encoding' => 'utf-8',

    /**
     * HTTP context options
     */
    'http_context' => [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ],

    /**
     * Allow scripts to run for a maximum of x seconds
     */
    'javascript_timeout' => 60,

    /**
     * The file where JavaScript messages should be logged
     * (Useful for debugging JavaScript support)
     */
    'javascript_log' => null,

    /**
     * Use the Unicode Bidi algorithm to handle RTL (right to left) text
     */
    'unicode_bidirectional' => true,
]; 