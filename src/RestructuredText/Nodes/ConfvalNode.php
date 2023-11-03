<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Nodes;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\LinkTargetNode;
use phpDocumentor\Guides\Nodes\Node;

/**
 * The confval directive configuration values.
 *
 * https://sphinx-toolbox.readthedocs.io/en/stable/extensions/confval.html
 *
 * @extends CompoundNode<Node>
 */
class ConfvalNode extends CompoundNode implements LinkTargetNode
{
    public const LINK_TYPE = 'std:confval';

    /**
     * @param list<Node> $value
     * @param array<string, InlineCompoundNode>  $additionalOptions
     */
    public function __construct(
        private readonly string $id,
        private readonly string $plainContent,
        private readonly InlineCompoundNode|null $type = null,
        private readonly bool $required = false,
        private readonly InlineCompoundNode|null $default = null,
        private readonly array $additionalOptions = [],
        array $value = [],
    ) {
        parent::__construct($value);
    }

    public function getPlainContent(): string
    {
        return $this->plainContent;
    }

    public function getLinkType(): string
    {
        return self::LINK_TYPE;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLinkText(): string
    {
        return $this->plainContent;
    }

    public function getType(): InlineCompoundNode|null
    {
        return $this->type;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getDefault(): InlineCompoundNode|null
    {
        return $this->default;
    }

    /** @return array<string,InlineCompoundNode> */
    public function getAdditionalOptions(): array
    {
        return $this->additionalOptions;
    }
}
