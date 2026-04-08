/**
 * Formats the price for display.
 * - Uses commas as thousand separators by default.
 * - For large numbers (>= 1 billion or 1 million), uses abbreviations like "tỷ" or "triệu".
 * 
 * @param price The price to format.
 * @param currency The currency (e.g., "VND", "USD").
 * @returns Formatted price string.
 */
export const formatPrice = (price: number | string | null, currency: string = 'VND'): string => {
  if (price === null || price === undefined) return '';
  
  const numPrice = typeof price === 'string' ? parseFloat(price) : price;
  if (isNaN(numPrice)) return String(price);

  // If currency is not VND, use standard toLocaleString
  if (currency.toUpperCase() !== 'VND') {
    return `${currency} ${numPrice.toLocaleString()}`;
  }

  // Vietnamese price formatting (tỷ/triệu)
  if (numPrice >= 1000000000) {
    const billion = Math.floor(numPrice / 1000000000);
    const million = Math.floor((numPrice % 1000000000) / 1000000);
    if (million > 0) {
      return `~ ${billion} tỷ ${million}`;
    }
    return `${billion} tỷ`;
  } else if (numPrice >= 1000000) {
    const million = Math.floor(numPrice / 1000000);
    return `${million} triệu`;
  }

  // Fallback for small numbers: use thousand separator (comma)
  // For VND, usually uses dots, but user specifically asked for commas.
  return numPrice.toLocaleString('en-US'); // en-US uses commas for thousands
};
