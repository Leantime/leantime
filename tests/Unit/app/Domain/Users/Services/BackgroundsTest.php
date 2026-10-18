<?php

namespace Unit\app\Domain\Users\Services;

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\Request;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\UI\Theme;
use Leantime\Domain\Files\Services\Files;
use Leantime\Domain\Users\Services\Backgrounds;
use Unit\TestCase;

/**
 * The background service: Unsplash only works with a configured access key, photos are resolved
 * on the server by id, and the background type is validated.
 */
class BackgroundsTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private HttpClient $http;

    /** @var string[] Background image URLs the service stored through the theme. */
    private array $storedImages = [];

    /** @var string[] Background types the service stored through the theme. */
    private array $storedTypes = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->http = new HttpClient;
        $this->http->preventStrayRequests();
        $this->storedImages = [];
        $this->storedTypes = [];
    }

    private function makeService(string $accessKey = 'test-key'): Backgrounds
    {
        $config = $this->make(Environment::class);
        $config->unsplashAccessKey = $accessKey;

        $theme = $this->make(Theme::class, [
            'setBackgroundImage' => function (string $url) {
                $this->storedImages[] = $url;
            },
            'setBackgroundType' => function (string $type) {
                $this->storedTypes[] = $type;
            },
        ]);

        return new Backgrounds($theme, $config, $this->make(Files::class), $this->http);
    }

    private function unsplashPhoto(string $rawUrl): array
    {
        return [
            'id' => 'abc123',
            'alt_description' => 'snowy mountains',
            'urls' => ['raw' => $rawUrl, 'small' => 'https://images.unsplash.com/photo-1?w=400'],
            'user' => ['name' => 'Jane Doe', 'links' => ['html' => 'https://unsplash.com/@jane']],
            'links' => ['download_location' => 'https://api.unsplash.com/photos/abc123/download?ixid=xyz'],
        ];
    }

    public function test_unsplash_is_disabled_without_access_key(): void
    {
        $service = $this->makeService(accessKey: '');

        $this->assertFalse($service->isUnsplashEnabled());
        $this->assertSame(['photos' => [], 'nextPage' => 1], $service->searchUnsplash('mountains'));
        $this->assertFalse($service->useUnsplashPhoto('abc123'));
        $this->assertCount(0, $this->http->recorded());
    }

    public function test_search_returns_photos_with_attribution_and_sends_access_key(): void
    {
        $this->http->fake([
            'api.unsplash.com/search/photos*' => $this->http->response([
                'total_pages' => 3,
                'results' => [$this->unsplashPhoto('https://images.unsplash.com/photo-1?ixid=1')],
            ]),
        ]);

        $searchResult = $this->makeService()->searchUnsplash('mountains');

        $this->assertSame(2, $searchResult['nextPage']);
        $this->assertSame([[
            'id' => 'abc123',
            'thumbnailUrl' => 'https://images.unsplash.com/photo-1?w=400',
            'description' => 'snowy mountains',
            'photographerName' => 'Jane Doe',
            'photographerUrl' => 'https://unsplash.com/@jane?utm_source=leantime&utm_medium=referral',
        ]], $searchResult['photos']);

        $this->http->assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Client-ID test-key')
            && $request['query'] === 'mountains');
    }

    public function test_search_returns_nothing_when_unsplash_fails(): void
    {
        $this->http->fake(['api.unsplash.com/*' => $this->http->response(['errors' => ['Rate Limit Exceeded']], 403)]);

        $this->assertSame(['photos' => [], 'nextPage' => 1], $this->makeService()->searchUnsplash('mountains'));
    }

    public function test_the_next_page_after_the_last_one_starts_over(): void
    {
        $this->http->fake([
            'api.unsplash.com/search/photos*' => $this->http->response(['total_pages' => 3, 'results' => []]),
        ]);

        $this->assertSame(1, $this->makeService()->searchUnsplash('mountains', 3)['nextPage']);
        $this->http->assertSent(fn (Request $request) => $request['page'] === 3);
    }

    public function test_using_a_photo_stores_its_resized_url_and_reports_the_download(): void
    {
        $this->http->fake([
            'api.unsplash.com/photos/abc123/download*' => $this->http->response(['url' => 'https://example.test']),
            'api.unsplash.com/photos/abc123' => $this->http->response($this->unsplashPhoto('https://images.unsplash.com/photo-1?ixid=1')),
        ]);

        $this->assertTrue($this->makeService()->useUnsplashPhoto('abc123'));

        $this->assertSame(['https://images.unsplash.com/photo-1?ixid=1&w=2400&q=80&fm=jpg&fit=max'], $this->storedImages);
        $this->http->assertSent(fn (Request $request) => str_starts_with($request->url(), 'https://api.unsplash.com/photos/abc123/download'));
    }

    public function test_photo_outside_unsplash_image_host_is_refused(): void
    {
        $this->http->fake([
            'api.unsplash.com/photos/abc123' => $this->http->response($this->unsplashPhoto('https://evil.test/x.jpg')),
        ]);

        $this->assertFalse($this->makeService()->useUnsplashPhoto('abc123'));
        $this->assertSame([], $this->storedImages);
    }

    public function test_photo_id_with_path_characters_is_refused_without_a_request(): void
    {
        $this->assertFalse($this->makeService()->useUnsplashPhoto('../me'));
        $this->assertCount(0, $this->http->recorded());
    }

    public function test_unknown_background_type_is_refused(): void
    {
        $service = $this->makeService();

        $service->setBackgroundType('gradient');
        $this->assertSame(['gradient'], $this->storedTypes);

        $this->expectException(ValidationException::class);
        $service->setBackgroundType('video');
    }

    public function test_upload_of_an_image_format_browsers_cannot_show_is_refused(): void
    {
        // A TIFF named .jpg: getimagesize() detects it, but it is not a JPEG/PNG/GIF/WebP.
        $tiff = tempnam(sys_get_temp_dir(), 'bg');
        file_put_contents($tiff, "II*\x00\x08\x00\x00\x00\x03\x00\x00\x01\x03\x00\x01\x00\x00\x00\x01\x00\x00\x00\x01\x01\x03\x00\x01\x00\x00\x00\x01\x00\x00\x00\x15\x01\x03\x00\x01\x00\x00\x00\x01\x00\x00\x00\x00\x00\x00\x00");
        $this->assertSame(IMAGETYPE_TIFF_II, getimagesize($tiff)[2], 'The fixture must be a TIFF getimagesize() recognizes');

        session(['userdata.id' => 1]);

        $stored = $this->makeService()->useUploadedImage(['file' => [
            'name' => 'background.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $tiff,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tiff),
        ]]);

        unlink($tiff);

        $this->assertFalse($stored);
        $this->assertSame([], $this->storedImages);
    }

    public function test_upload_that_is_not_an_image_is_refused(): void
    {
        $fakeImage = tempnam(sys_get_temp_dir(), 'bg');
        file_put_contents($fakeImage, '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        session(['userdata.id' => 1]);

        $stored = $this->makeService()->useUploadedImage(['file' => [
            'name' => 'background.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $fakeImage,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($fakeImage),
        ]]);

        unlink($fakeImage);

        $this->assertFalse($stored);
        $this->assertSame([], $this->storedImages);
    }
}
