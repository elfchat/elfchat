/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Check if an element exist in DOM.
 */
$.fn.exist = function () {
    return $(this).length > 0;
};

/**
 * Translate given message.
 * @param {String} message
 * @returns {String}
 */
function tr(message) {
    return window.lang[message] ? window.lang[message] : message;
}

/**
 *
 * @param {String} message
 * @param {Object} params
 * @returns {String}
 */
function format(message, params = {}) {
    var key, value;

    for (key in params) if (params.hasOwnProperty(key)) {
        value = params[key];
        message = message.split('%' + key + '%').join(value);
    }
    return message;
};