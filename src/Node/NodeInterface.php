<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;

interface NodeInterface
{
    /**
     * @param \Basko\Lang\EvaluateContext $context
     * @return mixed
     */
    public function evaluate(EvaluateContext $context);

    /**
     * @return string
     */
    public function toString();
}
