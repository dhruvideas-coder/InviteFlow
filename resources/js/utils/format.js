/**
 * Formats a mobile number based on country.
 * Indian numbers (+91) are formatted as +91 XXXXX XXXXX.
 * 
 * @param {string} mobile 
 * @returns {string}
 */
export function formatMobile(mobile) {
    if (!mobile) return '';

    // Remove all non-numeric characters except +
    let cleaned = String(mobile).replace(/[^\d+]/g, '');

    // Handle Indian numbers (+91)
    // Common inputs: +919876543210, 919876543210, 9876543210
    if (cleaned.startsWith('+91') || cleaned.startsWith('91') || (cleaned.length === 10 && /^\d{10}$/.test(cleaned))) {
        let digits = cleaned;
        if (digits.startsWith('+91')) digits = digits.substring(3);
        else if (digits.startsWith('91') && digits.length > 10) digits = digits.substring(2);
        
        if (digits.length === 10) {
            return `+91 ${digits.substring(0, 5)} ${digits.substring(5)}`;
        }
    }

    // For other countries, if it starts with +, try to format as +XX XXXXX...
    if (cleaned.startsWith('+')) {
        // We don't have a full library, so we'll do a simple space after first 2 or 3 digits if it looks like a country code
        // US/Canada: +1 ..., UK: +44 ..., etc.
        if (cleaned.startsWith('+1')) {
            return `+1 ${cleaned.substring(2)}`;
        }
        if (cleaned.length > 4) {
            return `${cleaned.substring(0, 3)} ${cleaned.substring(3)}`;
        }
        return cleaned;
    }

    // Default: just return as is but maybe with some spacing if it's long
    return cleaned;
}
