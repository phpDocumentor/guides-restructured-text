<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\Table;

use InvalidArgumentException;

use function in_array;
use function sprintf;

final class TableSeparatorLineConfig
{
    /** @param int[][] $partRanges */
    public function __construct(
        private bool $isHeader,
        private array $partRanges,
        private string $lineCharacter,
        private string $rawContent,
    ) {
        if (!in_array($lineCharacter, ['=', '-'], true)) {
            throw new InvalidArgumentException(sprintf('Unexpected line character "%s"', $lineCharacter));
        }
    }

    public function isHeader(): bool
    {
        return $this->isHeader;
    }

    /**
     * Returns an array of position "ranges" where content should exist.
     *
     * For example:
     *      ===   =====   === ===
     *
     * Would yield:
     *      [[0, 3], [6, 11], [14, 17], [18, 21]]
     *
     * @return int[][]
     */
    public function getPartRanges(): array
    {
        return $this->partRanges;
    }

    /**
     * Returns the "line" character used in the separator,
     * either - or =
     */
    public function getLineCharacter(): string
    {
        return $this->lineCharacter;
    }

    public function getRawContent(): string
    {
        return $this->rawContent;
    }
}
