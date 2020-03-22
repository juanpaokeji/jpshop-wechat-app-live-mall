<?php

namespace tools\pay;
use tools\pay\Handlers\CsvHandler;
use tools\pay\Handlers\FormHandler;
use tools\pay\Handlers\JsonHandler;
use tools\pay\Handlers\XmlHandler;

include __DIR__ . DIRECTORY_SEPARATOR .'Mime.php';
include __DIR__ . DIRECTORY_SEPARATOR .'Httpful.php';
include __DIR__ . DIRECTORY_SEPARATOR .'Request.php';
include __DIR__ . DIRECTORY_SEPARATOR . 'Handlers' . DIRECTORY_SEPARATOR . 'JsonHandler.php';
include __DIR__ . DIRECTORY_SEPARATOR . 'Handlers' . DIRECTORY_SEPARATOR . 'FormHandler.php';
include __DIR__ . DIRECTORY_SEPARATOR . 'Handlers' . DIRECTORY_SEPARATOR . 'XmlHandler.php';
include __DIR__ . DIRECTORY_SEPARATOR . 'Handlers' . DIRECTORY_SEPARATOR . 'XHtmlHandler.php';
include __DIR__ . DIRECTORY_SEPARATOR . 'Handlers' . DIRECTORY_SEPARATOR . 'CsvHandler.php';

/**
 * Bootstrap class that facilitates autoloading.  A naive
 * PSR-0 autoloader.
 *
 * @author Nate Good <me@nategood.com>
 */
class Bootstrap
{

    const DIR_GLUE = DIRECTORY_SEPARATOR;
    const NS_GLUE = '\\';

    public static $registered = false;

    /**
     * Register the autoloader and any other setup needed
     */
    public static function init()
    {
        spl_autoload_register(array('\tools\pay\Bootstrap', 'autoload'));
        self::registerHandlers();
    }

    /**
     * The autoload magic (PSR-0 style)
     *
     * @param string $classname
     */
    public static function autoload($classname)
    {
        self::_autoload(dirname(dirname(__FILE__)), $classname);
    }

    /**
     * Register the autoloader and any other setup needed
     */
    public static function pharInit()
    {
        spl_autoload_register(array('\tools\pay\Bootstrap', 'pharAutoload'));
        self::registerHandlers();
    }

    /**
     * Phar specific autoloader
     *
     * @param string $classname
     */
    public static function pharAutoload($classname)
    {
        self::_autoload('phar://httpful.phar', $classname);
    }

    /**
     * @param string $base
     * @param string $classname
     */
    private static function _autoload($base, $classname)
    {
        $parts      = explode(self::NS_GLUE, $classname);
        $path       = $base . self::DIR_GLUE . implode(self::DIR_GLUE, $parts) . '.php';

        if (file_exists($path)) {
            require_once($path);
        }
    }
    /**
     * Register default mime handlers.  Is idempotent.
     */
    public static function registerHandlers()
    {
        if (self::$registered === true) {
            return;
        }

        // @todo check a conf file to load from that instead of
        // hardcoding into the library?
        $handlers = array(
            Mime::JSON => new JsonHandler(),
            Mime::XML  => new XmlHandler(),
            Mime::FORM => new FormHandler(),
            Mime::CSV  => new CsvHandler()
        );

        foreach ($handlers as $mime => $handler) {
            // Don't overwrite if the handler has already been registered
            if (Httpful::hasParserRegistered($mime))
                continue;
            Httpful::register($mime, $handler);
        }

        self::$registered = true;
    }
}
