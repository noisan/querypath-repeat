<?php
namespace Noi\Tests\QueryPath;

use Noi\QueryPath\RepeatExtension;

abstract class RepeatExtensionTestCase extends \PHPUnit_Framework_TestCase
{
    protected $repeater;
    protected $mockQueryPath;
    protected $mockCallback;

    public function setUp()
    {
        $this->mockQueryPath = $this->createMockQueryPath();
        $this->repeater = $this->createRepeatExtension($this->mockQueryPath);

        $this->mockCallback = $this->createMockCallback();
    }

    protected function createRepeatExtension($qp)
    {
        return new RepeatExtension($qp);
    }

    protected function createMockQueryPath()
    {
        return $this->getMockBuilder('QueryPath\DOMQuery')
                ->disableOriginalConstructor()->getMock();
    }

    protected function createMockCallback()
    {
        return $this->getMockBuilder('stdClass')
                ->setMethods(array('__invoke'))->getMock();
    }
}
