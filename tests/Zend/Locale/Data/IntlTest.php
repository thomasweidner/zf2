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

use \Zend\Locale\Data\Intl,
    \Zend\Locale\Exception\InvalidArgumentException,
    \Zend\Locale\Locale,
    \Zend\Cache\Cache;

/**
 * @category   Zend
 * @package    Zend_Locale
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Locale
 */
class IntlTest extends \PHPUnit_Framework_TestCase
{
    private $_cache = null;

    public function setUp()
    {
        $this->_cache = Cache::factory('Core', 'File',
                 array('lifetime' => 1, 'automatic_serialization' => true),
                 array('cache_dir' => __DIR__ . '/../../_files/'));
        Intl::setCache($this->_cache);
    }


    public function tearDown()
    {
        if ($this->_cache !== null) {
            $this->_cache->clean(Cache::CLEANING_MODE_ALL);
        }
    }

    /**
     * Test reading with standard locale
     */
    public function testNoLocale()
    {
        $this->assertTrue(is_array(Intl::getDisplayLanguage()));

        $this->setExpectedException('Zend\Locale\Exception\InvalidArgumentException');
        $value = Intl::getDisplayLanguage(null, 'nolocale');
    }

    /**
     * Test reading with standard locale
     */
    public function testNoLocale2()
    {
        $locale = new Locale('de');
        $this->assertTrue(is_array(Intl::getDisplayLanguage(null, $locale)));
    }

    /**
     * Test getDisplayLanguage
     */
    public function testGetDisplayLanguage()
    {
        $data = Intl::getDisplayLanguage(null, 'de');
        $this->assertEquals('Deutsch',  $data['de']);
        $this->assertEquals('Englisch', $data['en']);

        $value = Intl::getDisplayLanguage('de', 'de');
        $this->assertEquals('Deutsch', $value);

        $data = Intl::getDisplayLanguage('invalid content', 'de');
        $this->assertFalse($data);

        $data = Intl::getDisplayLanguage(null, 'de', true);
        $this->assertEquals('de', $data['Deutsch']);
        $this->assertEquals('en', $data['Englisch']);

        $data = Intl::getDisplayLanguage('Deutsch', 'de', true);
        $this->assertEquals('de', $data);

        $data = Intl::getDisplayLanguage('invalid content', 'de', true);
        $this->assertFalse($data);
    }

    /**
     * Test getDisplayScript
     */
    public function testGetDisplayScript()
    {
        $data = Intl::getDisplayScript(null, 'de_AT');
        $this->assertEquals('Arabisch',   $data['Arab']);
        $this->assertEquals('Lateinisch', $data['Latn']);

        $data = Intl::getDisplayScript('Arab', 'de_AT');
        $this->assertEquals('Arabisch', $data);

        $data = Intl::getDisplayScript('invalid content', 'de_AT');
        $this->assertFalse($data);

        $data = Intl::getDisplayScript(null, 'de_AT', true);
        $this->assertEquals('Arab', $data['Arabisch']);
        $this->assertEquals('Latn', $data['Lateinisch']);

        $data = Intl::getDisplayScript('Arabisch', 'de_AT', true);
        $this->assertEquals('Arab', $data);

        $data = Intl::getDisplayScript('invalid content', 'de_AT', true);
        $this->assertFalse($data);
    }

    /**
     * Test getDisplayTerritory
     */
    public function testGetDisplayTerritory()
    {
        $data = Intl::getDisplayTerritory(null, 'de_AT');
        $this->assertEquals('Deutschland', $data['DE']);
        $this->assertEquals('Martinique',  $data['MQ']);

        $data = Intl::getDisplayTerritory('DE', 'de_AT');
        $this->assertEquals('Deutschland', $data);

        $data = Intl::getDisplayTerritory('invalid content', 'de_AT');
        $this->assertFalse($data);

        $data = Intl::getDisplayTerritory(null, 'de_AT', true);
        $this->assertEquals('DE', $data['Deutschland']);
        $this->assertEquals('MQ', $data['Martinique']);

        $data = Intl::getDisplayTerritory('Deutschland', 'de_AT', true);
        $this->assertEquals('DE', $data);

        $data = Intl::getDisplayTerritory('invalid content', 'de_AT', true);
        $this->assertFalse($data);
    }

    /**
     * Test getDisplayVariant
     */
    public function testGetDisplayVariant()
    {
        $data = Intl::getDisplayVariant(null, 'de');
        $this->assertEquals('Boontling', $data['BOONT']);
        $this->assertEquals('Saho',      $data['SAAHO']);

        $data = Intl::getDisplayVariant('POSIX', 'de_AT');
        $this->assertEquals('Posix', $data);

        $data = Intl::getDisplayVariant('invalid content', 'de_AT');
        $this->assertFalse($data);

        $data = Intl::getDisplayVariant(null, 'de_AT', true);
        $this->assertEquals('BOONT', $data['Boontling']);
        $this->assertEquals('SAAHO', $data['Saho']);

        $data = Intl::getDisplayVariant('Posix', 'de_AT', true);
        $this->assertEquals('POSIX', $data);

        $data = Intl::getDisplayVariant('invalid content', 'de_AT', true);
        $this->assertFalse($data);
    }

    /**
     * Test getDisplayKey
     */
    public function testGetDisplayKey()
    {
        $this->setExpectedException('Zend\Locale\Exception\UnsupportedMethodException');
        $data = Intl::getDisplayKey(null, 'de_AT');
    }

    /**
     * Test getDisplayType
     */
    public function testGetDisplayType()
    {
        $this->setExpectedException('Zend\Locale\Exception\UnsupportedMethodException');
        $data = Intl::getDisplayType(null, 'de_AT');
    }

    /**
     * Test getDisplayMeasurement
     */
    public function testGetDisplayMeasurement()
    {
        $this->setExpectedException('Zend\Locale\Exception\UnsupportedMethodException');
        $data = Intl::getDisplayMeasurement(null, 'de_AT');
    }

    /**
     * Test getDisplayPattern
     */
    public function testGetDisplayPattern()
    {
        $this->setExpectedException('Zend\Locale\Exception\UnsupportedMethodException');
        $data = Intl::getDisplayPattern(null, 'de_AT');
    }

    /**
     * Test getLayout
     */
    public function testGetLayout()
    {
        $this->setExpectedException('Zend\Locale\Exception\UnsupportedMethodException');
        $data = Intl::getLayout(null, 'de_AT');
    }

    /**
     * Test getCharacter
     */
    public function testGetCharacters()
    {
        $this->setExpectedException('Zend\Locale\Exception\UnsupportedMethodException');
        $data = Intl::getCharacter(null, 'de_AT');
    }

    /**
     * Test getDelimiters
     */
    public function testGetDelimiters()
    {
        $this->setExpectedException('Zend\Locale\Exception\UnsupportedMethodException');
        $data = Intl::getDelimiter(null, 'de_AT');
    }
}
