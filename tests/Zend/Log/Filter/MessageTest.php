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
 * @package    Zend_Log
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\Log\Filter;

use \Zend\Log\Logger,
    \Zend\Log\Filter\Message,
    \Zend\Config\Config;

/**
 * @category   Zend
 * @package    Zend_Log
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Log
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testMessageFilterRecognizesInvalidRegularExpression()
    {
        $this->markTestSkipped('Skipped for ZF2 until implementation or test has been fixed');

        $this->setExpectedException('Zend\Log\Exception\InvalidArgumentException', 'invalid reg');
        $filter = new Message('invalid regexp');
    }

    public function testMessageFilter()
    {
        $this->markTestSkipped('Skipped for ZF2 until implementation or test has been fixed');

        $filter = new Message('/accept/');
        $this->assertTrue($filter->accept(array('message' => 'foo accept bar')));
        $this->assertFalse($filter->accept(array('message' => 'foo reject bar')));
    }

    public function testFactory()
    {
        $this->markTestSkipped('Skipped for ZF2 until implementation or test has been fixed');

        $cfg = array('log' => array('memory' => array(
            'writerName'   => "Mock",
            'filterName'   => "Message",
            'filterParams' => array(
                'regexp'   => "/42/"
             ),
        )));

        $logger = Logger::factory($cfg['log']);
        $this->assertTrue($logger instanceof Logger);
    }

    public function testFactoryWithConfig()
    {
        $this->markTestSkipped('Skipped for ZF2 until implementation or test has been fixed');

        $config = new Config(array('log' => array('memory' => array(
            'writerName'   => "Mock",
            'filterName'   => "Message",
            'filterParams' => array(
                'regexp'   => "/42/"
             ),
        ))));

        $filter = Message::factory($config->log->memory->filterParams);
        $this->assertTrue($filter instanceof Message);
    }
}
