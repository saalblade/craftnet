<?php

namespace craftcom\twigextensions;

use craftcom\helpers\MixHelper;

use Twig_Extension;
use Twig_SimpleFunction;

class CraftIdTwigExtension extends Twig_Extension
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Craft ID';
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('mix', [$this, 'mix']),
        ];
    }

    /**
     * @param        $path
     * @param string $manifestDirectory
     *
     * @return string
     * @throws \Exception
     */
    public function mix($path, $manifestDirectory = '')
    {
        return MixHelper::mix($path, $manifestDirectory);
    }
}
