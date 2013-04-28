<?php

namespace Rah\TextpatternPluginInstaller\Textpattern;

/**
 * Finds closest Textpattern installation location.
 */

class Find
{
    /**
     * Path to Textpattern installation.
     *
     * @var string|bool
     */

    static public $path = false;

    /**
     * Candidates for the installation location.
     *
     * @var array
     */

    protected $candidates = array('./', '../');

    /**
     * Constructor.
     */

    public function __construct()
    {
        if (self::$path === false)
        {
            foreach ($this->candidates as $candidate)
            {
                if (self::$path = $this->find($candidate))
                {
                    new Inject();
                    break;
                }
            }

            if (!self::$path)
            {
                throw new \InvalidArgumentException('Textpattern installation location was not found.');
            }
        }
    }

    /**
     * Finds the closest Textpattern installation path.
     *
     * @param  string      The directory
     * @return string|bool The path, or FALSE
     */
 
    public function find($directory)
    {
        if (!file_exists($directory) || !is_dir($directory) || !is_readable($directory))
        {
            return false;
        }

        $iterator = new \RecursiveDirectoryIterator(realpath($directory));
        $iterator = new \RecursiveIteratorIterator($iterator);

        foreach ($iterator as $file)
        {
        	if (basename($file) === 'config.php' && is_file($file) && is_readable($file) && $contents = file_get_contents($file))
            {
                if (strpos($contents, '$txpcfg') !== false && file_exists(dirname($file) . '/publish.php'))
                {
                    return dirname($file);
                }
            }
        }

        return false;
    }

    /**
     * Gives out the path.
     *
     * @return string The path or an empty string
     */

    public function __toString()
    {
        return (string) self::$path;
    }
}