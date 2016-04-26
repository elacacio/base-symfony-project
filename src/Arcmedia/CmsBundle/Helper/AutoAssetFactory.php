<?php

namespace Arcmedia\CmsBundle\Helper;

use Assetic\Asset\AssetInterface;
use Symfony\Bundle\AsseticBundle\Factory\AssetFactory;

/**
 * Class AutoAssetFactory
 * @package Arcmedia\CmsBundle\Helper
 */
class AutoAssetFactory extends AssetFactory
{

    /**
     * @param array $inputs
     * @param array $filters
     * @param array $options
     * @return \Assetic\Asset\AssetInterface
     */
    public function createAsset($inputs = array(), $filters = array(), array $options = array())
    {
        /** @var AssetInterface $asset */
        $asset = parent::createAsset($inputs, $filters, $options);
        $path = $asset->getTargetPath();
        if (strpos($path, "compiled") === 0) {
            $content = $asset->dump();
            $hash = substr(sha1($content), 0, 7);
            $parts = pathinfo($path);
            $filename = explode('@', $parts['filename'])[0];
            $target = $parts['dirname'].'/'.$filename."@".$hash.".".$parts['extension'];
            $asset->setTargetPath($target);
        }

        return $asset;
    }
}
