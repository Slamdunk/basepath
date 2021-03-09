<?php
namespace LosMiddleware\BasePathTest;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\View\Helper\BasePath;
use LosMiddleware\BasePath\BasePathMiddleware;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouterInterface;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class BasePathMiddlewareTest extends TestCase
{
    private const BASEPATH = '/basepath';

    /**
     * @return string[][]
     */
    public function routeProvider(): array
    {
        return [
            ['', ''],
            ['/', '/'],
            [self::BASEPATH, '/'],
            [self::BASEPATH . '/', '/'],
            [self::BASEPATH . '/test1', '/test1'],
        ];
    }

    /**
     * @dataProvider routeProvider
     */
    public function testCanHandleRoutes(string $route, string $expected): void
    {
        $urlHelper = new UrlHelper($this->createMock(RouterInterface::class));
        $basePathViewHelper = new BasePath();

        $basePath = new BasePathMiddleware(
            self::BASEPATH,
            $urlHelper,
            $basePathViewHelper
        );

        $request = new ServerRequest([], [], $route);
        $response = $basePath->process($request, new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new JsonResponse(['path' => $request->getUri()->getPath()]);
            }
        });
        $path = json_decode((string) $response->getBody(), true)['path'];
        self::assertSame($expected, $path);

        if (0 !== strpos($route, self::BASEPATH)) {
            return;
        }

        self::assertSame(self::BASEPATH, $urlHelper->getBasePath());
        $unique = uniqid('/foo');
        self::assertSame(self::BASEPATH . $unique, $basePathViewHelper($unique));
    }
}
