<?php
namespace Noi\QueryPath;

use QueryPath\Extension;
use QueryPath\Query;

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
        $this->qp->remove();
    }
}
