// create-processed-zip.js
const fs = require('fs');

// Read your original zipcodes.json
const originalZipData = require('./zipcodes.json');

// Process it to create the structure we need
const processedZipCodes = [];

// This function extracts municipality names from location strings
function extractMunicipalities(location) {
  const municipalities = [];
  
  // Remove common suffixes
  let cleanName = location
    .replace(/\s+CPO$/, '')
    .replace(/\s+Capital$/, '')
    .replace(/\s+City$/, '')
    .replace(/^City of /i, '')
    .replace(/^City Of /i, '')
    .trim();
  
  // Add the clean name
  municipalities.push(cleanName);
  
  // If it contains "City" in the original, add city variations
  if (location.includes('City') || location.includes('city')) {
    municipalities.push(`${cleanName} City`);
    municipalities.push(`City of ${cleanName}`);
  }
  
  return municipalities;
}

// Process each zip code entry
Object.entries(originalZipData).forEach(([zipCode, location]) => {
  const entry = {
    zip: zipCode,
    locations: [],
    municipality_matches: []
  };
  
  // Handle both string and array locations
  if (Array.isArray(location)) {
    entry.locations = location;
    location.forEach(loc => {
      entry.municipality_matches.push(...extractMunicipalities(loc));
    });
  } else {
    entry.locations = [location];
    entry.municipality_matches = extractMunicipalities(location);
  }
  
  // Remove duplicates
  entry.locations = [...new Set(entry.locations)];
  entry.municipality_matches = [...new Set(entry.municipality_matches)];
  
  processedZipCodes.push(entry);
});

// Save to file
fs.writeFileSync(
  'processed_zip_codes.json',
  JSON.stringify(processedZipCodes, null, 2)
);

console.log(`Created processed_zip_codes.json with ${processedZipCodes.length} entries`);