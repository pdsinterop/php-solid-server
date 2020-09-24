<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Start string buffer?
// Start session?
// Anything else?

/*/ Polyfill for PHP7.1 /*/
defined('PHP_SAPI') OR define('PHP_SAPI', php_sapi_name());

if (PHP_SAPI === 'cli-server') {

    file_put_contents(
        'php://stdout',
        vsprintf("[%s] %s:%s %s %s\n", [
            date('D M d h:i:s Y', $_SERVER['REQUEST_TIME']),
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['REMOTE_PORT'],
            $_SERVER['REQUEST_METHOD'],
            $_SERVER["REQUEST_URI"],
        ])
    );

    if (is_file(__DIR__.'/../web'.$_SERVER['REQUEST_URI'])) {
        // Allow the server to serve the requested resource as-is.
        return false;
    }
}
