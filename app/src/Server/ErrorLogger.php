<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server;

class ErrorLogger
{
    static public function register($app)
    {
        register_shutdown_function(function () use ($app) {
            $errfile = "unknown file";
            $errstr = "shutdown";
            $errno = E_CORE_ERROR;
            $errline = 0;

            $error = error_get_last();

            if ($error !== NULL) {
                $errno = $error["type"];
                $errfile = $error["file"];
                $errline = $error["line"];
                $errstr = $error["message"];
            }

            $message = sprintf('Fatal error: %s at %s line %s', $errstr, $errfile, $errline);
            $app['logger']->critical($message);
        });

        set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($app) {
            $message = sprintf('Error: %s at %s line %s', $errstr, $errfile, $errline);
            $app['logger']->error($message);
            echo "$message\n";
        });

        set_exception_handler(function (\Exception $e) use ($app) {
            $message = sprintf('%s: %s (uncaught exception) at %s line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
            $app['logger']->error($message);
            echo "$message\n";
        });
    }
} 