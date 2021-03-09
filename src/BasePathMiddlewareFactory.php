<?php

namespace LosMiddleware\BasePath;

use LosMiddleware\BasePath\BasePathMiddleware;
use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerInterface;

final class BasePathMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): BasePathMiddleware
    {
        $config = $container->get('config');
        $path   = $config[BasePathMiddleware::BASE_PATH] ?? '';

        $urlHelper = null;

        if ($container->has(UrlHelper::class)) {
            $urlHelper = $container->get(UrlHelper::class);
        }

        return new BasePathMiddleware($path, $urlHelper);
    }
}
