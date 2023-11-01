<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\SectionNode;

final class GenericLinkProvider
{
    /** @var array<string, string> */
    private array $textRoleLinkTypeMapping = [
        'ref' => SectionNode::STD_LABEL,
    ];

    public function addGenericLink(string $textRole, string $linkType): void
    {
        $this->textRoleLinkTypeMapping[$textRole] = $linkType;
    }

    /** @return string[] */
    public function getTextRoleLinkTypeMapping(): array
    {
        return $this->textRoleLinkTypeMapping;
    }

    public function getLinkType(string $textRole): string
    {
        return $this->textRoleLinkTypeMapping[$textRole] ?? SectionNode::STD_LABEL;
    }
}
