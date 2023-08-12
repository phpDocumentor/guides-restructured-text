<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleFactory;

/**
 * Rule to parse for default text roles such as `something`
 */
class DefaultTextRoleRule extends AbstractInlineRule
{
    public function __construct(private readonly TextRoleFactory $textRoleFactory)
    {
    }

    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::BACKTICK;
    }

    public function apply(DocumentParserContext $documentParserContext, InlineLexer $lexer): InlineNode|null
    {
        $text = '';

        $initialPosition = $lexer->token?->position;
        $lexer->moveNext();

        while ($lexer->token !== null) {
            $token = $lexer->token;
            switch ($token->type) {
                case $token->type === InlineLexer::BACKTICK:
                    if ($text === '') {
                        break 2;
                    }

                    $lexer->moveNext();

                    return $this->textRoleFactory->getDefaultTextRole()->processNode($documentParserContext, '', $text, $text);

                default:
                    $text .= $token->value;
            }

            if ($lexer->moveNext() === false && $lexer->token === null) {
                break;
            }
        }

        $this->rollback($lexer, $initialPosition ?? 0);

        return null;
    }

    public function getPriority(): int
    {
        // Must be executed after all other rules that contain single backticks
        return 20;
    }
}
