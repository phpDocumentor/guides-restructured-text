<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\DependencyInjection;

use phpDocumentor\Guides\RestructuredText\DependencyInjection\Compiler\TextRolePass;
use phpDocumentor\Guides\RestructuredText\Nodes\ConfvalNode;
use phpDocumentor\Guides\RestructuredText\Nodes\OptionNode;
use phpDocumentor\Guides\RestructuredText\Nodes\VersionChangeNode;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function dirname;
use function phpDocumentor\Guides\DependencyInjection\template;

class ReStructuredTextExtension extends Extension implements PrependExtensionInterface, CompilerPassInterface
{
    /** @param mixed[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 3) . '/resources/config'),
        );

        $loader->load('guides-restructured-text.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig(
            'guides',
            [
                'base_template_paths' => [
                    dirname(__DIR__, 3) . '/resources/template/html',
                    dirname(__DIR__, 3) . '/resources/template/latex',
                ],
                'templates' => [
                    template(ConfvalNode::class, 'body/directive/confval.html.twig'),
                    template(VersionChangeNode::class, 'body/version-change.html.twig'),
                    template(ConfvalNode::class, 'body/directive/confval.tex.twig', 'tex'),
                    template(OptionNode::class, 'body/directive/option.html.twig'),

                ],
            ],
        );
    }

    public function process(ContainerBuilder $container): void
    {
        (new TextRolePass())->process($container);
    }
}
