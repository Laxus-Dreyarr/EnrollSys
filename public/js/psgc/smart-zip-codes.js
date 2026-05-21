// smart-zip-codes.js - UPDATED WITH FALLBACK (COMPLETE VERSION)
class SmartZipCodes {
    constructor() {
        this.zipCodes = [];
        this.municipalityMapping = {};
        this.zipCodeDetails = {};
        this.loaded = false;
    }

    async load() {
        try {
            // Try to load processed zip codes
            const zipData = await fetch('/js/psgc/processed_zip_codes.json')
                .then(r => r.json())
                .catch(() => {
                    console.warn('processed_zip_codes.json not found, using fallback');
                    return this.createFallbackZipData();
                });
            
            this.zipCodes = zipData;
            
            // Try to load municipality mapping
            try {
                const mapping = await fetch('/js/psgc/municipality_zip_mapping.json')
                    .then(r => r.json());
                
                // Check if mapping is in wrong format (zip->location)
                if (this.isWrongFormat(mapping)) {
                    console.warn('Mapping is in wrong format, converting...');
                    this.municipalityMapping = this.convertToCorrectFormat(mapping);
                } else {
                    this.municipalityMapping = mapping;
                }
            } catch (error) {
                console.warn('municipality_zip_mapping.json not found or error, creating from zip data');
                this.municipalityMapping = this.createMappingFromZipData(zipData);
            }
            
            // Create a map for quick zip code lookup
            this.zipCodeDetails = {};
            zipData.forEach(zipEntry => {
                this.zipCodeDetails[zipEntry.zip] = zipEntry;
            });
            
            this.loaded = true;
            
            console.log(`Loaded ${zipData.length} zip codes and mapping for ${Object.keys(this.municipalityMapping).length} municipalities`);
            console.log('Sample mapping:', Object.keys(this.municipalityMapping).slice(0, 5));
        } catch (error) {
            console.error('Error loading zip codes:', error);
            // Load fallback data
            this.loadFallbackData();
        }
    }

    isWrongFormat(mapping) {
        // Check if the first key looks like a zip code (all digits)
        const firstKey = Object.keys(mapping)[0];
        return /^\d+$/.test(firstKey);
    }

    convertToCorrectFormat(wrongFormatMapping) {
        const correctFormat = {};
        
        Object.entries(wrongFormatMapping).forEach(([zipCode, location]) => {
            if (Array.isArray(location)) {
                location.forEach(loc => {
                    const cleanLoc = this.cleanLocationName(loc);
                    if (!correctFormat[cleanLoc]) {
                        correctFormat[cleanLoc] = [];
                    }
                    if (!correctFormat[cleanLoc].includes(zipCode)) {
                        correctFormat[cleanLoc].push(zipCode);
                    }
                });
            } else {
                const cleanLoc = this.cleanLocationName(location);
                if (!correctFormat[cleanLoc]) {
                    correctFormat[cleanLoc] = [];
                }
                if (!correctFormat[cleanLoc].includes(zipCode)) {
                    correctFormat[cleanLoc].push(zipCode);
                }
            }
        });
        
        return correctFormat;
    }

    cleanLocationName(location) {
        return location
            .replace(/\s+CPO.*$/i, '')
            .replace(/\s+-.*$/i, '')
            .replace(/\s+Capital$/i, '')
            .replace(/^City of /i, '')
            .replace(/^City Of /i, '')
            .trim();
    }

    createMappingFromZipData(zipData) {
        const mapping = {};
        
        zipData.forEach(entry => {
            entry.locations.forEach(location => {
                const cleanLocation = this.cleanLocationName(location);
                if (!mapping[cleanLocation]) {
                    mapping[cleanLocation] = [];
                }
                if (!mapping[cleanLocation].includes(entry.zip)) {
                    mapping[cleanLocation].push(entry.zip);
                }
                
                // Add city variations
                if (cleanLocation.includes('City')) {
                    const cityName = cleanLocation.replace(/\s+City$/i, '').trim();
                    if (cityName && cityName !== cleanLocation) {
                        if (!mapping[cityName]) {
                            mapping[cityName] = [];
                        }
                        if (!mapping[cityName].includes(entry.zip)) {
                            mapping[cityName].push(entry.zip);
                        }
                    }
                }
            });
        });
        
        return mapping;
    }

    createFallbackZipData() {
        // Create basic zip data structure from common knowledge
        return [
            { zip: "6541", locations: ["Ormoc City"], municipality_matches: ["Ormoc", "Ormoc City"] },
            { zip: "6521", locations: ["Baybay"], municipality_matches: ["Baybay"] },
            { zip: "1550", locations: ["Mandaluyong CPO"], municipality_matches: ["Mandaluyong"] }
            // Add more as needed
        ];
    }

    loadFallbackData() {
        console.log('Loading fallback data');
        this.zipCodes = this.createFallbackZipData();
        this.municipalityMapping = this.createMappingFromZipData(this.zipCodes);
        this.loaded = true;
    }

    // ========== ORIGINAL METHODS ==========
    
    // Get all zip codes for a municipality (smart matching)
    getZipCodesForMunicipality(municipalityName, isCity = false) {
        if (!this.loaded) {
            console.warn('SmartZipCodes not loaded yet');
            return [];
        }

        const matches = new Set();
        
        // Clean municipality name
        const cleanName = municipalityName.trim();
        
        // Try different matching strategies
        this.tryDirectMapping(cleanName, matches);
        
        // If it's a city, try city variations
        if (isCity) {
            this.tryCityVariations(cleanName, matches);
        }
        
        // Try fuzzy matching if still no matches
        if (matches.size === 0) {
            this.tryFuzzyMatching(cleanName, matches);
        }
        
        // Try removing common suffixes
        if (matches.size === 0) {
            const nameWithoutCommon = this.removeCommonSuffixes(cleanName);
            if (nameWithoutCommon !== cleanName) {
                this.tryDirectMapping(nameWithoutCommon, matches);
                if (isCity) {
                    this.tryCityVariations(nameWithoutCommon, matches);
                }
            }
        }
        
        return Array.from(matches).sort();
    }

    tryDirectMapping(name, matchesSet) {
        // Direct mapping lookup
        if (this.municipalityMapping[name]) {
            console.log(`Direct mapping found for ${name}:`, this.municipalityMapping[name]);
            this.municipalityMapping[name].forEach(zip => matchesSet.add(zip));
        } else {
            console.log(`No direct mapping for ${name}`);
        }
    }

    tryCityVariations(name, matchesSet) {
        const variations = [
            name,
            `${name} City`,
            `City of ${name}`,
            `City Of ${name}`,
            `City of ${name} Capital`,
            `City Of ${name} Capital`,
            `${name} City Capital`
        ];
        
        console.log(`Trying city variations for ${name}:`, variations);
        
        variations.forEach(variation => {
            if (this.municipalityMapping[variation]) {
                console.log(`Found mapping for variation ${variation}:`, this.municipalityMapping[variation]);
                this.municipalityMapping[variation].forEach(zip => matchesSet.add(zip));
            }
        });
        
        // Also check without common city prefixes
        const withoutPrefix = name.replace(/^City of /i, '').replace(/^City Of /i, '');
        if (withoutPrefix !== name) {
            console.log(`Trying without prefix: ${withoutPrefix}`);
            this.tryDirectMapping(withoutPrefix, matchesSet);
            this.tryDirectMapping(`${withoutPrefix} City`, matchesSet);
        }
    }

    tryFuzzyMatching(name, matchesSet) {
        const nameLower = name.toLowerCase();
        
        console.log(`Trying fuzzy matching for: ${name}`);
        
        // Look through all zip codes for matches
        this.zipCodes.forEach(zipEntry => {
            // Check in municipality_matches
            const foundInMatches = zipEntry.municipality_matches.some(match => {
                const matchLower = match.toLowerCase();
                return matchLower === nameLower || 
                       matchLower.includes(nameLower) || 
                       nameLower.includes(matchLower.replace(' city', '').replace(' municipality', ''));
            });
            
            if (foundInMatches) {
                console.log(`Fuzzy match found in municipality_matches: ${zipEntry.zip}`);
                matchesSet.add(zipEntry.zip);
                return;
            }
            
            // Check in locations
            const foundInLocations = zipEntry.locations.some(location => {
                const locLower = location.toLowerCase();
                return locLower === nameLower ||
                       locLower.includes(nameLower) ||
                       nameLower.includes(locLower.replace(' city', '').replace(' municipality', ''));
            });
            
            if (foundInLocations) {
                console.log(`Fuzzy match found in locations: ${zipEntry.zip}`);
                matchesSet.add(zipEntry.zip);
            }
        });
    }

    removeCommonSuffixes(name) {
        return name
            .replace(/\s+\(City\)$/i, '')
            .replace(/\s+City$/i, '')
            .replace(/^City of /i, '')
            .replace(/\s+Municipality$/i, '');
    }

    // Get zip code details
    getZipDetails(zipCode) {
        return this.zipCodeDetails[zipCode];
    }

    // Search zip codes by any term
    searchZipCodes(searchTerm) {
        if (!searchTerm) return [];
        
        const term = searchTerm.toLowerCase();
        const results = [];
        
        this.zipCodes.forEach(zipEntry => {
            // Search in zip code
            if (zipEntry.zip.includes(term)) {
                results.push({...zipEntry, matchType: 'zip_code'});
                return;
            }
            
            // Search in locations
            const locationMatches = zipEntry.locations.filter(loc => 
                loc.toLowerCase().includes(term)
            );
            
            if (locationMatches.length > 0) {
                results.push({
                    ...zipEntry,
                    matchedLocations: locationMatches,
                    matchType: 'location'
                });
            }
        });
        
        return results;
    }

    // Additional helper method to check what's available for debugging
    debugMunicipality(municipalityName) {
        console.log('=== DEBUG Municipality ===');
        console.log('Municipality:', municipalityName);
        console.log('In mapping:', this.municipalityMapping[municipalityName]);
        
        // Find all keys that might match
        const matchingKeys = Object.keys(this.municipalityMapping).filter(key => 
            key.toLowerCase().includes(municipalityName.toLowerCase()) ||
            municipalityName.toLowerCase().includes(key.toLowerCase().replace(' city', ''))
        );
        
        console.log('Possible matching keys:', matchingKeys);
        matchingKeys.forEach(key => {
            console.log(`  ${key}:`, this.municipalityMapping[key]);
        });
    }
}

// Create global instance
window.smartZipCodes = new SmartZipCodes();