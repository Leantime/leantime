<?php

namespace Unit\app\Domain\Users\Hxcontrollers;

use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\UI\Template;
use Leantime\Core\UI\Theme;
use Leantime\Domain\Users\Hxcontrollers\Background;
use Leantime\Domain\Users\Services\Backgrounds;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * Background changes are saved preferences, so they only act on POST: the Frontcontroller
 * dispatches GET to custom actions too and the request-origin check skips GET, so a cross-site
 * link could otherwise reset the user's background.
 */
class BackgroundTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * @param  \ArrayObject  $changes  Records every change that reached the service, as [method, argument].
     */
    private function controllerFor(string $method, array $parameters, \ArrayObject $changes): Background
    {
        $request = IncomingRequest::create('/hx/users/background/action', $method, $parameters);

        $tpl = $this->make(Template::class, [
            'emptyResponse' => fn ($code = 200) => new Response('', $code),
            'assign' => fn () => null,
            'setNotification' => fn () => null,
        ]);

        $backgrounds = $this->make(Backgrounds::class, [
            'setBackgroundType' => function (string $type) use ($changes): bool {
                $changes->append(['setBackgroundType', $type]);

                return true;
            },
            'useUnsplashPhoto' => function (string $photoId) use ($changes): bool {
                $changes->append(['useUnsplashPhoto', $photoId]);

                return true;
            },
            'useUploadedImage' => function (array $files) use ($changes): bool {
                $changes->append(['useUploadedImage', $files]);

                return true;
            },
            'getCurrentBackground' => fn () => ['type' => 'gradient', 'image' => ''],
            'isUnsplashEnabled' => fn () => false,
        ]);

        $theme = $this->make(Theme::class, [
            'getPrimaryColor' => fn () => '#006d9f',
            'getSecondaryColor' => fn () => '#00a886',
        ]);

        $controller = (new \ReflectionClass(Background::class))->newInstanceWithoutConstructor();
        foreach (['incomingRequest' => $request, 'tpl' => $tpl] as $property => $value) {
            $reflection = new \ReflectionProperty(Background::class, $property);
            $reflection->setAccessible(true);
            $reflection->setValue($controller, $value);
        }
        $controller->init($backgrounds, $theme);

        return $controller;
    }

    /**
     * @dataProvider changeActionProvider
     */
    public function test_get_is_rejected_without_changing_the_background(string $action, array $parameters): void
    {
        $changes = new \ArrayObject;

        $response = $this->controllerFor('GET', $parameters, $changes)->{$action}();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(405, $response->getStatusCode());
        $this->assertCount(0, $changes);
    }

    public static function changeActionProvider(): array
    {
        return [
            'switch to gradient' => ['setType', ['type' => 'gradient']],
            'use an Unsplash photo' => ['useUnsplashPhoto', ['photoId' => 'abc123']],
            'upload an image' => ['upload', []],
        ];
    }

    public function test_post_switches_the_type_from_the_request_body(): void
    {
        $changes = new \ArrayObject;

        $fragment = $this->controllerFor('POST', ['type' => 'gradient'], $changes)->setType();

        $this->assertSame('current-background', $fragment);
        $this->assertSame([['setBackgroundType', 'gradient']], $changes->getArrayCopy());
    }
}
