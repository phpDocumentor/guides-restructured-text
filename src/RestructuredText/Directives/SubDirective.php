<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\CollectionNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;

/**
 * A directive that parses the sub block and call the processSub that can
 * be overloaded, like :
 *
 * .. sub-directive::
 *      Some block of code
 *
 *      You can imagine anything here, like adding *emphasis*, lists or
 *      titles
 */
abstract class SubDirective extends BaseDirective
{
    /** @param Rule<CollectionNode> $startingRule */
    public function __construct(protected Rule $startingRule)
    {
    }
    
    /** {@inheritDoc} */
    final public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        $collectionNode = $this->getStartingRule()->apply($blockContext);

        if ($collectionNode === null) {
            return null;
        }

        $node = $this->processSub($collectionNode, $directive);

        if ($node === null) {
            return null;
        }

        return $node->withDefaultOptions($this->optionsToArray($directive->getOptions()));
    }

    /** @return Rule<CollectionNode> */
    protected function getStartingRule(): Rule
    {
        return $this->startingRule;
    }

    protected function processSub(
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        return null;
    }
}
