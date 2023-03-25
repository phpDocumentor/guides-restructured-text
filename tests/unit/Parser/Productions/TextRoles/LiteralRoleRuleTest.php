<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

final class LiteralRoleRuleTest extends StartEndRegexRoleRuleTest
{
    private LiteralRoleRule $rule;

    protected function setUp(): void
    {
        $this->rule = new LiteralRoleRule();
    }

    public function getRule(): StartEndRegexRoleRule
    {
        return $this->rule;
    }

    /**
     * @return array<int, array<int, array<int, string> | bool>>
     */
    public function ruleAppliesProvider(): array
    {
        return [
            [
                ['``text'],
                true,
            ],
            [
                ['`text'],
                false,
            ],
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function expectedLiteralContentProvider() : array
    {
        return [
            [
                '``literal``',
                'literal',
            ],
            [
                '``literal with spaces``',
                'literal with spaces'
            ],
            [
                '``literal with `single backticks` inside``',
                'literal with `single backticks` inside'
            ]
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function notEndingProvider(): array
    {
        return [
            [
                '``literal not ending',
                '``literal',
            ],
        ];
    }
}
