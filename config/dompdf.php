<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | Set some default values. It is possible to add all defines that can be set
    | in dompdf_config.inc.php. You can also override the entire config file.
    |
    */
    'show_warnings' => false,   // Throw an Exception on warnings from dompdf

    'public_path' => null,  // Override the public path if needed

    /*
     * Dejavu Sans font is missing glyphs for converted entities, turn it off if you need to show € and £.
     */
    'convert_entities' => true,

    'options' => array(
        /**
         * The location of the DOMPDF font directory
         */
        "font_dir" => storage_path('fonts'),

        /**
         * The location of the DOMPDF font cache directory
         */
        "font_cache" => storage_path('fonts'),

        /**
         * The location of a temporary directory.
         */
        "temp_dir" => sys_get_temp_dir(),

        /**
         * ✅ CHROOT - Correct pour accès fichiers locaux
         */
        "chroot" => realpath(base_path()),

        /**
         * ✅ PROTOCOLES - Ajout de data: pour base64
         */
        'allowed_protocols' => [
            "file://" => ["rules" => []],
            "http://" => ["rules" => []],
            "https://" => ["rules" => []], 
            "data://" => ["rules" => []]  // ⭐ AJOUT pour base64
        ],

        /**
         * Log output pour debug images
         */
        'log_output_file' => null,

        /**
         * Font subsetting
         */
        "enable_font_subsetting" => false,

        /**
         * ✅ BACKEND PDF - CPDF est optimal pour images
         */
        "pdf_backend" => "CPDF",

        /**
         * Media type
         */
        "default_media_type" => "screen",

        /**
         * Paper settings
         */
        "default_paper_size" => "a4",
        'default_paper_orientation' => "portrait",

        /**
         * Default font
         */
        "default_font" => "serif",

        /**
         * ⭐ DPI OPTIMISÉ pour images de qualité
         */
        "dpi" => 150,  // CHANGÉ: 96 → 150 pour meilleure qualité images

        /**
         * ✅ PHP désactivé pour sécurité
         */
        "enable_php" => false,

        /**
         * ✅ JavaScript désactivé pour PDF
         */
        "enable_javascript" => false,  // CHANGÉ: true → false (inutile pour PDF)

        /**
         * ⭐ ACCÈS DISTANT - Désactivé pour sécurité et performance
         */
        "enable_remote" => false,  // CHANGÉ: true → false (on utilise base64)

        /**
         * Font height ratio
         */
        "font_height_ratio" => 1.1,

        /**
         * ✅ HTML5 parser
         */
        "enable_html5_parser" => true,
    ),
);