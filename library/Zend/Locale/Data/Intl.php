<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Locale
 * @subpackage Data
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace Zend\Locale\Data;

use Zend\Cache\Cache,
    Zend\Cache\Frontend as CacheFrontend,
    Zend\Locale\Locale as ZFLocale,
    Zend\Locale\Exception\InvalidArgumentException,
    Zend\Locale\Exception\UnexpectedValueException,
    Zend\Locale\Exception\UnsupportedMethodException;

/**
 * Locale data provider, handles INTL
 *
 * @uses       Zend\Cache\Cache
 * @uses       Zend\Locale
 * @uses       Zend\Locale\Exception\InvalidArgumentException
 * @category   Zend
 * @package    Zend_Locale
 * @subpackage Data
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Intl extends AbstractLocale
{
    /**
     * Checks if Intl is available at all as it could be disabled even on PHP5.3
     *
     * @throws UnsupportedMethodExtension When Intl is missing
     * @return bool
     */
    protected static function isCldrAvailable()
    {
        if (!extension_loaded('intl')) {
            throw new UnsupportedMethodException('Missing intl extension');
        }

        return true;
    }

    /**
     * Returns detailed informations from the language table
     * If no detail is given a complete table is returned
     *
     * @param string $detail Detail to return information for
     * @param string  $locale Normalized locale
     * @param boolean $invert Invert output of the data
     * @return string|array
     */
    public static function getDisplayLanguage($detail = null, $locale = null, $invert = false)
    {
        self::isCldrAvailable();
        $locale = ZFLocale::findLocale($locale);
        if ($detail !== null) {
            return locale_get_display_language($locale);
        } else {
            $list = ZFLocale::getLocaleList();
            foreach($list as $key => $value) {
                $list[$key] = locale_get_display_language($key);
            }

            if ($invert) {
                array_flip($list);
            }

            return $list;
        }
    }

    /**
     * Returns detailed informations from the script table
     * If no detail is given a complete table is returned
     *
     * @param string  $detail Detail to return information for
     * @param string  $locale Normalized locale
     * @param boolean $invert Invert output of the data
     * @return string|array
     */
    public static function getDisplayScript($detail = null, $locale = null, $invert = false)
    {
        self::isCldrAvailable();
        $locale = ZFLocale::findLocale($locale);
        if ($detail !== null) {
            return locale_get_display_script($locale);
        } else {
            $list = ZFLocale::getLocaleList();
            foreach($list as $key => $value) {
                $list[$key] = locale_get_display_script($key);
            }

            if ($invert) {
                array_flip($list);
            }

            return $list;
        }
    }

    /**
     * Returns detailed informations from the territory table
     * If no detail is given a complete table is returned
     *
     * @param string  $detail Detail to return information for
     * @param string  $locale Normalized locale
     * @param boolean $invert Invert output of the data
     * @return string|array
     */
    public static function getDisplayTerritory($detail = null, $locale = null, $invert = false)
    {
        self::isCldrAvailable();
        $locale = ZFLocale::findLocale($locale);
        if ($detail !== null) {
            return locale_get_display_region($locale);
        } else {
            $list = ZFLocale::getLocaleList();
            foreach($list as $key => $value) {
                $list[$key] = locale_get_display_region($key);
            }

            if ($invert) {
                array_flip($list);
            }

            return $list;
        }
    }

    /**
     * Returns detailed informations from the variant table
     * If no detail is given a complete table is returned
     *
     * @param string  $detail Detail to return information for
     * @param string  $locale Normalized locale
     * @param boolean $invert Invert output of the data
     * @return string|array
     */
    public static function getDisplayVariant($detail = null, $locale = null, $invert = false)
    {
        self::isCldrAvailable();
        $locale = ZFLocale::findLocale($locale);
        if ($detail !== null) {
            return locale_get_display_variant($locale);
        } else {
            $list = ZFLocale::getLocaleList();
            foreach($list as $key => $value) {
                $list[$key] = locale_get_display_variant($key);
            }

            if ($invert) {
                array_flip($list);
            }

            return $list;
        }
    }
}
