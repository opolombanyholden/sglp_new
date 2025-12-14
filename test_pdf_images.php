<?php

/**
 * Script de test pour vérifier le chargement des images dans PdfTemplateHelper
 * 
 * Usage: php test_pdf_images.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST PDF IMAGES DEBUG ===\n\n";

// Test des chemins
$headerPath = storage_path('app/public/images/bg-entete-page.png');
$footerPath = storage_path('app/public/images/bg-pied-page.png');

echo "1. Chemins des images:\n";
echo "   Header: $headerPath\n";
echo "   Footer: $footerPath\n\n";

// Test existence
echo "2. Vérification existence:\n";
echo "   Header exists: " . (file_exists($headerPath) ? "✅ OUI" : "❌ NON") . "\n";
echo "   Footer exists: " . (file_exists($footerPath) ? "✅ OUI" : "❌ NON") . "\n\n";

// Test taille fichiers
if (file_exists($headerPath)) {
    $headerSize = filesize($headerPath);
    echo "   Header size: " . number_format($headerSize) . " bytes (" . round($headerSize / 1024, 2) . " KB)\n";
}

if (file_exists($footerPath)) {
    $footerSize = filesize($footerPath);
    echo "   Footer size: " . number_format($footerSize) . " bytes (" . round($footerSize / 1024, 2) . " KB)\n";
}

echo "\n3. Test conversion base64:\n";
if (file_exists($headerPath)) {
    $headerData = base64_encode(file_get_contents($headerPath));
    $headerBase64 = "data:image/png;base64," . $headerData;
    echo "   Header base64 length: " . strlen($headerBase64) . " chars\n";
    echo "   Header base64 preview: " . substr($headerBase64, 0, 100) . "...\n";
} else {
    echo "   ❌ Cannot test - header image not found\n";
}

echo "\n4. Test PdfTemplateHelper:\n";
try {
    $helper = new \App\Helpers\PdfTemplateHelper();
    $html = \App\Helpers\PdfTemplateHelper::wrapContent("Test PDF", "<h1>Test</h1>");

    // Check if base64 images are in the output
    $hasHeaderImage = strpos($html, 'data:image/png;base64') !== false;
    $hasFooterImage = substr_count($html, 'data:image/png;base64') >= 2;

    echo "   HTML generated: ✅ YES\n";
    echo "   Contains header image: " . ($hasHeaderImage ? "✅ YES" : "❌ NO") . "\n";
    echo "   Contains footer image: " . ($hasFooterImage ? "✅ YES" : "❌ NO") . "\n";
    echo "   HTML length: " . strlen($html) . " chars\n";

    // Save test HTML
    file_put_contents(storage_path('app/test_pdf_output.html'), $html);
    echo "\n   Test HTML saved to: " . storage_path('app/test_pdf_output.html') . "\n";

} catch (\Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
