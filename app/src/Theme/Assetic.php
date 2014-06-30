<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Theme;

class Assetic 
{
    private $assets = array();

    public function addAsset($dir, $webPath)
    {
        $this->assets[$dir] = $webPath;
    }

    public function getAssetWebPath($relativePath)
    {
        foreach ($this->assets as $dir => $webPath)
        {
            if(is_file($dir . '/' . $relativePath)) {
                return $webPath . '/' . $relativePath;
            }
        }

        throw new \RuntimeException("Asset `$relativePath` does not found.");
    }
} 