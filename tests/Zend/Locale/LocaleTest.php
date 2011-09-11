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
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\Locale;

use \Zend\Locale\Locale,
    \Zend\Locale\Exception\InvalidArgumentException,
    \Zend\Locale\Exception\UnexpectedValueException,
    \Zend\Cache\Cache,
    \Zend\Cache\Frontend\Core as CacheCore;

/**
 * @category   Zend
 * @package    Zend_Locale
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Locale
 */
class LocaleTest extends \PHPUnit_Framework_TestCase
{
    private $_cache  = null;
    private $_locale = null;

    public function setUp()
    {
        $this->_locale = setlocale(LC_ALL, 0);
        setlocale(LC_ALL, 'de');
        $this->_cache = Cache::factory('Core', 'File',
                 array('lifetime' => 120, 'automatic_serialization' => true),
                 array('cache_dir' => __DIR__ . '/../_files/'));
        LocaleTestHelper::resetObject();
        LocaleTestHelper::setCache($this->_cache);
        putenv("HTTP_ACCEPT_LANGUAGE=,de,en-UK-US;q=0.5,fr_FR;q=0.2");
    }

    public function tearDown()
    {
        $this->_cache->clean(Cache::CLEANING_MODE_ALL);
        if (is_string($this->_locale) && strpos($this->_locale, ';')) {
            $locales = array();
            foreach (explode(';', $this->_locale) as $l) {
                $tmp = explode('=', $l);
                $locales[$tmp[0]] = $tmp[1];
            }
            setlocale(LC_ALL, $locales);
            return;
        }
        setlocale(LC_ALL, $this->_locale);
    }

    /**
     * Test for object creation
     */
    public function testObjectCreation()
    {
        $this->assertTrue(LocaleTestHelper::isLocale('de'));

        $this->assertTrue(new LocaleTestHelper() instanceof Locale);
        $this->assertTrue(new LocaleTestHelper('root') instanceof Locale);
        try {
            $locale = new LocaleTestHelper(Locale::ENVIRONMENT);
            $this->assertTrue($locale instanceof Locale);
        } catch (InvalidArgumentException $e) {
            // ignore environments where the locale can not be detected
            $this->assertContains('Autodetection', $e->getMessage());
        }

        try {
            $this->assertTrue(new LocaleTestHelper(Locale::BROWSER) instanceof Locale);
        } catch (InvalidArgumentException $e) {
            // ignore environments where the locale can not be detected
            $this->assertContains('Autodetection', $e->getMessage());
        }

        $locale = new LocaleTestHelper('de');
        $this->assertTrue(new LocaleTestHelper($locale) instanceof Locale);

        $locale = new LocaleTestHelper('auto');
        $this->assertTrue(new LocaleTestHelper($locale) instanceof Locale);
    }

    /**
     * Test for serialization
     */
    public function testSerialize()
    {
        $value = new LocaleTestHelper('de_DE');
        $serial = $value->serialize();
        $this->assertTrue(!empty($serial));

        $newvalue = unserialize($serial);
        $this->assertTrue($value->equals($newvalue));
    }

    /**
     * Test toString
     */
    public function testToString()
    {
        $value = new LocaleTestHelper('de_DE');
        $this->assertEquals('de_DE', $value->toString());
        $this->assertEquals('de_DE', $value->__toString());
    }

    /**
     * Test getEnvironment
     */
    public function testLocaleDetail()
    {
        $value = new LocaleTestHelper('de_AT');
        $this->assertEquals('de', $value->getLanguage());
        $this->assertEquals('AT', $value->getRegion());

        $value = new LocaleTestHelper('en_US');
        $this->assertEquals('en', $value->getLanguage());
        $this->assertEquals('US', $value->getRegion());

        $value = new LocaleTestHelper('en');
        $this->assertEquals('en', $value->getLanguage());
        $this->assertFalse($value->getRegion());
    }

    /**
     * Test getEnvironment
     */
    public function testEnvironment()
    {
        $value = new LocaleTestHelper();
        $default = $value->getEnvironment();
        $this->assertTrue(is_array($default));
    }

    /**
     * Test getBrowser
     */
    public function testBrowser()
    {
        $value = new LocaleTestHelper();
        $default = $value->getBrowser();
        $this->assertTrue(is_array($default));
    }

    /**
     * Test clone
     */
    public function testCloning()
    {
        $value = new LocaleTestHelper('de_DE');
        $newvalue = clone $value;
        $this->assertEquals($value->toString(), $newvalue->toString());
    }

    /**
     * Test setLocale
     */
    public function testsetLocale()
    {
        $value = new LocaleTestHelper('de_DE');
        $value->setLocale('en_US');
        $this->assertEquals('en_US', $value->toString());

        $value->setLocale('en_AA');
        $this->assertEquals('en', $value->toString());

        $value->setLocale('xx_AA');
        $this->assertEquals('root', $value->toString());

        $value->setLocale('auto');
        $this->assertTrue(is_string($value->toString()));

        try {
            $value->setLocale('browser');
            $this->assertTrue(is_string($value->toString()));
        } catch (UnexpectedValueException $e) {
            // ignore environments where the locale can not be detected
            $this->assertContains('Autodetection', $e->getMessage());
        }

        try {
            $value->setLocale('environment');
            $this->assertTrue(is_string($value->toString()));
        } catch (UnexpectedValueException $e) {
            // ignore environments where the locale can not be detected
            $this->assertContains('Autodetection', $e->getMessage());
        }
    }

    /**
     * Test getDisplayLanguage
     */
    public function testgetDisplayLanguage()
    {
        set_error_handler(array($this, 'errorHandlerIgnore'));
        $list = LocaleTestHelper::getDisplayLanguage();
        $this->assertTrue(is_array($list));
        $list = LocaleTestHelper::getDisplayLanguage(null, 'de');

        $this->assertEquals('Deutsch', LocaleTestHelper::getDisplayLanguage('de', 'de_AT'));
        $this->assertEquals('German',  LocaleTestHelper::getDisplayLanguage('de', 'en'));

        try {
            $result = LocaleTestHelper::getDisplayLanguage('xyz');
            $this->fail();
        } catch(\Exception $e) {
            $this->assertContains('is no known locale', $e->getMessage());
        }

        $this->assertTrue(is_string(LocaleTestHelper::getDisplayLanguage('de', 'auto')));
        restore_error_handler();
    }

    /**
     * Test getDisplayScript
     */
    public function testgetDisplayScript()
    {
        set_error_handler(array($this, 'errorHandlerIgnore'));
        $list = LocaleTestHelper::getDisplayScript();
        $this->assertTrue(is_array($list));

        $list = LocaleTestHelper::getDisplayScript('script', 'de');
        $this->assertTrue(is_array($list));

        $this->assertEquals('Arabisch', LocaleTestHelper::getDisplayScript('Arab', 'de_AT'));
        $this->assertEquals('Arabic', LocaleTestHelper::getDisplayScript('Arab', 'en'));

        try {
            $this->assertFalse(LocaleTestHelper::getDisplayScript('xyz'));
            $this->fail();
        } catch (\Exception $e) {
            $this->assertContains('is no known locale', $e->getMessage());
        }

        $this->assertTrue(is_string(LocaleTestHelper::getDisplayScript('Arab', 'auto')));
        restore_error_handler();
    }

    /**
     * Test getDisplayTerritory
     */
    public function testgetDisplayTerritory()
    {
        set_error_handler(array($this, 'errorHandlerIgnore'));
        $list = LocaleTestHelper::getDisplayTerritory();
        $this->assertTrue(is_array($list));

        $list = LocaleTestHelper::getDisplayTerritory(null, 'de');
        $this->assertEquals("Vereinigte Staaten", $list['US']);
        $this->assertEquals('Deutschland', LocaleTestHelper::getDisplayTerritory('DE', 'de_DE'));
        $this->assertEquals('Germany', LocaleTestHelper::getDisplayTerritory('DE', 'en'));
        $this->assertEquals('Afrika', LocaleTestHelper::getDisplayTerritory('002', 'de_AT'));
        $this->assertEquals('Africa', LocaleTestHelper::getDisplayTerritory('002', 'en'));

        try {
            $result = LocaleTestHelper::getDisplayTerritory(null, 'xyz');
            $this->fail();
        } catch (\Exception $e) {
            $this->assertContains('is no known locale', $e->getMessage());
        }

        $this->assertTrue(is_string(LocaleTestHelper::getDisplayTerritory('DE', 'auto')));
        $this->assertTrue(is_string(LocaleTestHelper::getDisplayTerritory('002', 'auto')));
        restore_error_handler();
    }

    /**
     * Test getTranslation
     */
    public function _OLD_testgetTranslation()
    {
        try {
            $temp = LocaleTestHelper::getTranslation('xx');
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertContains('Unknown detail (', $e->getMessage());
        }

        $this->assertEquals('Januar', LocaleTestHelper::getTranslation('1', 'month', 'de_DE'));
        $this->assertEquals('January', LocaleTestHelper::getTranslation('1', 'month', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation('x', 'month'));

        $this->assertEquals('Jan', LocaleTestHelper::getTranslation(array('gregorian', 'format', 'abbreviated', '1'), 'month', 'de_DE'));
        $this->assertEquals('Jan', LocaleTestHelper::getTranslation(array('gregorian', 'format', 'abbreviated', '1'), 'month', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation(array('gregorian', 'format', 'abbreviated', 'x'), 'month'));

        $this->assertEquals('J', LocaleTestHelper::getTranslation(array('gregorian', 'stand-alone', 'narrow', '1'), 'month', 'de_DE'));
        $this->assertEquals('J', LocaleTestHelper::getTranslation(array('gregorian', 'stand-alone', 'narrow', '1'), 'month', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation(array('gregorian', 'stand-alone', 'narrow', 'x'), 'month'));

        $this->assertEquals('Sonntag', LocaleTestHelper::getTranslation('sun', 'day', 'de_DE'));
        $this->assertEquals('Sunday', LocaleTestHelper::getTranslation('sun', 'day', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation('xxx', 'day'));

        $this->assertEquals('So.', LocaleTestHelper::getTranslation(array('gregorian', 'format', 'abbreviated', 'sun'), 'day', 'de_DE'));
        $this->assertEquals('Sun', LocaleTestHelper::getTranslation(array('gregorian', 'format', 'abbreviated', 'sun'), 'day', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation(array('gregorian', 'format', 'abbreviated', 'xxx'), 'day'));

        $this->assertEquals('S', LocaleTestHelper::getTranslation(array('gregorian', 'stand-alone', 'narrow', 'sun'), 'day', 'de_DE'));
        $this->assertEquals('S', LocaleTestHelper::getTranslation(array('gregorian', 'stand-alone', 'narrow', 'sun'), 'day', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation(array('gregorian', 'stand-alone', 'narrow', 'xxx'), 'day'));

        $this->assertEquals('EEEE, d. MMMM y', LocaleTestHelper::getTranslation('full', 'date', 'de_DE'));
        $this->assertEquals('EEEE, MMMM d, y', LocaleTestHelper::getTranslation('full', 'date', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation('xxxx', 'date'));

        $this->assertEquals("HH:mm:ss zzzz", LocaleTestHelper::getTranslation('full', 'time', 'de_DE'));
        $this->assertEquals('h:mm:ss a zzzz', LocaleTestHelper::getTranslation('full', 'time', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation('xxxx', 'time'));

        $this->assertEquals('Wien', LocaleTestHelper::getTranslation('Europe/Vienna', 'citytotimezone', 'de_DE'));
        $this->assertEquals("St. John's", LocaleTestHelper::getTranslation('America/St_Johns', 'citytotimezone', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation('xxxx', 'citytotimezone'));

        $this->assertEquals('Euro', LocaleTestHelper::getTranslation('EUR', 'nametocurrency', 'de_DE'));
        $this->assertEquals('Euro', LocaleTestHelper::getTranslation('EUR', 'nametocurrency', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation('xxx', 'nametocurrency'));

        $this->assertEquals('EUR', LocaleTestHelper::getTranslation('Euro', 'currencytoname', 'de_DE'));
        $this->assertEquals('EUR', LocaleTestHelper::getTranslation('Euro', 'currencytoname', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation('xxx', 'currencytoname'));

        $this->assertEquals('Fr.', LocaleTestHelper::getTranslation('CHF', 'currencysymbol', 'de_DE'));
        $this->assertEquals('Fr.', LocaleTestHelper::getTranslation('CHF', 'currencysymbol', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation('xxx', 'currencysymbol'));

        $this->assertEquals('EUR', LocaleTestHelper::getTranslation('AT', 'currencytoregion', 'de_DE'));
        $this->assertEquals('EUR', LocaleTestHelper::getTranslation('AT', 'currencytoregion', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation('xxx', 'currencytoregion'));

        $this->assertEquals('011 014 015 017 018', LocaleTestHelper::getTranslation('002', 'regiontoterritory', 'de_DE'));
        $this->assertEquals('011 014 015 017 018', LocaleTestHelper::getTranslation('002', 'regiontoterritory', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation('xxx', 'regiontoterritory'));

        $this->assertEquals('AT BE CH DE LI LU', LocaleTestHelper::getTranslation('de', 'territorytolanguage', 'de_DE'));
        $this->assertEquals('AT BE CH DE LI LU', LocaleTestHelper::getTranslation('de', 'territorytolanguage', 'en'));
        $this->assertFalse(LocaleTestHelper::getTranslation('xxx', 'territorytolanguage'));
    }

    /**
     * Test getTranslationList
     */
    public function _OLD_testgetTranslationList()
    {
        try {
            $temp = LocaleTestHelper::getTranslationList();
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertContains('Unknown list (', $e->getMessage());
        }

        $this->assertTrue(in_array('Chinesischer Kalender', LocaleTestHelper::getTranslationList('type', 'de_DE', 'calendar')));
        $this->assertTrue(in_array('Chinese Calendar', LocaleTestHelper::getTranslationList('type', 'en', 'calendar')));

        $this->assertTrue(in_array('Januar', LocaleTestHelper::getTranslationList('month', 'de_DE')));
        $this->assertTrue(in_array('January', LocaleTestHelper::getTranslationList('month', 'en')));

        $this->assertTrue(in_array('Jan', LocaleTestHelper::getTranslationList('month', 'de_DE', array('gregorian', 'format', 'abbreviated'))));
        $this->assertTrue(in_array('Jan', LocaleTestHelper::getTranslationList('month', 'en', array('gregorian', 'format', 'abbreviated'))));

        $this->assertTrue(in_array('J', LocaleTestHelper::getTranslationList('month', 'de_DE', array('gregorian', 'stand-alone', 'narrow'))));
        $this->assertTrue(in_array('J', LocaleTestHelper::getTranslationList('month', 'en', array('gregorian', 'stand-alone', 'narrow'))));

        $this->assertTrue(in_array('Sonntag', LocaleTestHelper::getTranslationList('day', 'de_DE')));
        $this->assertTrue(in_array('Sunday', LocaleTestHelper::getTranslationList('day', 'en')));

        $this->assertTrue(in_array('So.', LocaleTestHelper::getTranslationList('day', 'de_DE', array('gregorian', 'format', 'abbreviated'))));
        $this->assertTrue(in_array('Sun', LocaleTestHelper::getTranslationList('day', 'en', array('gregorian', 'format', 'abbreviated'))));

        $this->assertTrue(in_array('S', LocaleTestHelper::getTranslationList('day', 'de_DE', array('gregorian', 'stand-alone', 'narrow'))));
        $this->assertTrue(in_array('S', LocaleTestHelper::getTranslationList('day', 'en', array('gregorian', 'stand-alone', 'narrow'))));

        $this->assertTrue(in_array('EEEE, d. MMMM y', LocaleTestHelper::getTranslationList('date', 'de_DE')));
        $this->assertTrue(in_array('EEEE, MMMM d, y', LocaleTestHelper::getTranslationList('date', 'en')));

        $this->assertTrue(in_array("HH:mm:ss zzzz", LocaleTestHelper::getTranslationList('time', 'de_DE')));
        $this->assertTrue(in_array("h:mm:ss a z", LocaleTestHelper::getTranslationList('time', 'en')));

        $this->assertTrue(in_array('Wien', LocaleTestHelper::getTranslationList('citytotimezone', 'de_DE')));
        $this->assertTrue(in_array("St. John's", LocaleTestHelper::getTranslationList('citytotimezone', 'en')));

        $this->assertTrue(in_array('Euro', LocaleTestHelper::getTranslationList('nametocurrency', 'de_DE')));
        $this->assertTrue(in_array('Euro', LocaleTestHelper::getTranslationList('nametocurrency', 'en')));

        $this->assertTrue(in_array('EUR', LocaleTestHelper::getTranslationList('currencytoname', 'de_DE')));
        $this->assertTrue(in_array('EUR', LocaleTestHelper::getTranslationList('currencytoname', 'en')));

        $this->assertTrue(in_array('Fr.', LocaleTestHelper::getTranslationList('currencysymbol', 'de_DE')));
        $this->assertTrue(in_array('Fr.', LocaleTestHelper::getTranslationList('currencysymbol', 'en')));

        $this->assertTrue(in_array('EUR', LocaleTestHelper::getTranslationList('currencytoregion', 'de_DE')));
        $this->assertTrue(in_array('EUR', LocaleTestHelper::getTranslationList('currencytoregion', 'en')));

        $this->assertTrue(in_array('AU NF NZ', LocaleTestHelper::getTranslationList('regiontoterritory', 'de_DE')));
        $this->assertTrue(in_array('AU NF NZ', LocaleTestHelper::getTranslationList('regiontoterritory', 'en')));

        $this->assertTrue(in_array('CZ', LocaleTestHelper::getTranslationList('territorytolanguage', 'de_DE')));
        $this->assertTrue(in_array('CZ', LocaleTestHelper::getTranslationList('territorytolanguage', 'en')));

        $char = LocaleTestHelper::getTranslationList('characters', 'de_DE');
        $this->assertEquals("[a ä b-o ö p-s ß t u ü v-z]", $char['characters']);
        $this->assertEquals("[á à ă â å ā æ ç é è ĕ ê ë ē í ì ĭ î ï ī ñ ó ò ŏ ô ø ō œ ú ù ŭ û ū ÿ]", $char['auxiliary']);
        $this->assertEquals("[a-z]", $char['currencySymbol']);

        $char = LocaleTestHelper::getTranslationList('characters', 'en');
        $this->assertEquals("[a-z]", $char['characters']);
        $this->assertEquals("[á à ă â å ä ã ā æ ç é è ĕ ê ë ē í ì ĭ î ï ī ñ ó ò ŏ ô ö ø ō œ ß ú ù ŭ û ü ū ÿ]", $char['auxiliary']);
        $this->assertEquals("[a-c č d-l ł m-z]", $char['currencySymbol']);
    }

    /**
     * Test for equality
     */
    public function testEquals()
    {
        $value = new LocaleTestHelper('de_DE');
        $serial = new LocaleTestHelper('de_DE');
        $serial2 = new LocaleTestHelper('de_AT');
        $this->assertTrue($value->equals($serial));
        $this->assertFalse($value->equals($serial2));
    }

    /**
     * Test getQuestion
     */
    public function _OLD_testgetQuestion()
    {
        $list = LocaleTestHelper::getQuestion();
        $this->assertTrue(isset($list['yes']));

        $list = LocaleTestHelper::getQuestion('de');
        $this->assertEquals('ja', $list['yes']);

        $this->assertTrue(is_array(LocaleTestHelper::getQuestion('auto')));

        try {
            $this->assertTrue(is_array(LocaleTestHelper::getQuestion('browser')));
        } catch (InvalidArgumentException $e) {
            $this->assertContains('Autodetection', $e->getMessage());
        }

        try {
            $this->assertTrue(is_array(LocaleTestHelper::getQuestion('environment')));
        } catch (InvalidArgumentException $e) {
            $this->assertContains('ocale', $e->getMessage());
        }
    }

    /**
     * Test getBrowser
     */
    public function testgetBrowser()
    {
        LocaleTestHelper::resetObject();
        $value = new LocaleTestHelper();
        $list = $value->getBrowser();
        if (empty($list)) {
            $this->markTestSkipped('Browser autodetection not possible in current environment');
        }
        $this->assertTrue(isset($list['de']));
        $this->assertEquals(array('de' => 1, 'en_UK' => 0.5, 'en_US' => 0.5,
                                  'en' => 0.5, 'fr_FR' => 0.2, 'fr' => 0.2), $list);

        LocaleTestHelper::resetObject();
        putenv("HTTP_ACCEPT_LANGUAGE=");

        $value = new LocaleTestHelper();
        $list = $value->getBrowser();
        $this->assertEquals(array(), $list);
    }

    /**
     * Test getHttpCharset
     */
    public function testgetHttpCharset()
    {
        LocaleTestHelper::resetObject();
        putenv("HTTP_ACCEPT_CHARSET=");
        $value = new LocaleTestHelper();
        $list = $value->getHttpCharset();
        $this->assertTrue(empty($list));

        LocaleTestHelper::resetObject();
        putenv("HTTP_ACCEPT_CHARSET=,iso-8859-1, utf-8, utf-16, *;q=0.1");
        $value = new LocaleTestHelper();
        $list = $value->getHttpCharset();
        $this->assertTrue(isset($list['utf-8']));
    }

    /**
     * Test isLocale
     */
    public function testIsLocale()
    {
        $locale = new LocaleTestHelper('ar');
        $this->assertTrue(LocaleTestHelper::isLocale($locale));
        $this->assertTrue(LocaleTestHelper::isLocale('de'));
        $this->assertTrue(LocaleTestHelper::isLocale('de_AT'));
        $this->assertTrue(LocaleTestHelper::isLocale('de_xx'));
        $this->assertFalse(LocaleTestHelper::isLocale('yy'));
        $this->assertFalse(LocaleTestHelper::isLocale(1234));
        $this->assertFalse(LocaleTestHelper::isLocale('', true));
        $this->assertFalse(LocaleTestHelper::isLocale('', false));
        $this->assertTrue(LocaleTestHelper::isLocale('auto'));
        $this->assertTrue(LocaleTestHelper::isLocale('browser'));
        if (count(Locale::getEnvironment()) != 0) {
            $this->assertTrue(LocaleTestHelper::isLocale('environment'));
        }
    }

    /**
     * Test getLocaleList
     */
    public function testGetLocaleList()
    {
        $this->assertTrue(is_array(LocaleTestHelper::getLocaleList()));
    }

    /**
     * Test setFallback
     */
    public function testsetFallback()
    {
        try {
            LocaleTestHelper::setFallback('auto');
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertContains("fully qualified locale", $e->getMessage());
        }

        try {
            LocaleTestHelper::setFallback('de_XX');
            $locale = new LocaleTestHelper();
            $this->assertTrue($locale instanceof Locale); // should defer to 'de' or any other standard locale
        } catch (InvalidArgumentException $e) {
            $this->fail(); // de_XX should automatically degrade to 'de'
        }

        try {
            LocaleTestHelper::setFallback('xy_ZZ');
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertContains("Unknown locale", $e->getMessage());
        }

        try {
            LocaleTestHelper::setFallback('de', 101);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertContains("Quality must be between", $e->getMessage());
        }

        try {
            LocaleTestHelper::setFallback('de', 90);
            $locale = new LocaleTestHelper();
            $this->assertTrue($locale instanceof Locale); // should defer to 'de' or any other standard locale
        } catch (InvalidArgumentException $e) {
            $this->fail();
        }

        try {
            LocaleTestHelper::setFallback('de-AT', 90);
            $locale = new LocaleTestHelper();
            $this->assertTrue($locale instanceof Locale);
        } catch (InvalidArgumentException $e) {
            $this->fail();
        }
    }

    /**
     * Test getFallback
     */
    public function testgetFallback()
    {
        LocaleTestHelper::setFallback('de');
        $this->assertTrue(array_key_exists('de', LocaleTestHelper::getFallback()));
    }

    /**
     * Caching method tests
     */
    public function testCaching()
    {
        $cache = LocaleTestHelper::getCache();
        $this->assertTrue($cache instanceof CacheCore);
        $this->assertTrue(LocaleTestHelper::hasCache());

        LocaleTestHelper::clearCache();
        $this->assertTrue(LocaleTestHelper::hasCache());

        LocaleTestHelper::removeCache();
        $this->assertFalse(LocaleTestHelper::hasCache());
    }

    /**
     * Caching method tests
     */
    public function testFindingTheProperLocale()
    {
        $this->assertTrue(is_string(LocaleTestHelper::findLocale()));
        $this->assertEquals('de', LocaleTestHelper::findLocale('de'));
        $this->assertEquals('de', LocaleTestHelper::findLocale('de_XX'));

        try {
            $locale = LocaleTestHelper::findLocale('xx_YY');
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertContains('is no known locale', $e->getMessage());
        }

        \Zend\Registry::set('Zend_Locale', 'de');
        $this->assertEquals('de', LocaleTestHelper::findLocale());
    }

    /**
     * @group ZF-3617
     */
    public function testZF3617()
    {
        $value = new LocaleTestHelper('en-US');
        $this->assertEquals('en_US', $value->toString());
    }

    /**
     * @group ZF-4963
     */
    public function testZF4963()
    {
        $value = new LocaleTestHelper();
        $locale = $value->toString();
        $this->assertTrue(!empty($locale));

        $this->assertFalse(LocaleTestHelper::isLocale(null));

        $value = new LocaleTestHelper(0);
        $value = $value->toString();
        $this->assertTrue(!empty($value));

        $this->assertFalse(LocaleTestHelper::isLocale(0));
    }

    /**
     * Test MultiPartLocales
     */
    public function testLongLocale()
    {
        $locale = new LocaleTestHelper('de_Latn_DE');
        $this->assertEquals('de_DE', $locale->toString());
        $this->assertTrue(LocaleTestHelper::isLocale('de_Latn_CAR_DE_sup3_win'));

        $locale = new LocaleTestHelper('de_Latn_DE');
        $this->assertEquals('de_DE', $locale->toString());

        $this->assertEquals('fr_FR', Locale::findLocale('fr-Arab-FR'));
    }

    /**
     * Test SunLocales
     */
    public function testSunLocale()
    {
        $this->assertTrue(LocaleTestHelper::isLocale('de_DE.utf8'));
        $this->assertFalse(LocaleTestHelper::isLocale('de.utf8.DE'));
    }

    /**
     * @group ZF-8030
     */
    public function testFailedLocaleOnPreTranslations()
    {
        $this->assertEquals('Andorra', LocaleTestHelper::getDisplayTerritory('AD', 'gl_GL'));
    }

    /**
     * @ZF-9488
     */
    public function testTerritoryToGetLocale()
    {
        $value = Locale::findLocale('US');
        $this->assertEquals('en_US', $value);

        $value = new Locale('US');
        $this->assertEquals('en_US', $value->toString());

        $value = new Locale('TR');
        $this->assertEquals('tr_TR', $value->toString());
    }

    /**
     * Ignores a raised PHP error when in effect, but throws a flag to indicate an error occurred
     *
     * @param  integer $errno
     * @param  string  $errstr
     * @param  string  $errfile
     * @param  integer $errline
     * @param  array   $errcontext
     * @return void
     */
    public function errorHandlerIgnore($errno, $errstr, $errfile, $errline, array $errcontext)
    {
        $this->_errorOccurred = true;
    }
}

class LocaleTestHelper extends Locale
{
    public static function resetObject()
    {
        self::$_auto        = null;
        self::$_environment = null;
        self::$_browser     = null;
    }
}
