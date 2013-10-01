<?php
namespace Noi\Tests\QueryPath;

use QueryPath;

class RepeatExtensionIntegrationTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        QueryPath::enable('Noi\QueryPath\RepeatExtension');
    }

    protected function assertDomEqualsXmlString($expectedXml, $actualDom)
    {
        $this->assertXmlStringEqualsXmlString($expectedXml, $actualDom->top()->xml());
    }

    public function testRepeat_EmptyQuery_RepeatsNothing()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root />';

        // Act
        $qp = qp($testXML)->find('item')->repeat(5);

        // Assert
        $this->assertDomEqualsXmlString($testXML, $qp);
    }

    public function testRepeat_ZeroTimes_RemovesSelectedNode()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root><item>TEST</item></root>';
        $expected = '<?xml version="1.0"?><root></root>';

        // Act
        $qp = qp($testXML)->find('item')->repeat(0);

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testRepeat_SingleNode_RepeatsTargetNode()
    {
        // Setup
        $counter = 2;
        $testXML = '<?xml version="1.0"?><root><item>TEST</item></root>';
        $expected = '<?xml version="1.0"?><root><item>TEST</item><item>TEST</item></root>';

        // Act
        $qp = qp($testXML)->find('item')->repeat($counter);

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testRepeat_MultipleNodes_RepeatsTargetNodes()
    {
        // Setup
        $counter = 3;
        $testXML = '<?xml version="1.0"?><root>' .
                '<div><item>A</item></div>' .
                '<div><item>B</item></div>' .
                '</root>';
        $expected = '<?xml version="1.0"?><root>' .
                '<div><item>A</item><item>A</item><item>A</item></div>' .
                '<div><item>B</item><item>B</item><item>B</item></div>' .
                '</root>';

        // Act
        $qp = qp($testXML)->find('item')->repeat($counter);

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testRepeat_RemovedNode_RepeatsRemovedNode()
    {
        // Setup
        $counter = 2;
        $testXML = '<?xml version="1.0"?><root>' .
                '<from><item>TEST</item></from>'.
                '<to />' .
                '</root>';
        $expected = '<?xml version="1.0"?><root>' .
                '<from />'.
                '<to><item>TEST</item><item>TEST</item></to>' .
                '</root>';

        $qp = qp($testXML);
        $dest = $qp->find('to');

        // Act
        $removed = $qp->remove('item');
        $removed->repeat($counter)->appendTo($dest);

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testRepeat_CallbackGiven_InvokesCallbackOnEachRepeat()
    {
        // Setup
        $callback = function ($i, $node) {
            $node->nodeValue = strtoupper($node->nodeValue) . ':' . $i;
        };

        $counter = 2;
        $testXML = '<?xml version="1.0"?><root><item>test</item></root>';
        $expected = '<?xml version="1.0"?><root><item>TEST:0</item><item>TEST:1</item></root>';

        // Act
        $qp = qp($testXML)->find('item')->repeat($counter, $callback);

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testRepeat_ArrayCounterGiven_InvokesCallbackWithEachArrayElement()
    {
        // Setup
        $counter = array('A', 'B', 'C');

        $callback = function ($i, $node) {
            $node->nodeValue = $i;
        };
        $testXML = '<?xml version="1.0"?><root><item>Test</item></root>';
        $expected = '<?xml version="1.0"?><root><item>A</item><item>B</item><item>C</item></root>';

        // Act
        $qp = qp($testXML)->find('item')->repeat($counter, $callback);

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testRepeat_TraversableCounterGiven_InvokesCallbackWithEachIteratedValue()
    {
        // Setup
        $counter = new \RegexIterator(new \ArrayIterator(array(
            'Apple', 'Banana', 'Kiwi', 'Lemon', 'Orange', 'Peach',
        )), '/e$/');

        $callback = function ($fruit, $node) {
            $node->nodeValue = $fruit;
        };
        $testXML = '<?xml version="1.0"?><root><item>Test</item></root>';
        $expected = '<?xml version="1.0"?><root><item>Apple</item><item>Orange</item></root>';

        // Act
        $qp = qp($testXML)->find('item')->repeat($counter, $callback);

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testRepeat_CallbackReturningFalse_BreaksRepeat()
    {
        // Setup
        $counter = 10;
        $callback = function ($i, $unused) {
            if (3 <= $i) {
                return false;
            }
        };

        $testXML = '<?xml version="1.0"?><root><item>Test</item></root>';
        $expected = '<?xml version="1.0"?><root><item>Test</item><item>Test</item><item>Test</item></root>';

        // Act
        $qp = qp($testXML)->find('item')->repeat($counter, $callback);

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testRepeat_ReturnsQueryPathInstance()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root><item>Test</item></root>';
        $expected = '<?xml version="1.0"?><root><item>Test</item>OK<item>Test</item>OK<item>Test</item>OK</root>';
        $qp = qp($testXML)->find('item');

        // Act
        $result = $qp->repeat(3);
        $result->textAfter('OK');

        // Assert
        $this->assertInstanceOf(get_class($qp), $result);
        $this->assertDomEqualsXmlString($expected, $qp);
    }
}
