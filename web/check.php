<!doctype html>
<htmL>
<head>
    <title>ElfChat Requirement</title>
    <style>
        body {
            margin: 30px 200px;
            font-family: Verdana, Geneva, sans-serif;
        }

        .requirement {
            color: #ff0000;
        }

        .recommendation {
            color: #3749da;
        }

        .done {
            color: #81ac2b;
        }

        a {
            display: inline-block;
            padding: 10px 30px;
            margin: 10px;
            font: inherit;
            text-decoration: none;
            border: solid 1px rgb(33, 134, 34);
            border-radius: 5px;
            color: rgb(255, 255, 255);
            background-color: rgb(40, 182, 44);
        }
    </style>
</head>
<body>
<?php
// Based on Symfony Requirement

// Config
define('REQUIRED_PHP_VERSION', '5.3.9');
define('OPEN_DIR', dirname(dirname(__FILE__)) . '/app/open');
define('UPLOAD_DIR', dirname(dirname(__FILE__)) . '/upload');
define('INSTALLED_PHP_VERSION', phpversion());

requirement(
    version_compare(INSTALLED_PHP_VERSION, REQUIRED_PHP_VERSION, '>='),
    sprintf(
        'You are running PHP version "<strong>%s</strong>", but ElfChat needs at least PHP "<strong>%s</strong>" to run.',
        INSTALLED_PHP_VERSION,
        REQUIRED_PHP_VERSION
    )
);

requirement(
    version_compare(INSTALLED_PHP_VERSION, '5.3.16', '!='),
    'PHP version must not be 5.3.16 as ElfChat won\'t work properly with it'
);

requirement(
    version_compare(INSTALLED_PHP_VERSION, '5.3.4', '>='),
    'You should use at least PHP 5.3.4 due to PHP bug #52083 in earlier versions.'
);

requirement(
    version_compare(INSTALLED_PHP_VERSION, '5.3.8', '>='),
    'You should have at least PHP 5.3.8 due to PHP bug #55156.'
);

requirement(
    is_writable(OPEN_DIR),
    sprintf('%s directory must be writable', OPEN_DIR)
);


requirement(
    is_writable(UPLOAD_DIR),
    sprintf('%s directory must be writable', UPLOAD_DIR)
);

$timezone = ini_get('date.timezone');
requirement(
    !empty($timezone),
    'date.timezone setting must be set in php.ini.'
);

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

requirement(
    extension_loaded('fileinfo'),
    'Install and enable the <strong>Fileinfo</strong> extension.'
);

if (function_exists('apc_store') && ini_get('apc.enabled')) {
    if (version_compare(INSTALLED_PHP_VERSION, '5.4.0', '>=')) {
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

recommendation(
    !!extension_loaded('xdebug'),
    'Disable XDebug on production for performance.'
);

recommendation(
    class_exists('Locale'),
    'Install and enable the <strong>intl</strong> extension.'
);

recommendation(
    version_compare(INSTALLED_PHP_VERSION, '5.4.0', '!='),
    'You should not use PHP 5.4.0 due to the PHP bug #61453.'
);

recommendation(
    version_compare(INSTALLED_PHP_VERSION, '5.4.11', '>=') || version_compare(INSTALLED_PHP_VERSION, '5.4.0', '<'),
    'You should have at least PHP 5.4.11 due to PHP bug #63379.'
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

// Finish

if (Count::$requirements == 0) {
    $configFile = OPEN_DIR . '/config.php';
    if (!file_exists($configFile)) {
        file_put_contents($configFile, '<?php return array();');
    }

    if (Count::$recommendation == 0) {
        echo "<div class='done'>Everything is OK, you can continue with the installation.</div>";
    }

    echo <<<HTML
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script>
    $.getJSON("../install/check.js1").fail(function () {
        $('#server_check').show();
    });
    </script>

    <div id="server_check" style="display: none" class="requirement">Check if you have uploaded '.htaccess' file on server or configured server properly.</div>

    <a id="install" href="../index.php/install">Install</a>
HTML;

}

// Functions

function requirement($if, $text)
{
    if (!$if) {
        print "<div class='requirement'>$text</div>";
        Count::$requirements++;
    }
}

function recommendation($if, $text)
{
    if (!$if) {
        print "<div class='recommendation'>$text</div>";
        Count::$recommendation++;
    }
}

class Count
{
    static $requirements = 0;

    static $recommendation = 0;
}

?>
</body>
</htmL>