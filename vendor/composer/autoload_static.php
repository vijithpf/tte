<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit729c34f968977a2fbeabffe5c70418aa
{
    public static $files = array (
        'ce89ac35a6c330c55f4710717db9ff78' => __DIR__ . '/..' . '/kriswallsmith/assetic/src/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\Process\\' => 26,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\Process\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/process',
        ),
    );

    public static $prefixesPsr0 = array (
        'A' => 
        array (
            'Assetic' => 
            array (
                0 => __DIR__ . '/..' . '/kriswallsmith/assetic/src',
            ),
        ),
    );

    public static $classMap = array (
        'JSMin' => __DIR__ . '/..' . '/lmammino/jsmin4assetic/src/JSMin.php',
        'JSMinUnterminatedCommentException' => __DIR__ . '/..' . '/lmammino/jsmin4assetic/src/JSMinUnterminatedCommentException.php',
        'JSMinUnterminatedRegExpException' => __DIR__ . '/..' . '/lmammino/jsmin4assetic/src/JSMinUnterminatedRegExpException.php',
        'JSMinUnterminatedStringException' => __DIR__ . '/..' . '/lmammino/jsmin4assetic/src/JSMinUnterminatedStringException.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit729c34f968977a2fbeabffe5c70418aa::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit729c34f968977a2fbeabffe5c70418aa::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit729c34f968977a2fbeabffe5c70418aa::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit729c34f968977a2fbeabffe5c70418aa::$classMap;

        }, null, ClassLoader::class);
    }
}
