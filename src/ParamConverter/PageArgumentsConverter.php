<?php

declare(strict_types=1);

namespace Retrofit\Drupal\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

final class PageArgumentsConverter implements ParamConverterInterface
{
    public function convert($value, $definition, $name, array $defaults)
    {
        if (str_starts_with($name, 'arg')) {
            return $value;
        }
        if (function_exists($name . '_load') && is_callable($name . '_load')) {
            $value = ($name . '_load')($value);
        }
        return $value;
    }

    public function applies($definition, $name, Route $route)
    {
        return $route->hasDefault('_menu_callback');
    }
}
