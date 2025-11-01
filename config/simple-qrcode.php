<?php

// Créer le fichier : config/simple-qrcode.php

return [
    /*
    |--------------------------------------------------------------------------
    | Simple QrCode Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour forcer l'utilisation de GD au lieu d'ImageMagick
    | Cela résout l'erreur "You need to install the imagick extension"
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Image Backend
    |--------------------------------------------------------------------------
    |
    | Définit quel backend utiliser pour la génération d'images
    | 'gd' : Utilise la bibliothèque GD (recommandé, plus compatible)
    | 'imagick' : Utilise ImageMagick (nécessite l'extension php-imagick)
    |
    */
    'image_backend' => 'gd',

    /*
    |--------------------------------------------------------------------------
    | Format par défaut
    |--------------------------------------------------------------------------
    |
    | Format de sortie par défaut pour les QR codes
    |
    */
    'default_format' => 'svg',

    /*
    |--------------------------------------------------------------------------
    | Taille par défaut
    |--------------------------------------------------------------------------
    |
    | Taille par défaut des QR codes générés
    |
    */
    'default_size' => 150,

    /*
    |--------------------------------------------------------------------------
    | Marge par défaut
    |--------------------------------------------------------------------------
    |
    | Marge par défaut autour des QR codes
    |
    */
    'default_margin' => 2,

    /*
    |--------------------------------------------------------------------------
    | Niveau de correction d'erreur par défaut
    |--------------------------------------------------------------------------
    |
    | L = ~7% correction
    | M = ~15% correction  
    | Q = ~25% correction
    | H = ~30% correction (recommandé)
    |
    */
    'default_error_correction' => 'H',

    /*
    |--------------------------------------------------------------------------
    | Couleurs par défaut
    |--------------------------------------------------------------------------
    |
    | Couleurs par défaut pour les QR codes
    |
    */
    'default_foreground_color' => [0, 62, 127],    // Bleu foncé
    'default_background_color' => [255, 255, 255], // Blanc

    /*
    |--------------------------------------------------------------------------
    | Optimisations pour PDF
    |--------------------------------------------------------------------------
    |
    | Paramètres optimisés pour l'usage dans les PDF
    |
    */
    'pdf_optimized' => [
        'size' => 120,
        'margin' => 2,
        'error_correction' => 'H',
        'format' => 'png'
    ],
];