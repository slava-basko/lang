<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;

interface NodeInterface
{
    public function evaluate(EvaluateContext $context);

    public function toString();
}