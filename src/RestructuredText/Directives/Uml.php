<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Graphs\Nodes\UmlNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use Webmozart\Assert\Assert;

use function dirname;
use function explode;
use function implode;
use function sprintf;
use function str_replace;

/**
 * Renders a uml diagram, example:
 *
 * .. uml::
 *    skinparam activityBorderColor #516f42
 *    skinparam activityBackgroundColor #a3dc7f
 *    skinparam shadowing false
 *
 *    start
 *    :Boot the application;
 *    :Parse files into an AST;
 *    :Transform AST into artifacts;
 *    stop
 */
final class Uml extends Directive
{
    public function getName(): string
    {
        return 'uml';
    }

    /** {@inheritDoc} */
    public function process(
        DocumentParserContext $documentParserContext,
        \phpDocumentor\Guides\RestructuredText\Parser\Directive $directive,
    ): Node|null {
        $parser = $documentParserContext->getParser();
        $parserContext = $parser->getParserContext();

        $value = implode("\n", $documentParserContext->getDocumentIterator()->toArray());

        if (empty($value)) {
            $value = $this->loadExternalUmlFile($parserContext, $directive->getData());
            if ($value === null) {
                return null;
            }
        }

        $node = new UmlNode($value);
        $node->setClasses(explode(' ', (string) $directive->getOption('classes')->getValue()));
        $node->setCaption($directive->getData());

        $document = $parser->getDocument();
        if ($directive->getVariable() !== '') {
            $document->addVariable($directive->getVariable(), $node);

            return null;
        }

        return $node;
    }

    private function loadExternalUmlFile(ParserContext $parserContext, string $path): string|null
    {
        $fileName = sprintf(
            '%s/%s',
            dirname($parserContext->getCurrentAbsolutePath()),
            $path,
        );

        if (!$parserContext->getOrigin()->has($fileName)) {
            $parserContext->addError(
                sprintf('Tried to include "%s" as a diagram but the file could not be found', $fileName),
            );

            return null;
        }

        $value = $parserContext->getOrigin()->read($fileName);
        Assert::string($value);

        return str_replace(['@startuml', '@enduml'], '', $value);
    }
}
