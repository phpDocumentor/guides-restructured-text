<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use phpDocumentor\Guides\Nodes\Inline\InlineMarkupToken;
use phpDocumentor\Guides\Nodes\Inline\LiteralToken;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

/**
 * Rule to parse for default text roles such as `something`
 */
class DefaultTextRoleRule extends AbstractInlineRule
{
    public function applies(SpanLexer $lexer): bool
    {
        return $lexer->token?->type === SpanLexer::BACKTICK;
    }

    public function apply(ParserContext $parserContext, SpanLexer $lexer): InlineMarkupToken|null
    {
        $text = '';

        $initialPosition = $lexer->token?->position;
        $lexer->moveNext();

        while ($lexer->token !== null) {
            $token = $lexer->token;
            switch ($token->type) {
                case $token->type === SpanLexer::BACKTICK:
                    if ($text === '') {
                        break 2;
                    }

                    $lexer->moveNext();

                    return new LiteralToken($text);

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