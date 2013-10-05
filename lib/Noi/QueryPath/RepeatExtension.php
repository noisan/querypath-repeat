<?php
namespace Noi\QueryPath;

use QueryPath\Extension;
use QueryPath\Query;
use QueryPath\Exception;
use SplObjectStorage;

/**
 * A QueryPath extension that adds extra methods for repeating selected elements.
 *
 * This extension provides two methods:
 *
 * - repeat()
 * - repeatInner()
 *
 * Usage:
 * <code>
 * <?php
 * QueryPath::enable('Noi\QueryPath\RepeatExtension');
 * $qp = qp('<?xml version="1.0"?><root><div><p>Test</p></div></root>');
 *
 * $qp->find('div')->repeat(2);
 * $qp->find('div')->repeatInner(array('Apple', 'Orange', 'Lemon'), function ($name, $node) {
 *     qp($node, 'p')->text($name);
 * });
 *
 * $qp->writeXML();
 * </code>
 *
 * OUTPUT:
 * <code>
 * <?xml version="1.0"?>
 * <root>
 *   <div>
 *     <p>Apple</p>
 *     <p>Orange</p>
 *     <p>Lemon</p>
 *   </div>
 *   <div>
 *     <p>Apple</p>
 *     <p>Orange</p>
 *     <p>Lemon</p>
 *   </div>
 * </root>
 * </code>
 *
 * @see RepeatExtension::repeat()
 * @see RepeatExtension::repeatInner()
 *
 * @author Akihiro Yamanoi <akihiro.yamanoi@gmail.com>
 */
class RepeatExtension implements Extension
{
    protected $qp;

    public function __construct(Query $qp)
    {
        $this->qp = $qp;
    }

    /**
     * Repeats each element in the current DOMQuery object.
     *
     * Usage:
     * <code>
     * <?php
     * QueryPath::enable('Noi\QueryPath\RepeatExtension');
     * $qp = qp('<?xml version="1.0"?><root><div>Test</div></root>');
     *
     * $qp->find('div')->repeat(3, function ($i, $node) {
     *     qp($node)->text('Repeat: ' . $i);
     * });
     *
     * $qp->writeXML();
     * </code>
     *
     * OUTPUT:
     * <code>
     * <?xml version="1.0"?>
     * <root>
     *   <div>Repeat: 0</div>
     *   <div>Repeat: 1</div>
     *   <div>Repeat: 2</div>
     * </root>
     * </code>
     *
     * @param integer|array|\Traversable $counter
     * @param callable|null $callback An optional callback that will be called
     *                                with each value of counter as the first argument
     *                                and each cloned node as the second argument
     * @return \QueryPath\DOMQuery The DOMQuery object, with the repeated element(s) selected
     */
    public function repeat($counter, $callback = null)
    {
        $repeated = new SplObjectStorage();

        foreach ($this->qp->get() as $templateNode) {
            $added = $this->repeatNode($templateNode, $counter, $callback);
            $repeated->addAll($added);

            $this->removeNode($templateNode);
        }

        $this->qp->setMatches($repeated);
        return $this->qp;
    }

    /**
     * Repeats the child (inner) nodes of each element in the current DOMQuery object.
     *
     * Usage:
     * <code>
     * <?php
     * QueryPath::enable('Noi\QueryPath\RepeatExtension');
     * $qp = qp('<?xml version="1.0"?><root><div><b>Test</b><i>Test</i></div></root>');
     *
     * $qp->find('div')->repeatInner(3, function ($i, $node) {
     *     qp($node, 'b')->text('Repeat: ' . $i);
     *     qp($node, 'i')->text('Repeat: ' . $i);
     * });
     *
     * $qp->writeXML();
     * </code>
     *
     * OUTPUT:
     * <code>
     * <?xml version="1.0"?>
     * <root>
     *   <div>
     *     <b>Repeat: 0</b>
     *     <i>Repeat: 0</i>
     *     <b>Repeat: 1</b>
     *     <i>Repeat: 1</i>
     *     <b>Repeat: 2</b>
     *     <i>Repeat: 2</i>
     *   </div>
     * </root>
     * </code>
     *
     * @param integer|array|\Traversable $counter
     * @param callable|null $callback  An optional callback that will be called
     *                                 with each value of counter as the first argument
     *                                 and each element
     * @return \QueryPath\DOMQuery The DOMQuery object with the same element(s) selected
     */
    public function repeatInner($counter, $callback = null)
    {
        foreach ($this->qp->get() as $templateNode) {
            $repeated = $this->repeatNode($templateNode, $counter, $callback);

            $this->removeChildren($templateNode);
            foreach ($repeated as $node) {
                $this->appendChildren($templateNode, $this->removeChildren($this->removeNode($node)));
            }
        }
        return $this->qp;
    }

    protected function repeatNode($templateNode, $counter, $callback = null)
    {
        if ($callback and !is_callable($callback)) {
            throw new Exception('Callback is not callable.');
        }

        $repeated = new SplObjectStorage();
        foreach ($this->prepareCounter($counter) as $i) {
            $ins = $templateNode->cloneNode(true);
            // modify each clone
            if ($callback and (call_user_func($callback, $i, $ins) === false)) {
                break;
            }

            if (isset($templateNode->parentNode)) {
                $ins = $templateNode->parentNode->insertBefore($ins, $templateNode);
            }
            $repeated->attach($ins);
        }
        return $repeated;
    }

    protected function prepareCounter($counter)
    {
        if (!is_scalar($counter)) {
            return $counter;
        }

        if (0 < $counter) {
            return range(0, $counter - 1);
        } else {
            return array();
        }
    }

    protected function removeNode($node)
    {
        if (isset($node->parentNode)) {
            return $node->parentNode->removeChild($node);
        } else {
            return $node;
        }
    }

    protected function removeChildren($node)
    {
        $removed = array();
        while ($node->firstChild) {
            $removed[] = $node->removeChild($node->firstChild);
        }
        return $removed;
    }

    protected function appendChildren($node, $children)
    {
        foreach ($children as $child) {
            $node->appendChild($child);
        }
    }
}
