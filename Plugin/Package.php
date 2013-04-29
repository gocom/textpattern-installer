<?php

namespace Rah\TextpatternPluginInstaller\Plugin;
use Rah\TextpatternPluginInstaller\Textpattern\Inject as Textpattern;

/**
 * A plugin package.
 */

class Package extends Base
{
    /**
     * Finds plugin packages.
     *
     * @param  string $directory
     * @return bool
     */

    protected function find($directory)
    {
        if ($iterator = parent::find($directory))
        {
            foreach ($iterator as $file)
            {
                if (preg_match('/^[a-z0-9]{3}_[a-z0-9]{0,64}_v[a-z0-9\-\.]+(_zip)?\.txt$/i', basename($file)),  && is_file($file) && is_readable($file) && $contents = file_get_contents($file))
                {
                    $plugin = (object) null;
                    $plugin->name = implode('_v', array_slice(explode('_v', basename($file, '.txt')), 0, -1));
                    $this->plugin[] = $plugin;
                    $this->package[] = $contents;
                }
            }
        }

        return !empty($this->plugin);
    }

    /**
     * Skip packager, just inject Textpattern.
     */

    protected function package()
    {
        new Textpattern();
    }
}