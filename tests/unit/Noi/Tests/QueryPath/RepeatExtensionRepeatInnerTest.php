<?php
namespace Noi\Tests\QueryPath;

class RepeatExtensionRepeatInnerTest extends RepeatExtensionTestCase
{
    /** @test */
    public function RemovesAllChildNodes_IfCounterIsZero()
    {
        // Setup
        $dom = $this->createDOM('<root><item>Test</item></root>');
        $expected = $this->createDOM('<root><item></item></root>');

        $this->setTargetNode($dom->getElementsByTagName('item'));

        // Act
        $this->repeater->repeatInner(0);

        // Assert
        $this->assertDomEquals($expected, $dom);
    }

    /** @test */
    public function DoesNotInvokeGivenCallback_IfCounterIsZero()
    {
        // Expect
        $this->mockCallback->expects($this->never())
                ->method('__invoke');

        // Act
        $this->repeater->repeatInner(0, $this->mockCallback);
    }

    /** @test */
    public function ReturnsCurrentQueryInstance()
    {
        // Act
        $result = $this->repeater->repeatInner($this->unused);

        // Assert
        $this->assertSame($this->mockQueryPath, $result);
    }

    /** @test */
    public function GetsSelectedNodesFromCurrentQuery()
    {
        // Expects
        $this->mockQueryPath->expects($this->atLeastOnce())
                ->method('get');

        // Act
        $this->repeater->repeatInner($this->unused);
    }

    /**
     * @test
     * @dataProvider getTestCounters
     */
    public function RepeatsInnerNodesSpecifiedTimes($counter, $name, $xml, $expected)
    {
        // Setup
        $dom = $this->createDOM($xml);
        $expected = $this->createDOM($expected);

        $this->setTargetNode($dom->getElementsByTagName($name));

        // Act
        $this->repeater->repeatInner($counter);

        // Assert
        $this->assertDomEquals($expected, $dom);
    }

    public function getTestCounters()
    {
        return array(
            array(3, 'item', '<root><item>Test</item></root>', '<root><item>TestTestTest</item></root>'),
            array(array('a', 'b'), 'p',
                '<r><p><span>Test</span><b>Test</b></p><single/></r>',
                '<r><p><span>Test</span><b>Test</b><span>Test</span><b>Test</b></p><single/></r>'),
            array(
                new \ArrayIterator(range(0, 4)),
                'it',
                '<r><it>Test<p>OK</p></it></r>',
                '<r><it>Test<p>OK</p>Test<p>OK</p>Test<p>OK</p>Test<p>OK</p>Test<p>OK</p></it></r>',
            ),
            // multi-repeat
            array(2, 'm', '<r><m>(AAA)</m><m>(BBB)</m></r>', '<r><m>(AAA)(AAA)</m><m>(BBB)(BBB)</m></r>'),
        );
    }

    /**
     * @test
     * @dataProvider getTestCounters
     */
    public function DoesNotChangeCurrentSetOfMatches($counter, $name, $xml, $expected)
    {
        // Setup
        $dom = $this->createDOM('<root><item>Test</item></root>');
        $this->setTargetNode($dom->getElementsByTagName('item'));

        // Expect
        $this->mockQueryPath->expects($this->never())
                ->method('setMatches');

        // Act
        $this->repeater->repeatInner(123);
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
        $this->repeater->repeatInner($this->unused, $testCallback);
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
        $this->repeater->repeatInner($counter, $this->mockCallback);
    }

    /**
     * @test
     * @dataProvider getTestParameters
     */
    public function InvokesCallbackWithIndexAndNode($counter, $expected)
    {
        // Setup
        $dom = $this->createDOM('<root><item><b>Test</b><i>Inner</i></item></root>');
        $node = $dom->getElementsByTagName('item')->item(0);
        $this->setTargetNode(array($node));

        // Expect
        foreach ($expected as $i => $count) {
            $this->mockCallback->expects($this->at($i))
                    ->method('__invoke')->with($count, $node);
        }

        // Act
        $this->repeater->repeatInner($counter, $this->mockCallback);
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
        $this->repeater->repeatInner($counter, $this->mockCallback);
    }

    /** @test */
    public function RepeatsChildrenOfRemovedNodes()
    {
        // Setup
        $counter = 2;

        $dom = $this->createDOM('<root><item><b>TEST</b><i>TEST</i></item></root>');
        $expected = $this->createDOM('<root/>');
        $repeated = $this->createDOM('<item><b>TEST</b><i>TEST</i><b>TEST</b><i>TEST</i></item>');

        $removed = $this->removeNode($dom->getElementsByTagName('item')->item(0));
        $this->setTargetNode(array($removed));

        // Act
        $this->repeater->repeatInner($counter);

        // Assert
        $this->assertDomEquals($expected, $dom);
        $this->assertDomEquals($repeated, $this->createDOM($dom->saveXML($removed)));
    }
}
