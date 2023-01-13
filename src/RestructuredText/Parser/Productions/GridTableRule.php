<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LineChecker;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\GridTableBuilder;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\TableBuilder;

use phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\TableSeparatorLineConfig;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#grid-tables
 * @implements Rule<TableNode>
 */
final class GridTableRule implements Rule
{
    private GridTableBuilder $builder;
    private RuleContainer $productions;

    public function __construct(RuleContainer $productions)
    {
        $this->builder = new GridTableBuilder();
        $this->productions = $productions;
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return $this->isColumnDefinitionLine($documentParser->getDocumentIterator()->current());
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        $line = $documentIterator->current();

        $tableSeparatorLineConfig = $this->tableLineConfig($line, '-');
        $context = new ParserContext();
        $context->pushSeparatorLine($tableSeparatorLineConfig);
        $context->pushSeparatorLine($tableSeparatorLineConfig);

        while ($documentIterator->getNextLine() !== null) {
            $documentIterator->next();

            if ($this->isHeaderDefinitionLine($documentIterator->current())) {
                $separatorLineConfig = $this->tableLineConfig($documentIterator->current(), '=');
                $context->pushSeparatorLine($separatorLineConfig);
                continue;
            }

            if ($this->isColumnDefinitionLine($documentIterator->current())) {
                $separatorLineConfig = $this->tableLineConfig($documentIterator->current(), '-');
                $context->pushSeparatorLine($separatorLineConfig);
                // if an empty line follows a separator line, then it is the end of the table
                if (LinesIterator::isEmptyLine($documentIterator->current())) {
                    break;
                }

                continue;
            }

            $context->pushContentLine($documentIterator->current());
        }

        return $this->builder->buildNode($context, $documentParserContext, $this->productions);
    }

    private function tableLineConfig(string $line, string $char): TableSeparatorLineConfig
    {
        $parts = [];
        $strlen = strlen($line);

        $currentPartStart = 1;
        for ($i = 1; $i < $strlen; $i++) {
            if ($line[$i] === '+') {
                $parts[] = [$currentPartStart, $i];
                $currentPartStart = ++$i;
            }
        }

        return new TableSeparatorLineConfig(
            $char === '=',
            $parts,
            $char,
            $line
        );
    }

    private function isColumnDefinitionLine(string $line): bool
    {
        return $this->isDefintionLine($line, '-');
    }

    private function isHeaderDefinitionLine(string $line): bool
    {
        return $this->isDefintionLine($line, '=');
    }

    private function isDefintionLine(string $line, string $char): bool
    {
        return preg_match("/^(?:\+$char+)+\+$/", trim($line)) > 0;
    }
}
