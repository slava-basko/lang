<?php

namespace Basko\Lang\Export;

use Basko\Lang\Node\NodeInterface;

interface ExportInterface
{
    /**
     * @param \Basko\Lang\Node\NodeInterface $node
     * @return string
     */
    public function build(NodeInterface $node);
}
