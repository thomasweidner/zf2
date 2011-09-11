<?php

namespace ZendTest\Code\Scanner\DerivedScanner;

use Zend\Code\Scanner\DirectoryScanner,
    Zend\Code\Scanner\AggregateDirectoryScanner,
    Zend\Code\Scanner\DerivedClassScanner;

class DerivedClassScannerTest extends \PHPUnit_Framework_TestCase
{

    public function testCreatesClass()
    {
        $this->markTestSkipped('Skipped for ZF2 until implementation or test has been fixed');

        $ds = new DirectoryScanner();
        $ds->addDirectory(__DIR__ . '/../TestAsset');
        $ads = new AggregateDirectoryScanner();
        $ads->addScanner($ds);
        $c = $ads->getClass('ZendTest\Code\Scanner\TestAsset\MapperExample\RepositoryB');
        //echo $c->getName();
        //var_dump($c->getMethods(true));
        var_dump($c->getProperties());
    }


}