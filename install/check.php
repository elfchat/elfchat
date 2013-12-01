<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('REQUIRED_PHP_VERSION', '5.3.3');

$installedPhpVersion = phpversion();

/**
 * Requirement
 */

requirement(
    version_compare($installedPhpVersion, REQUIRED_PHP_VERSION, '>='),
    sprintf(
        'You are running PHP version "<strong>%s</strong>", but ElfChat needs at least PHP "<strong>%s</strong>" to run.',
        $installedPhpVersion,
        REQUIRED_PHP_VERSION
    )
);

requirement(
    version_compare($installedPhpVersion, '5.3.16', '!='),
    'PHP version must not be 5.3.16 as ElfChat won\'t work properly with it'
);

$open = dirname(__DIR__) . '/open';
requirement(
    is_writable($open),
    sprintf('%s directory must be writable', $open)
);

phpIniRequirement(
    'date.timezone', true, false,
    'date.timezone setting must be set');

requirement(
    function_exists('json_encode'),
    'Install and enable the <strong>JSON</strong> extension.'
);

requirement(
    function_exists('session_start'),
    'Install and enable the <strong>session</strong> extension.'
);

requirement(
    function_exists('ctype_alpha'),
    'Install and enable the <strong>ctype</strong> extension.'
);

requirement(
    function_exists('token_get_all'),
    'Install and enable the <strong>Tokenizer</strong> extension.'
);

if (function_exists('apc_store') && ini_get('apc.enabled')) {
    if (version_compare($installedPhpVersion, '5.4.0', '>=')) {
        requirement(
            version_compare(phpversion('apc'), '3.1.13', '>='),
            'APC version must be at least 3.1.13 when using PHP 5.4'
        );
    } else {
        requirement(
            version_compare(phpversion('apc'), '3.0.17', '>='),
            'APC version must be at least 3.0.17'
        );
    }
}

requirement(
    function_exists('mb_strlen'),
    'Install and enable the <strong>mbstring</strong> extension.'
);

requirement(
    function_exists('iconv'),
    'Install and enable the <strong>iconv</strong> extension.'
);

requirement(
    class_exists('PDO'),
    'Install <strong>PDO</strong>.'
);

if (class_exists('PDO')) {
    $drivers = PDO::getAvailableDrivers();
    requirement(
        count($drivers),
        'Install <strong>PDO drivers</strong>.'
    );
}

/**
 * Recommendations
 */

recommendation(
    class_exists('Locale'),
    'Install and enable the <strong>intl</strong> extension.'
);

$accelerator =
    (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable'))
    ||
    (extension_loaded('apc') && ini_get('apc.enabled'))
    ||
    (extension_loaded('Zend Optimizer+') && ini_get('zend_optimizerplus.enable'))
    ||
    (extension_loaded('Zend OPcache') && ini_get('opcache.enable'))
    ||
    (extension_loaded('xcache') && ini_get('xcache.cacher'))
    ||
    (extension_loaded('wincache') && ini_get('wincache.ocenabled'));

recommendation(
    $accelerator,
    'Install and enable a <strong>PHP accelerator</strong> like APC (highly recommended).'
);

phpIniRecommendation('short_open_tag', false);

phpIniRecommendation('short_open_tag', false);

phpIniRecommendation('magic_quotes_gpc', false, true);

phpIniRecommendation('register_globals', false, true);

phpIniRecommendation('session.auto_start', false);


include __DIR__ . '/views/header.php';
include __DIR__ . '/views/check.php';
include __DIR__ . '/views/footer.php';