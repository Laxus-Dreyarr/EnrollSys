<?php
// database/scripts/process-zip-codes.php

// Run from command line: php database/scripts/process-zip-codes.php

require __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Processing zip codes...\n";

// Paths
$publicPath = __DIR__ . '/../../public/js/psgc/';
$zipRawFile = $publicPath . 'zip_codes_raw.json';

// Check if raw zip file exists
if (!file_exists($zipRawFile)) {
    echo "Error: zip_codes_raw.json not found in {$publicPath}\n";
    echo "Please download it first and place it in public/js/psgc/\n";
    exit(1);
}

// Load raw zip data
$zipData = json_decode(file_get_contents($zipRawFile), true);
$processed = [];
$mapping = [];

foreach ($zipData as $zip => $location) {
    $entry = [
        'zip' => (string)$zip,
        'locations' => is_array($location) ? $location : [$location],
        'type' => 'unknown',
        'municipality_matches' => []
    ];
    
    // Determine type
    foreach ($entry['locations'] as $loc) {
        $locLower = strtolower($loc);
        
        if (strpos($locLower, 'city') !== false) {
            $entry['type'] = 'city';
            break;
        } elseif (strpos($locLower, 'municipality') !== false) {
            $entry['type'] = 'municipality';
            break;
        } elseif (strpos($locLower, 'barangay') !== false || 
                  strpos($locLower, 'district') !== false ||
                  strpos($locLower, 'cpo') !== false) {
            $entry['type'] = 'area';
        }
    }
    
    // Extract municipality names for matching
    foreach ($entry['locations'] as $loc) {
        // Remove common suffixes
        $municipalityName = preg_replace('/\s*(City|Municipality|Town|Capital|CPO|District)$/i', '', $loc);
        $municipalityName = preg_replace('/^City of /i', '', $municipalityName);
        $municipalityName = preg_replace('/^City Of /i', '', $municipalityName);
        $municipalityName = preg_replace('/\s*-\s*.+$/', '', $municipalityName);
        $municipalityName = trim($municipalityName);
        
        if ($municipalityName && !in_array($municipalityName, $entry['municipality_matches'])) {
            $entry['municipality_matches'][] = $municipalityName;
            
            // Build mapping
            if (!isset($mapping[$municipalityName])) {
                $mapping[$municipalityName] = [];
            }
            if (!in_array($entry['zip'], $mapping[$municipalityName])) {
                $mapping[$municipalityName][] = $entry['zip'];
            }
        }
    }
    
    $processed[] = $entry;
}

// Save processed data
file_put_contents($publicPath . 'processed_zip_codes.json', json_encode($processed, JSON_PRETTY_PRINT));
file_put_contents($publicPath . 'municipality_zip_mapping.json', json_encode($mapping, JSON_PRETTY_PRINT));

echo "✓ Processed " . count($processed) . " zip codes\n";
echo "✓ Created mapping for " . count($mapping) . " municipalities\n";
echo "✓ Files saved to: " . $publicPath . "\n";