<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Metadata\MetaNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;

/**
 * Add a meta information:
 *
 * .. meta::
 *      :key: value
 */
class Meta extends Directive
{
    public function getName(): string
    {
        return 'meta';
    }

    /**
     * @param string[] $options
     */
    public function process(
        MarkupLanguageParser $parser,
        ?Node $node,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        $document = $parser->getDocument();

        foreach ($options as $key => $value) {
            $document->addHeaderNode(new MetaNode($key, $value));
        }

        if ($node === null) {
            return null;
        }

        $document->addNode($node);
    }
}
