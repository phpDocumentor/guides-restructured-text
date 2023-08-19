<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use Doctrine\Common\Lexer\AbstractLexer;
use phpDocumentor\Guides\ReferenceResolvers\ExternalReferenceResolver;
use ReflectionClass;

use function array_column;
use function array_flip;
use function parse_url;
use function preg_match;

use const PHP_URL_SCHEME;

/** @extends AbstractLexer<int, string> */
final class InlineLexer extends AbstractLexer
{
    public const WORD = 1;
    public const UNDERSCORE = 2;
    public const ANONYMOUS_END = 3;
    public const PHRASE_ANONYMOUS_END = 4;
    public const LITERAL = 5;
    public const BACKTICK = 6;
    public const NAMED_REFERENCE_END = 7;
    public const INTERNAL_REFERENCE_START = 8;
    public const EMBEDED_URL_START = 9;
    public const EMBEDED_URL_END = 10;
    public const NAMED_REFERENCE = 11;
    public const ANONYMOUSE_REFERENCE = 12;
    public const COLON = 13;
    public const OCTOTHORPE = 14;
    public const WHITESPACE = 15;
    public const ANNOTATION_START = 16;
    public const ANNOTATION_END = 17;
    public const DOUBLE_BACKTICK = 18;
    public const HYPERLINK = 19;
    public const EMAIL = 20;
    public const EMPHASIS_DELIMITER = 21;
    public const STRONG_DELIMITER = 22;
    public const NBSP = 23;
    public const VARIABLE_DELIMITER = 24;
    public const ESCAPED_SIGN = 25;

    /**
     * Map between string position and position in token list.
     *
     * @link https://github.com/doctrine/lexer/issues/53
     *
     * @var array<int, int>
     */
    private array $tokenPositions = [];

    /** @return string[] */
    protected function getCatchablePatterns(): array
    {
        return [
            '\\\\``', // must be a separate case, as the next pattern would split in "\`" + "`", causing it to become a intepreted text
            '\\\\[\s\S]', // Escaping hell... needs escaped slash in regex, but also in php.
            '\\S+@\\S+\\.\\S+',
            '[a-z0-9-]+_{2}', //Inline href.
            '[a-z0-9-]+_{1}(?=[\s\.+]|$)', //Inline href.
            '``.+?``(?!`)',
            '`__',
            '`_',
            '`~',
            '<',
            '>',
            '_`',
            '`',
            '_{2}',
            ':',
            '|',
            '\\*\\*',
            '\\*',
            '\b(?<!:)[a-z0-9\\.\-+]{2,}:[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*[-a-zA-Z0-9()@%_\\+~#&\\/=]', // standalone hyperlinks
        ];
    }

    /** @param int $position */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function resetPosition($position = 0): void
    {
        parent::resetPosition($this->tokenPositions[$position]);
    }

    /** @param string $input */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    protected function scan($input): void
    {
        parent::scan($input);

        $class = new ReflectionClass(AbstractLexer::class);
        $property = $class->getProperty('tokens');
        $property->setAccessible(true);
        /** @var array<int, string> $tokens */
        $tokens = $property->getValue($this);

        $this->tokenPositions = array_flip(array_column($tokens, 'position'));
    }

    /** @return string[] */
    protected function getNonCatchablePatterns(): array
    {
        return [];
    }

    /** @inheritDoc */
    protected function getType(string &$value)
    {
        if (preg_match('/^\\\\[\s\S]/i', $value)) {
            return self::ESCAPED_SIGN;
        }

        if (preg_match('/``.+``(?!`)/i', $value)) {
            return self::LITERAL;
        }

        if (preg_match('/' . ExternalReferenceResolver::SUPPORTED_SCHEMAS . ':[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*[-a-zA-Z0-9()@%_\\+~#&\\/=]/', $value) && parse_url($value, PHP_URL_SCHEME) !== null) {
            return self::HYPERLINK;
        }

        if (preg_match('/\\S+@\\S+\\.\\S+/i', $value)) {
            return self::EMAIL;
        }

        if (preg_match('/[a-z0-9-]+_{2}/i', $value)) {
            return self::ANONYMOUSE_REFERENCE;
        }

        if (preg_match('/[a-z0-9-]+_{1}(?=\s|$)/i', $value)) {
            return self::NAMED_REFERENCE;
        }

        if (preg_match('/\s/i', $value)) {
            return self::WHITESPACE;
        }

        switch ($value) {
            case '`':
                return self::BACKTICK;

            case '**':
                return self::STRONG_DELIMITER;

            case '*':
                return self::EMPHASIS_DELIMITER;

            case '|':
                return self::VARIABLE_DELIMITER;

            case '<':
                return self::EMBEDED_URL_START;

            case '>':
                return self::EMBEDED_URL_END;

            case '_':
                return self::UNDERSCORE;

            case '`_':
                return self::NAMED_REFERENCE_END;

            case '_`':
                return self::INTERNAL_REFERENCE_START;

            case '__':
                return self::ANONYMOUS_END;

            case '`__':
                return self::PHRASE_ANONYMOUS_END;

            case ':':
                return self::COLON;

            case '#':
                return self::OCTOTHORPE;

            case '[':
                return self::ANNOTATION_START;

            case ']':
                return self::ANNOTATION_END;

            case '~':
                return self::NBSP;

            default:
                return self::WORD;
        }
    }
}
