<?php
namespace Noi\Tests\QueryPath;

class RepeatExtensionRepeatTest extends RepeatExtensionTestCase
{
    /** @test */
    public function RemovesSelectedElements_IfCounterIsZero()
    {
        // Setup
        $dom = $this->createDOM('<root><item>Test</item></root>');
        $expected = $this->createDOM('<root></root>');

        $this->setTargetNode($dom->getElementsByTagName('item'));

        // Act
        $this->repeater->repeat(0);

        // Assert
        $this->assertEqualXMLStructure($expected->documentElement, $dom->documentElement);
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

    /** @test */
    public function GetsSelectedNodesFromCurrentQuery()
    {
        // Expects
        $this->mockQueryPath->expects($this->once())
                ->method('get')->with($this->isNull(), $this->isFalse());

        // Act
        $this->repeater->repeat($this->unused);
    }

    /** @test */
    public function ReturnsCurrentQueryInstance()
    {
        // Act
        $result = $this->repeater->repeat($this->unused);

        // Assert
        $this->assertSame($this->mockQueryPath, $result);
    }

    /**
     * @test
     * @dataProvider getTestCounters
     */
    public function RepeatsNodeSpecifiedTimes($counter, $name, $xml, $expected)
    {
        // Setup
        $dom = $this->createDOM($xml);
        $expected = $this->createDOM($expected);

        $this->setTargetNode($dom->getElementsByTagName($name));

        // Act
        $this->repeater->repeat($counter);

        // Assert
        $this->assertEqualXMLStructure($expected->documentElement, $dom->documentElement);
    }

    public function getTestCounters()
    {
        return array(
            array(3, 'item', '<root><item>Test</item></root>', '<root><item>Test</item><item>Test</item><item>Test</item></root>'),
            array(array('a', 'b'), 'i', '<r><i>Test</i></r>', '<r><i>Test</i><i>Test</i></r>'),
            array(
                new \ArrayIterator(range(0, 4)),
                'it',
                '<r><it>Test</it></r>',
                '<r><it>Test</it><it>Test</it><it>Test</it><it>Test</it><it>Test</it></r>',
            ),
            // multi-repeat
            array(2, 'm', '<r><m>AAA</m><m>BBB</m></r>', '<r><m>AAA</m><m>AAA</m><m>BBB</m><m>BBB</m></r>'),
        );
    }

    /** @test */
    public function SetsRepeatedNodesInCurrentQuery()
    {
        // Setup
        $counter = 5;

        $dom = $this->createDOM('<root><item>Test</item></root>');
        $this->setTargetNode($dom->getElementsByTagName('item'));

        // Expect
        $this->mockQueryPath->expects($this->once())
                ->method('setMatches')->with($this->countOf($counter));

        // Act
        $this->repeater->repeat($counter);
    }

    /**
     * @test
     * @expectedException \QueryPath\Exception
     */
    public function ThrowsException_OnInvalidCallback()
    {
        // Setup
        $testCallback = 'invalid_callback';

        $this->setTargetNode($this->createDOM('<dummy/>')->childNodes);

        // Act
        $this->repeater->repeat($this->unused, $testCallback);
    }

    /** @test */
    public function InvokesCallbackOnEachClonedNode()
    {
        // Setup
        $counter = 3;
        $this->setTargetNode($this->createDOM('<dummy/>')->childNodes);

        // Expect
        $this->mockCallback->expects($this->exactly($counter))
                ->method('__invoke');

        // Act
        $this->repeater->repeat($counter, $this->mockCallback);
    }

    /** @test */
    public function BreaksOutOfRepeatLoop_IfCallbackReturnsFalse()
    {
        // Setup
        $counter = 100;
        $this->setTargetNode($this->createDOM('<dummy/>')->childNodes);

        // Expect
        $this->mockCallback->expects($this->exactly(3))
                ->method('__invoke')
                ->will($this->onConsecutiveCalls(true, null, false));

        // Act
        $this->repeater->repeat($counter, $this->mockCallback);
    }

    /**
     * @test
     * @dataProvider getTestParameters
     */
    public function InvokesCallbackWithIndexAndNode($counter, $expected)
    {
        // Setup
        $dom = $this->createDOM('<root><item /></root>');
        $node = $dom->getElementsByTagName('item')->item(0);
        $this->setTargetNode(array($node));

        // Expect
        foreach ($expected as $i => $count) {
            $this->mockCallback->expects($this->at($i))
                    ->method('__invoke')->with($count, $node);
        }

        // Act
        $this->repeater->repeat($counter, $this->mockCallback);
    }

    public function getTestParameters()
    {
        return array(
            array(3, array(0, 1, 2)),
            array(array('a', 'b'), array('a', 'b')),
            array(new \ArrayIterator(range(1, 5)), array(1, 2, 3, 4, 5)),
        );
    }

    /** @test */
    public function RepeatsRemovedNodes()
    {
        // Setup
        $counter = 2;

        $dom = $this->createDOM('<root><item>TEST</item></root>');
        $removed = $this->removeNode($dom->getElementsByTagName('item')->item(0));

        $this->setTargetNode(array($removed));

        // Expect
        $this->mockQueryPath->expects($this->once())
                ->method('setMatches')->with($this->logicalAnd(
                            $this->countOf($counter),
                            $this->containsOnlyInstancesOf($removed)));

        // Act
        $this->repeater->repeat($counter);

        // Assert
        $this->assertEqualXMLStructure(
                $this->createDOM('<root />')->documentElement, $dom->documentElement);
    }
}
