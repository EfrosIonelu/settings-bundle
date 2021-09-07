<?php

declare(strict_types=1);

namespace Lwc\SettingsBundle\Menu;

use Knp\Menu\Util\MenuManipulator;
use Lwc\SettingsBundle\Settings\RegistryInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    /**
     * @var MenuManipulator
     */
    private MenuManipulator $manipulator;

    /**
     * @var RegistryInterface
     */
    private RegistryInterface $settingsRegistry;

    /**
     * AdminMenuListener constructor.
     *
     * @param MenuManipulator $manipulator
     * @param RegistryInterface $settingsRegistry
     */
    public function __construct(MenuManipulator $manipulator, RegistryInterface $settingsRegistry)
    {
        $this->manipulator = $manipulator;
        $this->settingsRegistry = $settingsRegistry;
    }

    /**
     * @param MenuBuilderEvent $event
     */
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        if ($this->settingsRegistry->count()) {
            $menu = $event->getMenu();
            if (null !== ($configurationMenu = $menu->getChild('configuration'))) {
                $settings = $configurationMenu->addChild('lwc_settings', ['route' => 'lwc_sylius_settings_admin_index']);
                $settings
                    ->setLabel('lwc.settings.menu.admin.configuration.settings')
                    ->setLabelAttribute('icon', 'cog')
                ;
                $this->manipulator->moveChildToPosition($configurationMenu, $settings, 1);
            }
        }
    }
}
