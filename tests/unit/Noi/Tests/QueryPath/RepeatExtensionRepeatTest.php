<?php
namespace Noi\Tests\QueryPath;

class RepeatExtensionRepeatTest extends RepeatExtensionTestCase
{
    /** @test */
    public function RemovesSelectedElements_IfCounterIsZero()
    {
        // Expect
        $this->mockQueryPath->expects($this->once())
                ->method('remove');

        // Act
        $this->repeater->repeat(0);
    }

    /** @test */
    public function DoesNotInvokeGivenCallback_IfCounterIsZero()
    {
        // Expect
        $this->mockCallback->expects($this->never())
                ->method('__invoke');

        // Act
        $this->repeater->repeat(0, $this->mockCallback);
    }
}
