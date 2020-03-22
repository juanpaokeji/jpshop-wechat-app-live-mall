<?php

namespace tools\pay;

use tools\pay\Handlers\MimeHandlerAdapter;

class Httpful {
    const VERSION = '0.2.20';

    private static $mimeRegistrar = array();
    private static $default = null;

    /**
     * @param $mimeType
     * @param MimeHandlerAdapter $handler
     */
    public static function register($mimeType, MimeHandlerAdapter $handler)
    {
        self::$mimeRegistrar[$mimeType] = $handler;
    }

    /**
     * @param null $mimeType
     * @return mixed|null|MimeHandlerAdapter
     */
    public static function get($mimeType = null)
    {
        if (isset(self::$mimeRegistrar[$mimeType])) {
            return self::$mimeRegistrar[$mimeType];
        }

        if (empty(self::$default)) {
            self::$default = new MimeHandlerAdapter();
        }

        return self::$default;
    }

    /**
     * Does this particular Mime Type have a parser registered
     * for it?
     * @param string $mimeType
     * @return bool
     */
    public static function hasParserRegistered($mimeType)
    {
        return isset(self::$mimeRegistrar[$mimeType]);
    }
}
