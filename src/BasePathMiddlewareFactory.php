<?php

namespace LosMiddleware\BasePath;

use Laminas\View\Helper\BasePath;
use Laminas\View\HelperPluginManager;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

use function assert;

final class BasePathMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): BasePathMiddleware
    {
        $config = $container->get('config');
        $path   = $config[BasePathMiddleware::BASE_PATH] ?? '';

        $urlHelper = null;
        if ($container->has(UrlHelper::class)) {
            $urlHelper = $container->get(UrlHelper::class);
            assert($urlHelper instanceof UrlHelper);
        }
        $basePathViewHelper = null;
        if ($container->has(TemplateRendererInterface::class)) {
            $renderer = $container->get(TemplateRendererInterface::class);
            assert($renderer instanceof TemplateRendererInterface);
            $viewHelperPluginManager = $container->get(HelperPluginManager::class);
            assert($viewHelperPluginManager instanceof HelperPluginManager);
            $basePathViewHelper = $viewHelperPluginManager->get(BasePath::class);
            assert($basePathViewHelper instanceof BasePath);
        }

        return new BasePathMiddleware($path, $urlHelper, $basePathViewHelper);
    }
}
