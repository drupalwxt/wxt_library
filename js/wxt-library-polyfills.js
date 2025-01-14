// Add `isArray` polyfill for Zepto
if (typeof Zepto !== 'undefined' && typeof Zepto.isArray !== 'function') {
    Zepto.isArray = Array.isArray;
}

// Add `isArray` polyfill for jQuery
if (typeof jQuery !== 'undefined' && typeof jQuery.isArray !== 'function') {
    jQuery.isArray = Array.isArray;
}

