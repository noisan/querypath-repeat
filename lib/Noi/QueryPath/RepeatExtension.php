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
        }

        $this->qp->remove();  // remove template nodes
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
            $templateNode->parentNode->insertBefore($ins, $templateNode);
            $repeated->attach($templateNode->previousSibling);
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
}
