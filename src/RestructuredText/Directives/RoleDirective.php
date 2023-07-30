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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\TextRoles\BaseTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\GenericTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleFactory;
use Psr\Log\LoggerInterface;

use function is_string;
use function preg_match;
use function trim;

/**
 * The "role" directive dynamically creates a custom interpreted text role and registers it with the parser.
 *
 * https://docutils.sourceforge.io/docs/ref/rst/directives.html#role
 */
class RoleDirective extends BaseDirective
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TextRoleFactory $textRoleFactory,
    ) {
    }

    public function getName(): string
    {
        return 'role';
    }

    public function process(
        DocumentParserContext $documentParserContext,
        Directive $directive,
    ): Node|null {
        $name = $directive->getData();
        $role = 'span';
        if (preg_match('/^([A-Za-z-]*)\(([A-Za-z-]*)\)$/', trim($name), $match) > 0) {
            $name = $match[1];
            $role = $match[2];
        }

        $baseRole = $this->textRoleFactory->getTextRole($role);
        if (!$baseRole instanceof BaseTextRole) {
            $this->logger->error('Text role "' . $role . '", class ' . $baseRole::class . ' cannot be extended. ');

            return null;
        }

        $customRole = $baseRole->withName($name);
        if (is_string($directive->getOption('class')->getValue())) {
            $customRole->setClass($directive->getOption('class')->getValue());
        } else {
            $customRole->setClass($name);
        }

        if ($customRole instanceof GenericTextRole) {
            $customRole->setBaseRole($role);
        }

        $this->textRoleFactory->replaceTextRole($customRole);

        return null;
    }
}
