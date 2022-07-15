<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionList;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionListTerm;
use phpDocumentor\Guides\Nodes\Links\Link;
use phpDocumentor\Guides\Nodes\Lists\ListItem;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;

use function array_map;
use function count;
use function explode;
use function preg_match;
use function strlen;
use function substr;
use function trim;

class LineDataParser
{
    private MarkupLanguageParser $parser;
    private SpanParser $spanParser;

    public function __construct(SpanParser $spanParser)
    {
        $this->spanParser = $spanParser;
    }

    public function parseLink(string $line): ?Link
    {
        // Links
        if (preg_match('/^\.\. _`(.+)`: (.+)$/mUsi', $line, $match) > 0) {
            return $this->createLink($match[1], $match[2], Link::TYPE_LINK);
        }

        // anonymous links
        if (preg_match('/^\.\. _(.+): (.+)$/mUsi', $line, $match) > 0) {
            return $this->createLink($match[1], $match[2], Link::TYPE_LINK);
        }

        // Short anonymous links
        if (preg_match('/^__ (.+)$/mUsi', trim($line), $match) > 0) {
            $url = $match[1];

            return $this->createLink('_', $url, Link::TYPE_LINK);
        }

        // Anchor links - ".. _`anchor-link`:"
        if (preg_match('/^\.\. _`(.+)`:$/mUsi', trim($line), $match) > 0) {
            $anchor = $match[1];

            return new Link($anchor, '#' . $anchor, Link::TYPE_ANCHOR);
        }

        if (preg_match('/^\.\. _(.+):$/mUsi', trim($line), $match) > 0) {
            $anchor = $match[1];

            return $this->createLink($anchor, '#' . $anchor, Link::TYPE_ANCHOR);
        }

        return null;
    }

    private function createLink(string $name, string $url, string $type): Link
    {
        return new Link($name, $url, $type);
    }

    public function parseDirectiveOption(string $line): ?DirectiveOption
    {
        if (preg_match('/^(\s+):(.+): (.*)$/mUsi', $line, $match) > 0) {
            return new DirectiveOption($match[2], trim($match[3]));
        }

        if (preg_match('/^(\s+):(.+):(\s*)$/mUsi', $line, $match) > 0) {
            return new DirectiveOption($match[2], true);
        }

        return null;
    }

    public function parseDirective(string $line): ?Directive
    {
        if (preg_match('/^\.\. (\|(.+)\| |)([^\s]+)::( (.*)|)$/mUsi', $line, $match) > 0) {
            return new Directive(
                $match[2],
                $match[3],
                trim($match[4])
            );
        }

        return null;
    }

    public function parseListLine(string $line): ?ListItem
    {
        $depth = 0;

        for ($i = 0; $i < strlen($line); $i++) {
            $char = $line[$i];

            if ($char === ' ') {
                $depth++;
            } elseif ($char === "\t") {
                $depth += 2;
            } else {
                break;
            }
        }

        if (preg_match('/^((\*|\-)|([\d#]+)\.) (.+)$/', trim($line), $match) > 0) {
            return new ListItem(
                $line[$i],
                $line[$i] !== '*' && $line[$i] !== '-',
                $depth,
                [$match[4]]
            );
        }

        if (strlen($line) === 1 && $line[0] === '-') {
            return new ListItem(
                $line[$i],
                $line[$i] !== '*' && $line[$i] !== '-',
                $depth,
                ['']
            );
        }

        return null;
    }

    /**
     * @param string[] $lines
     */
    public function parseDefinitionList(DocumentParserContext $documentParserContext, array $lines): DefinitionList
    {
        /** @var array{term: SpanNode, classifiers: list<SpanNode>, definition: string}|null $definitionListTerm */
        $definitionListTerm = null;
        $definitionList     = [];

        $createDefinitionTerm = function (array $definitionListTerm) use ($documentParserContext): ?DefinitionListTerm {
            // parse any markup in the definition (e.g. lists, directives)
            $definitionNodes = $documentParserContext->getParser()->parseFragment($definitionListTerm['definition'])
                ->getNodes();
            if (empty($definitionNodes)) {
                return null;
            } elseif (count($definitionNodes) === 1 && $definitionNodes[0] instanceof ParagraphNode) {
                // if there is only one paragraph node, the value is put directly in the <dd> element
                $definitionNodes = [$definitionNodes[0]->getValue()];
            } else {
                // otherwise, .first and .last are added to the first and last nodes of the definition
                $definitionNodes[0]->setClasses($definitionNodes[0]->getClasses() + ['first']);
                $definitionNodes[count($definitionNodes) - 1]
                    ->setClasses($definitionNodes[count($definitionNodes) - 1]->getClasses() + ['last']);
            }

            return new DefinitionListTerm(
                $definitionListTerm['term'],
                $definitionListTerm['classifiers'],
                $definitionNodes
            );
        };

        $currentOffset = 0;
        foreach ($lines as $key => $line) {
            // indent or empty line = term definition line
            if ($definitionListTerm !== null && (trim($line) === '') || $line[0] === ' ') {
                if ($currentOffset === 0) {
                    // first line of a definition determines the indentation offset
                    $definition    = ltrim($line);
                    $currentOffset = strlen($line) - strlen($definition);
                } else {
                    $definition = substr($line, $currentOffset);
                }

                $definitionListTerm['definition'] .= $definition . "\n";

                // non empty string at the start of the line = definition term
            } elseif (trim($line) !== '') {
                // we are starting a new term so if we have an existing
                // term with definitions, add it to the definition list
                if ($definitionListTerm !== null) {
                    $definitionList[] = $createDefinitionTerm($definitionListTerm);
                }

                $parts = explode(':', trim($line));

                $term = $parts[0];
                unset($parts[0]);

                $classifiers = array_map(function (string $classifier) use ($documentParserContext): SpanNode {
                    return $this->spanParser->parse($classifier, $documentParserContext->getContext());
                }, array_map('trim', $parts));

                $currentOffset      = 0;
                $definitionListTerm = [
                    'term' => $this->spanParser->parse($term, $documentParserContext->getContext()),
                    'classifiers' => $classifiers,
                    'definition' => '',
                ];
            }
        }

        // append the last definition of the list
        if ($definitionListTerm !== null) {
            $definitionList[] = $createDefinitionTerm($definitionListTerm);
        }

        return new DefinitionList($definitionList);
    }
}
