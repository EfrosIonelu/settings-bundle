<?php

declare(strict_types=1);

namespace Lwc\SettingsBundle\DependencyInjection;

use Lwc\SettingsBundle\Settings\Metadata;
use Lwc\SettingsBundle\Settings\Settings;
use Lwc\SettingsBundle\Settings\SettingsInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

final class InstantiateSettingsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        // Get required parameters and definitions in order to populate the DI
        try {
            $plugins = $container->getParameter('lwc.settings.config.plugins');
            $registry = $container->findDefinition('lwc.settings.registry');
            $metadataRegistry = $container->findDefinition('lwc.settings.metadata_registry');
        } catch (InvalidArgumentException $exception) {
            return;
        }

        foreach ($plugins as $alias => $configuration) {
            $metadataRegistry->addMethodCall('addFromAliasAndConfiguration', [$alias, $configuration]);
            $metadata = Metadata::fromAliasAndConfiguration($alias, $configuration);

            $id = $metadata->getServiceId('settings');

            $class = $metadata->getClass('settings');
            $this->validateSettingsResource($class);

            $container->setDefinition($id, new Definition($class, [
                $this->getMetadataDefinition($metadata),
                $container->findDefinition('lwc_settings.repository.setting'),
            ]));

            $aliases = [
                SettingsInterface::class . ' $' . $metadata->getName() . 'Settings' => $id,
                Settings::class . ' $' . $metadata->getName() . 'Settings' => $id,
            ];
            if (Settings::class !== $class) {
                $aliases[$class . ' $' . $metadata->getName() . 'Settings'] = $id;
            }
            $container->addAliases($aliases);

            $registry->addMethodCall('addSettingsInstance', [new Reference($id)]);
        }
    }

    private function validateSettingsResource(string $class): void
    {
        if (!\in_array(SettingsInterface::class, class_implements($class), true)) {
            throw new InvalidArgumentException(sprintf('Class "%s" must implement "%s" to be registered as a Settings resource.', $class, SettingsInterface::class));
        }
    }

    /**
     * @param Metadata $metadata
     *
     * @return Definition
     */
    private function getMetadataDefinition(Metadata $metadata): Definition
    {
        $metadataDefinition = new Definition(Metadata::class);
        $metadataDefinition
            ->setFactory([new Reference('lwc.settings.metadata_registry'), 'get'])
            ->setArguments([$metadata->getAlias()])
        ;

        return $metadataDefinition;
    }
}
