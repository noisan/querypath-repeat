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

    public function testRepeatInner_EmptyQuery_RepeatsNothing()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root />';

        // Act
        $qp = qp($testXML)->find('item')->repeatInner(5);

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

    public function testRepeatInner_ZeroTimes_RemovesChildrenOfSelectedNode()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root><item>TEST</item></root>';
        $expected = '<?xml version="1.0"?><root><item></item></root>';

        // Act
        $qp = qp($testXML)->find('item')->repeatInner(0);

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

    public function testRepeatInner_SingleNode_RepeatsChildrenOfTargetNode()
    {
        // Setup
        $counter = 2;
        $testXML = '<?xml version="1.0"?><root><item><b>Child A</b><i>Child B</i></item></root>';
        $expected = '<?xml version="1.0"?><root><item><b>Child A</b><i>Child B</i><b>Child A</b><i>Child B</i></item></root>';

        // Act
        $qp = qp($testXML)->find('item')->repeatInner($counter);

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

    public function testRepeatInner_MultipleNodes_RepeatsChildrenOfEachSelectedNodes()
    {
        // Setup
        $counter = 3;
        $testXML = '<?xml version="1.0"?><root>' .
                '<div><item><i>A1</i><p>A2</p></item></div>' .
                '<div><item><i>B1</i><p>B2</p></item></div>' .
                '</root>';
        $expected = '<?xml version="1.0"?><root>' .
                '<div><item><i>A1</i><p>A2</p><i>A1</i><p>A2</p><i>A1</i><p>A2</p></item></div>' .
                '<div><item><i>B1</i><p>B2</p><i>B1</i><p>B2</p><i>B1</i><p>B2</p></item></div>' .
                '</root>';

        // Act
        $qp = qp($testXML)->find('item')->repeatInner($counter);

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

    public function testRepeatInner_RemovedNode_RepeatsChildrenOfRemovedNode()
    {
        // Setup
        $counter = 2;
        $testXML = '<?xml version="1.0"?><root>' .
                '<from><item><p>TEST</p></item></from>'.
                '<to />' .
                '</root>';
        $expected = '<?xml version="1.0"?><root>' .
                '<from />'.
                '<to><item><p>TEST</p><p>TEST</p></item></to>' .
                '</root>';

        $qp = qp($testXML);
        $dest = $qp->find('to');

        // Act
        $removed = $qp->remove('item');
        $removed->repeatInner($counter)->appendTo($dest);

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

    public function testRepeatInner_CallbackGiven_InvokesCallbackOnEachRepeat()
    {
        // Setup
        $callback = function ($i, $node) {
            $p = qp($node)->find('p');
            $p->text(strtoupper($p->text() . ':' . $i));
        };

        $counter = 2;
        $testXML = '<?xml version="1.0"?><root><item><p>test</p></item></root>';
        $expected = '<?xml version="1.0"?><root><item><p>TEST:0</p><p>TEST:1</p></item></root>';

        // Act
        $qp = qp($testXML)->find('item')->repeatInner($counter, $callback);

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

    public function testRepeatInner_ArrayCounterGiven_InvokesCallbackWithEachArrayElement()
    {
        // Setup
        $counter = array('A', 'B', 'C');

        $callback = function ($i, $node) {
            $node->nodeValue = $i;
        };
        $testXML = '<?xml version="1.0"?><root><item>Test</item></root>';
        $expected = '<?xml version="1.0"?><root><item>ABC</item></root>';

        // Act
        $qp = qp($testXML)->find('item')->repeatInner($counter, $callback);

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

    public function testRepeatInner_TraversableCounterGiven_InvokesCallbackWithEachIteratedValue()
    {
        // Setup
        $counter = new \RegexIterator(new \ArrayIterator(array(
            'Apple', 'Banana', 'Kiwi', 'Lemon', 'Orange', 'Peach',
        )), '/e$/');

        $callback = function ($fruit, $node) {
            qp($node)->find('p')->text($fruit);
        };
        $testXML = '<?xml version="1.0"?><root><item><p>Test</p></item></root>';
        $expected = '<?xml version="1.0"?><root><item><p>Apple</p><p>Orange</p></item></root>';

        // Act
        $qp = qp($testXML)->find('item')->repeatInner($counter, $callback);

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

    public function testRepeatInner_CallbackReturningFalse_BreaksRepeat()
    {
        // Setup
        $counter = 10;
        $callback = function ($i, $unused) {
            if (3 <= $i) {
                return false;
            }
        };

        $testXML = '<?xml version="1.0"?><root><item>Test</item></root>';
        $expected = '<?xml version="1.0"?><root><item>TestTestTest</item></root>';

        // Act
        $qp = qp($testXML)->find('item')->repeatInner($counter, $callback);

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

    public function testRepeatInner_ReturnsQueryPathInstance()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root><item><p>A</p></item><item><p>B</p></item></root>';
        $expected = '<?xml version="1.0"?><root><item><p>A</p><p>A</p></item>OK<item><p>B</p><p>B</p></item>OK</root>';
        $qp = qp($testXML)->find('item');

        // Act
        $result = $qp->repeatInner(2);
        $result->textAfter('OK');

        // Assert
        $this->assertInstanceOf(get_class($qp), $result);
        $this->assertDomEqualsXmlString($expected, $qp);
    }
}
