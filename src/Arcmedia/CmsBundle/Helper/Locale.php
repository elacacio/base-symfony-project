<?php

namespace Arcmedia\CmsBundle\Helper;

/**
 * Class Locale
 * @package Arcmedia\CmsBundle\Helper
 */
class Locale
{
    /**
     * Returns tag.     es-ES, en-US
     *
     * @param string $locale
     * @return string
     */
    public static function getTag($locale)
    {
        return locale_get_primary_language($locale).'-'.locale_get_region($locale);
    }

    /**
     * Returna sef.     es, mx, co, us
     *
     * @param string $localeOrTag
     * @return string
     */
    public static function getSef($localeOrTag)
    {
        return strtolower(locale_get_region($localeOrTag));
    }

    /**
     * Returns locale definition for library d3js
     * https://github.com/mbostock/d3/wiki/Localization#locale
     *
     * @param string $locale
     * @return array
     */
    public static function getD3LocaleDefinition($locale)
    {
        $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);

        $dateFmt = new \IntlDateFormatter($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
        $dateFmt->setPattern('EEEE');

        $sdateFmt = new \IntlDateFormatter($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
        $sdateFmt->setPattern('EEE');

        $days = [];
        $shortDays = [];
        for ($i = 0; $i < 7; $i++) {
            $ts = strtotime('next Sunday +'.$i.' days');
            $days[] = $dateFmt->format($ts);
            $shortDays[] = $sdateFmt->format($ts);
        }

        $dateFmt->setPattern('MMMM');
        $sdateFmt->setPattern('MMM');
        $months = [];
        $shortMonths = [];
        $day = new \DateTime();
        $day->setDate(2010, 1, 1);
        $oneMonth = new \DateInterval("P1M");
        for ($i = 0; $i < 11; $i++) {
            $months[] = $dateFmt->format($day);
            $shortMonths[] = $sdateFmt->format($day);
            $day = $day->add($oneMonth);
        }

        $d3LocaleDefinition = [
            "decimal" => $formatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL),
            "thousands" => $formatter->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL),
            "grouping" => [$formatter->getAttribute(\NumberFormatter::GROUPING_SIZE)],
            "currency" => [$formatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL), ""],
            "dateTime" => "%a %b %e %X %Y",
            "date" => "%Y/%m/%d",
            "time" => "%H:%M:%S",
            "periods" => ["AM", "PM"],
            "days" => $days,
            "shortDays" => $shortDays,
            "months" => $months,
            "shortMonths" => $shortMonths,
        ];

        return $d3LocaleDefinition;
    }
}
