<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite2ac4d1bdedaf37534ce5c4891be8ee9
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Fragen\\Git_Updater\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Fragen\\Git_Updater\\' => 
        array (
            0 => __DIR__ . '/..' . '/afragen/github-updater/src/Git_Updater',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'Parsedown' => 
            array (
                0 => __DIR__ . '/..' . '/erusev/parsedown',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Fragen\\Singleton' => __DIR__ . '/..' . '/afragen/singleton/Singleton.php',
        'WP_Dependency_Installer' => __DIR__ . '/..' . '/afragen/wp-dependency-installer/wp-dependency-installer.php',
        'WP_Dependency_Installer_Skin' => __DIR__ . '/..' . '/afragen/wp-dependency-installer/wp-dependency-installer-skin.php',
        'WP_Dismiss_Notice' => __DIR__ . '/..' . '/afragen/wp-dismiss-notice/wp-dismiss-notice.php',
        'WordPressdotorg\\Plugin_Directory\\Readme\\Parser' => __DIR__ . '/..' . '/afragen/wordpress-plugin-readme-parser/class-parser.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite2ac4d1bdedaf37534ce5c4891be8ee9::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite2ac4d1bdedaf37534ce5c4891be8ee9::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInite2ac4d1bdedaf37534ce5c4891be8ee9::$prefixesPsr0;
            $loader->classMap = ComposerStaticInite2ac4d1bdedaf37534ce5c4891be8ee9::$classMap;

        }, null, ClassLoader::class);
    }
}