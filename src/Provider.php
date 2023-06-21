<?php

declare(strict_types=1);

namespace Retrofit\Drupal;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Template\Loader\FilesystemLoader;
use Retrofit\Drupal\Language\GlobalLanguageContentSetter;
use Retrofit\Drupal\Menu\MenuLinkManager;
use Retrofit\Drupal\ParamConverter\PageArgumentsConverter;
use Retrofit\Drupal\Routing\HookMenuRegistry;
use Retrofit\Drupal\Routing\HookMenuRoutes;
use Retrofit\Drupal\Template\RetrofitExtension;
use Retrofit\Drupal\Theme\Registry;
use Retrofit\Drupal\User\GlobalUserSetter;
use Retrofit\Drupal\User\HookPermissions;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Reference;

class Provider extends ServiceProviderBase
{
    public function register(ContainerBuilder $container)
    {
        $namespaces = $container->getParameter('container.namespaces');
        $namespaces['Retrofit\Drupal'] = __DIR__;
        $container->setParameter('container.namespaces', $namespaces);

        $container
          ->register(HookMenuRegistry::class)
          ->addArgument(new Reference('module_handler'))
          ->addArgument(new Reference('cache.data'));

        $container
          ->register(HookMenuRoutes::class)
          ->setAutowired(true)
          ->addTag('event_subscriber');

        $container
          ->register(GlobalUserSetter::class)
          ->addTag('event_subscriber');

        $container
          ->register(GlobalLanguageContentSetter::class)
          ->addArgument(new Reference('language_manager'))
          ->addTag('event_subscriber');

        $container
          ->register(PageArgumentsConverter::class)
          ->addTag('paramconverter');

        $container->setDefinition(
            MenuLinkManager::class,
            (new ChildDefinition('plugin.manager.menu.link'))
            ->setDecoratedService('plugin.manager.menu.link')
        );

        $container->setDefinition(
            Registry::class,
            (new ChildDefinition('theme.registry'))
            ->setDecoratedService('theme.registry')
        );

        $container->setDefinition(
            FilesystemLoader::class,
            (new ChildDefinition('twig.loader.filesystem'))
            ->setDecoratedService('twig.loader.filesystem')
            ->addMethodCall('addPath', [__DIR__ . '/../templates', 'retrofit'])
        );

        $container->register(RetrofitExtension::class)
            ->addArgument(new Reference('theme.registry'))
            ->addTag('twig.extension');

        if ($container->has('user.permissions')) {
            $container
              ->register(HookPermissions::class)
              ->setDecoratedService('user.permissions')
              ->addArgument(new Reference(HookPermissions::class . '.inner'))
              ->addArgument(new Reference('module_handler'));
        }
    }

    public function alter(ContainerBuilder $container)
    {
    }
}
