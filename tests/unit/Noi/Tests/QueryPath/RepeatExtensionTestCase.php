<?php
namespace Noi\Tests\QueryPath;

use Noi\QueryPath\RepeatExtension;
use DOMDocument;
use SplObjectStorage;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_Constraint_Count;

abstract class RepeatExtensionTestCase extends PHPUnit_Framework_TestCase
{
    protected $repeater;
    protected $mockQueryPath;
    protected $mockCallback;
    protected $nodeStorage;
    protected $unused = null;

    public function setUp()
    {
        $this->nodeStorage = $this->createNodeStorage();
        $this->mockQueryPath = $this->createMockQueryPath($this->nodeStorage);
        $this->repeater = $this->createRepeatExtension($this->mockQueryPath);

        $this->mockCallback = $this->createMockCallback();
    }

    protected function createRepeatExtension($qp)
    {
        return new RepeatExtension($qp);
    }

    protected function createNodeStorage()
    {
        return new SplObjectStorage();
    }

    protected function createMockQueryPath($nodeStorage)
    {
        $mock = $this->getMockBuilder('QueryPath\DOMQuery')
                ->disableOriginalConstructor()->getMock();

        $mock->expects($this->any())
                ->method('get')
                ->will($this->returnValue($nodeStorage));

        return $mock;
    }

    protected function createMockCallback()
    {
        return $this->getMockBuilder('stdClass')
                ->setMethods(array('__invoke'))->getMock();
    }

    protected function createDOM($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        return $dom;
    }

    protected function setTargetNode($nodes)
    {
        $this->nodeStorage->removeAll($this->nodeStorage);
        foreach ($nodes as $node) {
            $this->nodeStorage->attach($node);
        }
    }

    protected function countOf($count)
    {
        return new PHPUnit_Framework_Constraint_Count($count);
    }

    protected function removeNode($node)
    {
        return $node->parentNode->removeChild($node);
    }

    protected function assertDomEquals($expected, $actual)
    {
        $this->assertXmlStringEqualsXmlString(
                $expected->saveXML(), $actual->saveXML());
    }
}
