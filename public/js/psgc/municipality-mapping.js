// create-municipality-mapping.js
const fs = require('fs');

// Read the processed zip codes
const processedZipCodes = require('./processed_zip_codes.json');

// Create municipality to zip codes mapping
const municipalityMapping = {};

processedZipCodes.forEach(entry => {
  entry.municipality_matches.forEach(municipality => {
    if (!municipalityMapping[municipality]) {
      municipalityMapping[municipality] = [];
    }
    
    if (!municipalityMapping[municipality].includes(entry.zip)) {
      municipalityMapping[municipality].push(entry.zip);
    }
  });
});

// Also add mappings for locations that look like municipalities
processedZipCodes.forEach(entry => {
  entry.locations.forEach(location => {
    // Check if location looks like a municipality (not CPO, not specific addresses)
    if (!location.includes('CPO') && 
        !location.includes('Correspondence') &&
        !location.includes('Development Bank') &&
        !location.includes('Radio') &&
        !location.includes('Bible') &&
        !location.includes('Feblas') &&
        !location.includes('Far Eastern')) {
      
      let cleanLocation = location
        .replace(/\s+CPO$/, '')
        .replace(/\s+Capital$/, '')
        .trim();
      
      if (!municipalityMapping[cleanLocation]) {
        municipalityMapping[cleanLocation] = [];
      }
      
      if (!municipalityMapping[cleanLocation].includes(entry.zip)) {
        municipalityMapping[cleanLocation].push(entry.zip);
      }
      
      // Add city variations
      if (cleanLocation.includes('City') || location.includes('City')) {
        const cityName = cleanLocation.replace(/\s+City$/, '').trim();
        if (cityName && !municipalityMapping[cityName]) {
          municipalityMapping[cityName] = [];
        }
        if (cityName && !municipalityMapping[cityName].includes(entry.zip)) {
          municipalityMapping[cityName].push(entry.zip);
        }
      }
    }
  });
});

// Save to file
fs.writeFileSync(
  'municipality_zip_mapping.json',
  JSON.stringify(municipalityMapping, null, 2)
);

console.log(`Created municipality_zip_mapping.json with ${Object.keys(municipalityMapping).length} municipalities`);