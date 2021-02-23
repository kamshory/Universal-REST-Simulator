<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2e2b3c9ad7fc9b6c4ae58a8760e1bc10
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2e2b3c9ad7fc9b6c4ae58a8760e1bc10::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2e2b3c9ad7fc9b6c4ae58a8760e1bc10::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
