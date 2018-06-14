import Vue from 'vue'
import Accounting from 'accounting'

/**
 * Formats a value as a currency value.
 */
Vue.filter('currency', value => {
    let precision = 2;
    let floatValue = parseFloat(value);

    // Auto precision
    if(Math.round(floatValue) === floatValue) {
        precision = 0;
    }

    return Accounting.formatMoney(floatValue, '$', precision);
})

/**
 * Translate filter.
 */
Vue.filter('t', value => {
    return value;
})

/**
 * FormatNumber.
 */
Vue.filter('formatNumber', (number, precision, thousand, decimal) => {
    return Accounting.formatNumber(number, precision, thousand, decimal);
})
