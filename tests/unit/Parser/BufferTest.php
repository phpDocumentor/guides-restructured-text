<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use PHPUnit\Framework\TestCase;

use function explode;

final class BufferTest extends TestCase
{
    public function testItDetectsIndent(): void
    {
        $code = <<<'CODE'
               if (true) {
                   echo 'Hello world';
               }
            CODE;

        $buffer = new Buffer(explode("\n", $code), UnindentStrategy::ALL);
        self::assertSame(<<<'CODE'
        if (true) {
            echo 'Hello world';
        }
        CODE, $buffer->getLinesString());
    }

    /* public function testItDetectsIndentForLists(): void */
    /* { */
    /*     $code = <<<'CODE' */
    /*           item 1 */
    /*         Not an item */
    /*         CODE; */

    /*     $buffer = new Buffer(explode("\n", $code), UnindentStrategy::FIRST); */
    /*     self::assertSame(<<<'CODE' */
    /*     if (true) { */
    /*         echo 'Hello world'; */
    /*     } */
    /*     CODE, $buffer->getLinesString()); */
    /* } */
}
