<?php
namespace rkt\MessageAPI;

/**
 * Bootstrap and register class loader
 *
 * @author Ravish Tiwari <ravishktiwari@hotmail.com>
 */
class Bootstrap
{
    /**
     * Register the autoloader and any other setup needed
     */
    public static function init($includePath, $nameSpace=__NAMESPACE__)
    {
        require_once 'SplClassLoader.php';
        $classLoader = new SplClassLoader($nameSpace, $includePath );
        $classLoader->register();
    }
}