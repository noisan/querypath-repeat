<?php
namespace Noi\QueryPath;

use QueryPath\Extension;
use QueryPath\Query;
use QueryPath\Exception;
use SplObjectStorage;

/**
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
            $node->parentNode->removeChild($node);
        }
    }
}
