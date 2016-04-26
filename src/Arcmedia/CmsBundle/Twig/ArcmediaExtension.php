<?php

namespace Arcmedia\CmsBundle\Twig;

use ArrayObject;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use Arcmedia\CmsBundle\Helper\Locale;

/**
 * Class ArcmediaExtension
 * @package Arcmedia\CmsBundle\Twig
 */
class ArcmediaExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    /** @var RequestStack */
    private $requestStack;

    /**
     * ArcmediaExtension constructor.
     * @param RequestStack  $requestStack
     */
    public function __construct($requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Agrega la variable global 'script_declarations'
     * y las variables globales de internacionalización
     * tag
     * sef
     * lang_code
     * country_code
     *
     * @return array
     */
    public function getGlobals()
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        return [
            'script_declarations' => new ArrayObject(),
            'tag' => Locale::getTag($locale),
            'lang_code' => locale_get_primary_language($locale),
            'country_code' => locale_get_region($locale),
            'sef' => Locale::getSef($locale),
        ];
    }

    /**
     * Agrega el parser para la etiqueta script_declaration
     *
     * @return array
     */
    public function getTokenParsers()
    {
        return [
            new ScriptDeclarationTokenParser(),
        ];
    }

    /**
     * Agrega las funciones de manipulación de modulos
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('get_primary_language', 'locale_get_primary_language'),
            new Twig_SimpleFunction('get_region', 'locale_get_region'),
        ];
    }


    /**
     * Nombre de la extension
     *
     * @return string
     */
    public function getName()
    {
        return 'arcmedia_extension';
    }

    /**
     * Añade un filtro para la URL
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            'url_decode' => new Twig_SimpleFilter('url_decode', [$this, 'urlDecode']),
        ];
    }

    /**
     * URL Decode a string
     *
     * @param string $url
     *
     * @return string The decoded URL
     */
    public function urlDecode($url)
    {
        return urldecode($url);
    }
}
