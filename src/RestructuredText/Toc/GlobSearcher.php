<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Toc;

use Flyfinder\Specification\Glob;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;

use function rtrim;

class GlobSearcher
{
    public function __construct(
        private readonly DocumentNameResolverInterface $documentNameResolver,
    ) {
    }

    /** @return string[] */
    public function globSearch(ParserContext $parserContext, string $globPattern): array
    {
        $fileSystem = $parserContext->getOrigin();
        $files = $fileSystem->find(
            new Glob(rtrim($parserContext->absoluteRelativePath(''), '/') . '/' . $globPattern),
        );
        $allFiles = [];
        foreach ($files as $file) {
            $allFiles[] = $this->documentNameResolver->absoluteUrl($parserContext->getDirName(), $file['filename']);
        }

        return $allFiles;
    }
}
