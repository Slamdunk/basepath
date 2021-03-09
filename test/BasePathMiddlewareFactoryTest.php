<?php

namespace LosMiddleware\BasePathTest;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Renderer\PhpRenderer;
use LosMiddleware\BasePath\BasePathMiddleware;
use LosMiddleware\BasePath\BasePathMiddlewareFactory;
use Mezzio\Helper\UrlHelper;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class BasePathMiddlewareFactoryTest extends TestCase
{
    public function testFullfillDependencies(): void
    {
        $basePath = uniqid('/foo_');
        $sm = new ServiceManager();
        $sm->configure([
            'services' => [
                'config' => [
                    BasePathMiddleware::BASE_PATH => $basePath,
                    'templates' => [
                        'extension' => 'phtml',
                        'paths' => [
                            'app' => [__DIR__],
                        ],
                    ],
                ],
            ],
        ]);

        $sm->configure((new \Mezzio\Helper\ConfigProvider())->getDependencies());
        $sm->configure((new \Mezzio\LaminasView\ConfigProvider())->getDependencies());
        $sm->configure((new \Mezzio\Router\ConfigProvider())->getDependencies());
        $sm->configure((new \Mezzio\Router\FastRouteRouter\ConfigProvider())->getDependencies());

        $factory = new BasePathMiddlewareFactory();

        $basePathMiddleware = $factory($sm);

        $request = new ServerRequest([], [], $basePath . uniqid('/bar_'));
        $requestHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new EmptyResponse();
            }
        };
        $basePathMiddleware->process($request, $requestHandler);

        $urlHelper = $sm->get(UrlHelper::class);
        self::assertInstanceOf(UrlHelper::class, $urlHelper);
        self::assertSame($basePath, $urlHelper->getBasePath());

        $viewRenderer = $sm->get(TemplateRendererInterface::class);
        self::assertInstanceOf(TemplateRendererInterface::class, $viewRenderer);
        self::assertSame($basePath, $viewRenderer->render('app::template', ['layout' => false]));
    }
}
