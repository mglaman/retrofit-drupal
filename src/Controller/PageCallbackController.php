<?php

declare(strict_types=1);

namespace Retrofit\Drupal\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PageCallbackController implements ContainerInjectionInterface
{
    public function __construct(
        private readonly ModuleHandlerInterface $moduleHandler
    ) {
    }

    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('module_handler')
        );
    }

    public function getTitle(RouteMatchInterface $routeMatch): string
    {
        $route = $routeMatch->getRouteObject();
        assert($route !== null);
        $callback = $route->getDefault('_custom_title_callback');
        if (!is_callable($callback)) {
            return '';
        }
        $arguments = $route->getDefault('_custom_title_arguments');
        return call_user_func_array($callback, $arguments);
    }

    public function getPage(RouteMatchInterface $routeMatch, Request $request): array
    {
        $route = $routeMatch->getRouteObject();
        assert($route !== null);
        if ($route->hasOption('file')) {
            $modulePath = $this->moduleHandler->getModule($route->getOption('module'))->getPath();
            $includePath = $modulePath . '/' . $route->getOption('file');
            if (file_exists($includePath)) {
                require_once $includePath;
            }
        }
        $callback = $route->getDefault('_menu_callback');
        if (!is_callable($callback)) {
            throw new NotFoundHttpException();
        }
        $arguments = $routeMatch->getParameters()->all();
        $result = call_user_func_array($callback, array_values($arguments));
        return is_string($result) ? [
          '#markup' => Markup::create($result),
        ] : $result;
    }
}
