<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit684364e6af3817dd4e45d4ee574e592b
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Http\\Message\\' => 17,
        ),
        'L' => 
        array (
            'League\\Uri\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'League\\Uri\\' => 
        array (
            0 => __DIR__ . '/..' . '/league/uri/src',
            1 => __DIR__ . '/..' . '/league/uri-interfaces/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'src\\InstantCache\\InstantCache' => __DIR__ . '/../..' . '/src/InstantCache/InstantCache.php',
        'src\\JsonDBServ\\JsonDBServ' => __DIR__ . '/../..' . '/src/JsonDBServ/JsonDBServ.php',
        'src\\JsonDBServ\\Manual' => __DIR__ . '/../..' . '/src/JsonDBServ/Manual.php',
        'src\\JsonDB\\JsonDB' => __DIR__ . '/../..' . '/src/JsonDB/JsonDB.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit684364e6af3817dd4e45d4ee574e592b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit684364e6af3817dd4e45d4ee574e592b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit684364e6af3817dd4e45d4ee574e592b::$classMap;

        }, null, ClassLoader::class);
    }
}
