<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\Nodes\Inline\VariableInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

/**
 * Rule to parse for inline variables such as |replace_me|
 */
class VariableInlineRule extends AbstractInlineRule
{
    public function applies(InlineLexer $lexer): bool
    {
        return $lexer->token?->type === InlineLexer::VARIABLE_DELIMITER;
    }

    public function apply(DocumentParserContext $documentParserContext, InlineLexer $lexer): InlineNode|null
    {
        $text = '';

        $initialPosition = $lexer->token?->position;
        $lexer->moveNext();

        while ($lexer->token !== null) {
            $token = $lexer->token;
            switch ($token->type) {
                case $token->type === InlineLexer::VARIABLE_DELIMITER:
                    if ($text === '') {
                        break 2;
                    }

                    $lexer->moveNext();

                    return new VariableInlineNode($text);

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
        return 200;
    }
}
