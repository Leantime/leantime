<?php

namespace Unit\app\Domain\Users\Services;

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\Request;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\UI\Theme;
use Leantime\Domain\Files\Services\Files;
use Leantime\Domain\Setting\Services\Setting;
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

    /** @var array<string, mixed> In-memory user settings. */
    private array $settings = [];

    /** @var int[] File ids the service deleted. */
    private array $deletedFileIds = [];

    /** @var string[] File names the service handed to Files::upload(). */
    private array $uploadedNames = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->http = new HttpClient;
        $this->http->preventStrayRequests();
        $this->storedImages = [];
        $this->storedTypes = [];
        $this->settings = [];
        $this->deletedFileIds = [];
        $this->uploadedNames = [];
        session(['userdata.id' => 1]);
    }

    private function makeService(string $accessKey = 'test-key', ?int $uploadedFileId = null): Backgrounds
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

        $files = $this->make(Files::class, [
            'upload' => function ($files) use ($uploadedFileId) {
                $this->uploadedNames[] = $files['file']['name'];

                return ['fileId' => $uploadedFileId, 'encName' => 'enc'.$uploadedFileId, 'extension' => 'png', 'realName' => 'bg.png'];
            },
            'deleteFile' => function ($fileId) {
                $this->deletedFileIds[] = (int) $fileId;

                return true;
            },
        ]);

        $settings = $this->make(Setting::class, [
            'getSetting' => fn ($key, $default = false) => $this->settings[$key] ?? $default,
            'saveSetting' => function ($key, $value) {
                $this->settings[$key] = $value;

                return true;
            },
            'deleteSetting' => function ($key) {
                unset($this->settings[$key]);
            },
        ]);

        return new Backgrounds($theme, $config, $files, $this->http, $settings, app('cache'));
    }

    /**
     * A real PNG upload, as one $_FILES entry.
     */
    private function pngUpload(): array
    {
        $png = tempnam(sys_get_temp_dir(), 'bg');
        $image = imagecreatetruecolor(4, 4);
        imagepng($image, $png);

        return ['name' => 'bg.png', 'type' => 'image/png', 'tmp_name' => $png, 'error' => UPLOAD_ERR_OK, 'size' => filesize($png)];
    }

    public function test_a_new_upload_deletes_the_previously_uploaded_background(): void
    {
        $this->assertTrue($this->makeService(uploadedFileId: 10)->useUploadedImage(['file' => $this->pngUpload()]));
        $this->assertSame([], $this->deletedFileIds, 'Nothing to delete on the first upload');

        $this->assertTrue($this->makeService(uploadedFileId: 11)->useUploadedImage(['file' => $this->pngUpload()]));

        $this->assertSame([10], $this->deletedFileIds);
        $this->assertSame(11, $this->settings['usersettings.1.backgroundFileId']);

        // Unique names: with file renaming turned off, two uploads of "bg.png" would otherwise share
        // one stored file, and deleting the replaced one would delete the new background.
        $this->assertCount(2, array_unique($this->uploadedNames));
        foreach ($this->uploadedNames as $uploadedName) {
            $this->assertMatchesRegularExpression('/^background[0-9a-f]{16}\.png$/', $uploadedName);
        }
    }

    public function test_switching_to_the_gradient_deletes_the_uploaded_background(): void
    {
        $this->settings['usersettings.1.backgroundFileId'] = 10;

        $this->makeService()->setBackgroundType('gradient');

        $this->assertSame([10], $this->deletedFileIds);
        $this->assertArrayNotHasKey('usersettings.1.backgroundFileId', $this->settings);
    }

    public function test_a_change_waits_for_another_change_and_gives_up_without_saving(): void
    {
        // Two tabs: while one change holds the user's lock, another must not save or clean up,
        // and its freshly uploaded file is removed again.
        $this->settings['usersettings.1.backgroundFileId'] = 10;
        $otherChange = app('cache')->lock('users.background.1', 30);
        $this->assertTrue($otherChange->get());

        $waitSeconds = new \ReflectionClassConstant(Backgrounds::class, 'LOCK_WAIT_SECONDS');
        $this->assertSame(10, $waitSeconds->getValue(), 'The test relies on the wait being bounded');

        try {
            $started = microtime(true);
            $saved = $this->makeService(uploadedFileId: 11)->useUploadedImage(['file' => $this->pngUpload()]);
        } finally {
            $otherChange->release();
        }

        $this->assertFalse($saved);
        $this->assertGreaterThanOrEqual(9, microtime(true) - $started, 'It waited for the other change first');
        $this->assertSame([], $this->storedImages, 'Nothing is saved');
        $this->assertSame(10, $this->settings['usersettings.1.backgroundFileId'], 'The tracked upload is unchanged');
        $this->assertSame([11], $this->deletedFileIds, 'Only the new, unused upload is removed');
    }

    public function test_an_unsplash_photo_deletes_the_uploaded_background(): void
    {
        $this->settings['usersettings.1.backgroundFileId'] = 10;
        $this->http->fake([
            'api.unsplash.com/photos/abc123/download*' => $this->http->response(['url' => 'https://example.test']),
            'api.unsplash.com/photos/abc123' => $this->http->response($this->unsplashPhoto('https://images.unsplash.com/photo-1?ixid=1')),
        ]);

        $this->assertTrue($this->makeService()->useUnsplashPhoto('abc123'));

        $this->assertSame([10], $this->deletedFileIds);
        $this->assertArrayNotHasKey('usersettings.1.backgroundFileId', $this->settings);
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

        $this->settings['usersettings.1.backgroundFileId'] = 10;

        $this->assertFalse($this->makeService()->useUnsplashPhoto('abc123'));
        $this->assertSame([], $this->storedImages);
        $this->assertSame([], $this->deletedFileIds, 'A refused photo keeps the uploaded background');
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
