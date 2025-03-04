<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdaf2298b79e509ac73c2cad95eb356c2
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Carbon_Fields\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Carbon_Fields\\' => 
        array (
            0 => __DIR__ . '/..' . '/htmlburger/carbon-fields/core',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdaf2298b79e509ac73c2cad95eb356c2::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdaf2298b79e509ac73c2cad95eb356c2::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitdaf2298b79e509ac73c2cad95eb356c2::$classMap;

        }, null, ClassLoader::class);
    }
}
