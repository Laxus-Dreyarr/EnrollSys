<?php

namespace App\Helpers;

class PSGC
{
    /**
     * Get all regions
     */
    public static function getRegions()
    {
        $path = public_path('js/psgc/regions.json');
        return file_exists($path) ? json_decode(file_get_contents($path), true) : [];
    }

    /**
     * Get provinces by region designation
     */
    public static function getProvincesByRegion($regionDesignation)
    {
        $path = public_path('js/psgc/provinces.json');
        $provinces = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
        
        return array_values(array_filter($provinces, function($province) use ($regionDesignation) {
            return $province['region'] === $regionDesignation;
        }));
    }

    /**
     * Get municipalities by province
     */
    public static function getMunicipalitiesByProvince($provinceName)
    {
        $path = public_path('js/psgc/municipalities.json');
        $municipalities = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
        
        return array_values(array_filter($municipalities, function($municipality) use ($provinceName) {
            return $municipality['province'] === $provinceName;
        }));
    }

    /**
     * Get zip codes for a municipality
     */
    public static function getZipCodesForMunicipality($municipalityName, $isCity = false)
    {
        $path = public_path('js/psgc/municipality_zip_mapping.json');
        $mapping = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
        
        $zipCodes = [];
        
        // Check direct mapping
        if (isset($mapping[$municipalityName])) {
            $zipCodes = array_merge($zipCodes, $mapping[$municipalityName]);
        }
        
        // Check city variations if it's a city
        if ($isCity) {
            $variations = [
                $municipalityName,
                $municipalityName . ' City',
                'City of ' . $municipalityName,
                'City Of ' . $municipalityName
            ];
            
            foreach ($variations as $variation) {
                if (isset($mapping[$variation]) && $variation !== $municipalityName) {
                    $zipCodes = array_merge($zipCodes, $mapping[$variation]);
                }
            }
        }
        
        return array_unique($zipCodes);
    }

    /**
     * Get all zip codes
     */
    public static function getAllZipCodes()
    {
        $path = public_path('js/psgc/processed_zip_codes.json');
        return file_exists($path) ? json_decode(file_get_contents($path), true) : [];
    }

    /**
     * Search zip codes
     */
    public static function searchZipCodes($searchTerm)
    {
        $zipCodes = self::getAllZipCodes();
        $results = [];
        
        foreach ($zipCodes as $entry) {
            // Search in zip code
            if (strpos($entry['zip'], $searchTerm) !== false) {
                $results[] = $entry;
                continue;
            }
            
            // Search in locations
            foreach ($entry['locations'] as $location) {
                if (stripos($location, $searchTerm) !== false) {
                    $results[] = $entry;
                    break;
                }
            }
        }
        
        return $results;
    }
}