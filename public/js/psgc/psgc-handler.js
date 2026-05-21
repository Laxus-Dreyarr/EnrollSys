class PSGC {
    constructor() {
        this.data = {
            regions: [],
            provinces: [],
            municipalities: [],
            barangays: []
        };
        
        this.municipalityToBarangays = new Map(); // Cache for faster lookup
        
        // Load all data initially
        this.loadData();
    }

    async loadData() {
        try {
            const basePath = '/js/psgc/';
            
            // Load all JSON files
            const [regions, provinces, municipalities, barangays] = await Promise.all([
                fetch(`${basePath}regions.json`).then(r => r.json()),
                fetch(`${basePath}provinces.json`).then(r => r.json()),
                fetch(`${basePath}municipalities.json`).then(r => r.json()),
                fetch(`${basePath}barangays.json`).then(r => r.json())
            ]);

            // Process municipalities to add proper display names and lookup variations
            const processedMunicipalities = municipalities.map(municipality => {
                const variations = this.generateCityNameVariations(municipality.name, municipality.city);
                
                return {
                    ...municipality,
                    displayName: municipality.city ? `${municipality.name} (City)` : municipality.name,
                    nameVariations: variations
                };
            });

            // Create a reverse mapping from citymun to municipality
            const citymunToMunicipality = this.createCitymunMapping(processedMunicipalities, barangays);
            
            this.data = { 
                regions, 
                provinces, 
                municipalities: processedMunicipalities,
                barangays 
            };
            
            this.citymunToMunicipality = citymunToMunicipality;
            this.buildMunicipalityToBarangaysCache(barangays);
            
            console.log('PSGC data loaded successfully');
            console.log('Citymun to Municipality mapping created:', citymunToMunicipality.size, 'entries');
        } catch (error) {
            console.error('Error loading PSGC data:', error);
        }
    }

    // Generate all possible name variations for a city
    generateCityNameVariations(name, isCity) {
        const variations = new Set();
        
        if (!isCity) {
            variations.add(name);
            return Array.from(variations);
        }
        
        // Add the base name
        variations.add(name);
        
        // Common variations found in barangays.json
        variations.add(`${name} City`);
        variations.add(`${name} City Capital`);
        variations.add(`City of ${name}`);
        variations.add(`City Of ${name}`);
        variations.add(`City of ${name} Capital`);
        variations.add(`City Of ${name} Capital`);
        
        // Special cases for known variations
        if (name === "Manila") {
            variations.add("City of Manila");
            variations.add("City Of Manila");
            variations.add("Manila Capital");
        }
        
        // Convert to lowercase for case-insensitive matching
        const lowerVariations = new Set();
        variations.forEach(variation => {
            lowerVariations.add(variation.toLowerCase());
        });
        
        return Array.from(variations);
    }

    // Create mapping from barangay citymun to municipality
    createCitymunMapping(municipalities, barangays) {
        const map = new Map();
        
        // First, get all unique citymun values from barangays
        const uniqueCityMuns = [...new Set(barangays.map(b => b.citymun))];
        
        // For each unique citymun, find matching municipalities
        uniqueCityMuns.forEach(citymun => {
            const citymunLower = citymun.toLowerCase();
            
            // Try to find municipality by matching variations
            const matchingMunicipalities = municipalities.filter(municipality => {
                // If municipality is not a city, only match exact names
                if (!municipality.city) {
                    return municipality.name.toLowerCase() === citymunLower;
                }
                
                // For cities, check all variations
                return municipality.nameVariations.some(variation => 
                    variation.toLowerCase() === citymunLower
                );
            });
            
            if (matchingMunicipalities.length > 0) {
                map.set(citymun, matchingMunicipalities[0]);
            } else {
                // Try fuzzy matching for cities
                const cityMunicipalities = municipalities.filter(m => m.city);
                const fuzzyMatch = cityMunicipalities.find(municipality => {
                    // Check if citymun contains municipality name or vice versa
                    const muniNameLower = municipality.name.toLowerCase();
                    return citymunLower.includes(muniNameLower) || 
                           muniNameLower.includes(citymunLower.replace(' city', '').replace(' city capital', '').replace('city of ', '').replace('city of', ''));
                });
                
                if (fuzzyMatch) {
                    map.set(citymun, fuzzyMatch);
                }
            }
        });
        
        return map;
    }

    // Build cache for faster barangay lookup
    buildMunicipalityToBarangaysCache(barangays) {
        barangays.forEach(barangay => {
            const municipality = this.citymunToMunicipality.get(barangay.citymun);
            if (municipality) {
                const key = municipality.name;
                if (!this.municipalityToBarangays.has(key)) {
                    this.municipalityToBarangays.set(key, []);
                }
                this.municipalityToBarangays.get(key).push(barangay);
            }
        });
    }

    // Region Methods
    regions = {
        all: () => this.data.regions,
        find: (name) => this.data.regions.find(r => r.name === name),
        filter: (query) => this.data.regions.filter(r => 
            r.name.toLowerCase().includes(query.toLowerCase())
        )
    };

    // Province Methods
    provinces = {
        all: () => this.data.provinces,
        find: (name) => this.data.provinces.find(p => p.name === name),
        filter: (query) => this.data.provinces.filter(p => 
            p.name.toLowerCase().includes(query.toLowerCase())
        ),
        findByRegion: (regionDesignation) => this.data.provinces.filter(p => 
            p.region === regionDesignation
        )
    };

    // Municipality Methods
    municipalities = {
        all: () => this.data.municipalities,
        find: (name) => this.data.municipalities.find(m => m.name === name),
        findByCitymun: (citymun) => {
            // Try direct mapping first
            if (this.citymunToMunicipality.has(citymun)) {
                return this.citymunToMunicipality.get(citymun);
            }
            
            // Try to find by name variations
            const citymunLower = citymun.toLowerCase();
            return this.data.municipalities.find(municipality => {
                if (!municipality.city) {
                    return municipality.name.toLowerCase() === citymunLower;
                }
                return municipality.nameVariations.some(variation => 
                    variation.toLowerCase() === citymunLower
                );
            });
        },
        filter: (query) => this.data.municipalities.filter(m => 
            m.name.toLowerCase().includes(query.toLowerCase())
        ),
        findByProvince: (provinceName) => this.data.municipalities.filter(m => 
            m.province === provinceName
        ),
        getDisplayName: (municipality) => {
            if (!municipality) return '';
            return municipality.displayName || municipality.name;
        }
    };

    // Barangay Methods - ULTIMATE FIXED VERSION
    barangays = {
        all: () => this.data.barangays,
        find: (name) => this.data.barangays.find(b => b.name === name),
        filter: (query) => this.data.barangays.filter(b => 
            b.name.toLowerCase().includes(query.toLowerCase())
        ),
        // BEST METHOD: Using cached mapping
        findByMunicipality: (municipalityName) => {
            if (this.municipalityToBarangays.has(municipalityName)) {
                return this.municipalityToBarangays.get(municipalityName);
            }
            return [];
        },
        // Alternative: Find by municipality object
        findByMunicipalityObj: (municipality) => {
            if (!municipality) return [];
            return this.barangays.findByMunicipality(municipality.name);
        },
        // Find by citymun string directly
        findByCitymun: (citymun) => {
            return this.data.barangays.filter(b => b.citymun === citymun);
        },
        // Find all barangays for a city (fuzzy matching)
        findByCity: (cityName) => {
            // First, find the municipality
            const municipality = this.municipalities.find(cityName);
            if (!municipality || !municipality.city) {
                return [];
            }
            
            // Get all possible citymun variations for this city
            const allCityMuns = [...this.citymunToMunicipality.entries()]
                .filter(([citymun, muni]) => muni.name === municipality.name)
                .map(([citymun]) => citymun);
            
            // Get barangays from all matching citymuns
            let allBarangays = [];
            allCityMuns.forEach(citymun => {
                const barangays = this.data.barangays.filter(b => b.citymun === citymun);
                allBarangays = [...allBarangays, ...barangays];
            });
            
            return allBarangays;
        }
    };
}

// Create global instance
window.psgc = new PSGC();