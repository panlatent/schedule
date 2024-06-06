<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\helpers;

use Composer\Autoload\ClassLoader;
use Craft;
use Throwable;

/**
 * Class ClassHelper
 *
 * @package panlatent\schedule\helpers
 * @author Panlatent <panlatent@gmail.com>
 */
class ClassHelper
{
    /**
     * Returns known component classes.
     *
     * @return string[]
     */
    public static function findClasses(): array
    {
        // See if Composer has an optimized autoloader
        // h/t https://stackoverflow.com/a/46435124/1688568
        $autoloadClass = null;
        foreach (get_declared_classes() as $class) {
            if (str_starts_with($class, 'ComposerAutoloaderInit')) {
                $autoloadClass = $class;
                break;
            }
        }

        if ($autoloadClass !== null) {
            // Get a list of namespaces we care about
            $namespaces = ['craft'];
            foreach (Craft::$app->getPlugins()->getAllPlugins() as $plugin) {
                $classParts = explode('\\', get_class($plugin));
                if (count($classParts) > 1) {
                    $namespaces[] = implode('\\', array_slice($classParts, 0, -1));
                }
            }

            $psr4Config = Craft::$app->getComposer()->getConfig()['autoload']['psr-4'] ?? [];
            foreach (array_keys($psr4Config) as $namespace) {
                $namespaces[] = rtrim($namespace, '\\');
            }

            $namespaces = array_unique($namespaces);

            /** @var ClassLoader $classLoader */
            /** @noinspection PhpUndefinedMethodInspection */
            $classLoader = $autoloadClass::getLoader();
            try {
                foreach ($classLoader->getClassMap() as $class => $file) {
                    if (!class_exists($class, false) &&
                        !interface_exists($class, false) &&
                        !trait_exists($class, false) &&
                        file_exists($file) &&
                        !str_ends_with($class, 'Test') &&
                        !str_ends_with($class, 'TestCase') &&
                        !str_starts_with($class, 'craft\test\\')) {
                        // See if it's in a namespace we care about
                        foreach ($namespaces as $namespace) {
                            if (str_starts_with($class, $namespace . '\\')) {
                                require $file;
                                break;
                            }
                        }
                    }
                }
            } catch (Throwable) {
            }

        }

        $classes = get_declared_classes();
        sort($classes);

        return $classes;
    }

    /**
     * @param string $doc
     * @return string|null
     */
    public static function getPhpDocSummary(string $doc): ?string
    {
        foreach (preg_split("/\r\n|\n|\r/", $doc) as $line) {
            $line = preg_replace('#^[/*\s]*(?:@event\s+\S+\s+)?#', '', $line);
            if (str_starts_with($line, '@')) {
                return null;
            }
            if ($line) {
                return $line;
            }
        }

        return null;
    }
}