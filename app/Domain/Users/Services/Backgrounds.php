<?php

namespace Leantime\Domain\Users\Services;

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\UI\Theme;
use Leantime\Domain\Files\Services\Files;
use Leantime\Domain\Setting\Services\Setting;

/**
 * The current user's page background: the theme's color gradient, an uploaded image or an
 * Unsplash photo.
 *
 * Unsplash is only offered when an access key is configured (LEAN_UNSPLASH_ACCESS_KEY). Photos
 * are always resolved on the server by their Unsplash id, so a caller can never store an
 * arbitrary URL as background.
 *
 * An uploaded background is a file of the user; it is deleted once another background replaces it.
 * Changes run under a per-user lock (changeBackground), so two tabs changing the background at
 * the same time can't delete the upload that ends up in use.
 */
class Backgrounds extends BaseService
{
    public const TYPE_GRADIENT = 'gradient';

    public const TYPE_IMAGE = 'image';

    private const UNSPLASH_API_URL = 'https://api.unsplash.com';

    private const UNSPLASH_IMAGE_HOST = 'images.unsplash.com';

    private const UNSPLASH_RESULTS_PER_PAGE = 12;

    /** Query used when the user has not typed one, with a random page so each load looks fresh. */
    private const UNSPLASH_DEFAULT_QUERY = 'wallpaper';

    private const UNSPLASH_DEFAULT_QUERY_PAGES = 20;

    /** Unsplash resizes raw photos on the fly; this keeps backgrounds sharp without loading the original. */
    private const UNSPLASH_BACKGROUND_SIZE = '&w=2400&q=80&fm=jpg&fit=max';

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /** Image formats every browser can show; getimagesize() also detects TIFF, BMP and others. */
    private const IMAGE_TYPES = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

    /** Seconds a change may hold the per-user lock, and seconds another change waits for it. */
    private const LOCK_SECONDS = 30;

    private const LOCK_WAIT_SECONDS = 10;

    public function __construct(
        private Theme $theme,
        private Environment $config,
        private Files $files,
        private HttpClient $http,
        private Setting $settings,
        private CacheManager $cache,
    ) {}

    /**
     * The current user's background.
     *
     * @return array{type: string, image: string}
     */
    public function getCurrentBackground(): array
    {
        return [
            'type' => $this->theme->getBackgroundType(),
            'image' => (string) $this->theme->getBackgroundImage(),
        ];
    }

    /**
     * Whether Unsplash photos can be offered (an access key is configured).
     */
    public function isUnsplashEnabled(): bool
    {
        return trim((string) $this->config->unsplashAccessKey) !== '';
    }

    /**
     * Switch the current user's background type. Switching to the gradient removes the image.
     *
     * @param  string  $type  'gradient' or 'image'
     * @return bool False when another background change kept it from being saved.
     *
     * @throws ValidationException When the type is unknown.
     */
    public function setBackgroundType(string $type): bool
    {
        if (! in_array($type, [self::TYPE_GRADIENT, self::TYPE_IMAGE], true)) {
            $message = 'Unknown background type';

            throw new ValidationException(['type' => [$message]], $message);
        }

        // Switching to "image" keeps the current image; no file changes, so no lock needed.
        if ($type === self::TYPE_IMAGE) {
            $this->theme->setBackgroundType($type);

            return true;
        }

        // The gradient removes the image, so an uploaded one is no longer needed.
        return $this->changeBackground(fn () => $this->theme->setBackgroundType($type), newFileId: null);
    }

    /**
     * Search Unsplash for landscape photos. Without a query a random page of wallpapers is returned.
     * No photos when Unsplash is not configured or the request fails. nextPage is the page that
     * shows other photos for the same query (back to 1 after the last one).
     *
     * @return array{photos: array<int, array{id: string, thumbnailUrl: string, description: string, photographerName: string, photographerUrl: string}>, nextPage: int}
     */
    public function searchUnsplash(string $query, int $page = 1): array
    {
        $noPhotos = ['photos' => [], 'nextPage' => 1];

        if (! $this->isUnsplashEnabled()) {
            return $noPhotos;
        }

        $query = trim($query);
        $page = max(1, $page);
        if ($query === '') {
            $query = self::UNSPLASH_DEFAULT_QUERY;
            $page = random_int(1, self::UNSPLASH_DEFAULT_QUERY_PAGES);
        }

        try {
            $response = $this->unsplashRequest()->get('/search/photos', [
                'query' => $query,
                'page' => $page,
                'per_page' => self::UNSPLASH_RESULTS_PER_PAGE,
                'orientation' => 'landscape',
                'content_filter' => 'high',
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Unsplash search failed: '.$exception->getMessage());

            return $noPhotos;
        }

        if (! $response->successful()) {
            Log::warning('Unsplash search failed with status '.$response->status());

            return $noPhotos;
        }

        $photos = [];
        foreach ((array) $response->json('results', []) as $photo) {
            if (! isset($photo['id'], $photo['urls']['small'])) {
                continue;
            }

            $photos[] = [
                'id' => (string) $photo['id'],
                'thumbnailUrl' => (string) $photo['urls']['small'],
                'description' => (string) ($photo['alt_description'] ?? ''),
                'photographerName' => (string) ($photo['user']['name'] ?? ''),
                'photographerUrl' => $this->withReferral((string) ($photo['user']['links']['html'] ?? 'https://unsplash.com')),
            ];
        }

        $totalPages = (int) $response->json('total_pages', 1);

        return [
            'photos' => $photos,
            'nextPage' => $page < $totalPages ? $page + 1 : 1,
        ];
    }

    /**
     * Use an Unsplash photo as the current user's background.
     *
     * Looks the photo up by id, stores its image URL and reports the download to Unsplash, as their
     * API guidelines require when a photo is used.
     *
     * @param  string  $photoId  The Unsplash photo id.
     * @return bool False when Unsplash is not configured or the photo can't be loaded.
     */
    public function useUnsplashPhoto(string $photoId): bool
    {
        if (! $this->isUnsplashEnabled() || preg_match('/^[A-Za-z0-9_-]{1,64}$/', $photoId) !== 1) {
            return false;
        }

        try {
            $response = $this->unsplashRequest()->get('/photos/'.$photoId);
        } catch (\Throwable $exception) {
            Log::warning('Unsplash photo lookup failed: '.$exception->getMessage());

            return false;
        }

        if (! $response->successful()) {
            return false;
        }

        $rawUrl = (string) $response->json('urls.raw', '');
        if (! $this->isUnsplashImageUrl($rawUrl)) {
            return false;
        }

        $saved = $this->changeBackground(
            fn () => $this->theme->setBackgroundImage($rawUrl.self::UNSPLASH_BACKGROUND_SIZE),
            newFileId: null,
        );
        if (! $saved) {
            return false;
        }

        $this->trackUnsplashDownload((string) $response->json('links.download_location', ''));

        return true;
    }

    /**
     * Use an uploaded image as the current user's background. The file is stored privately for
     * the user (module "user"), so only they can load it.
     *
     * @param  array  $files  The request's files ($_FILES); the image is expected under "file".
     * @return bool False when no valid image was uploaded or storing it failed.
     */
    public function useUploadedImage(array $files): bool
    {
        $userId = (int) session('userdata.id');
        $upload = $files['file'] ?? null;

        if ($userId <= 0 || ! is_array($upload) || ! $this->isImageUpload($upload)) {
            return false;
        }

        // A unique name: with LEAN_FILESYSTEM_RENAME_FILES=false files are stored under their own
        // name, so two uploads of "background.png" would share one file and deleting the replaced
        // one would delete the new background too.
        $extension = strtolower(pathinfo((string) $upload['name'], PATHINFO_EXTENSION));
        // Letters and digits only: files/get strips everything else from the name before the lookup.
        $upload['name'] = 'background'.bin2hex(random_bytes(8)).'.'.$extension;

        $storedFile = $this->files->upload(['file' => $upload], 'user', $userId);

        // Files::upload returns the file row on success, an error message or false otherwise.
        if (! is_array($storedFile)) {
            return false;
        }

        $fileUrl = BASE_URL.'/files/get?'.http_build_query([
            'module' => 'user',
            'encName' => $storedFile['encName'],
            'ext' => $storedFile['extension'],
            'realName' => $storedFile['realName'],
        ]);

        $saved = $this->changeBackground(
            fn () => $this->theme->setBackgroundImage($fileUrl),
            newFileId: (int) $storedFile['fileId'],
        );
        if (! $saved) {
            $this->files->deleteFile((int) $storedFile['fileId']);

            return false;
        }

        return true;
    }

    /**
     * Apply a background change and clean up the upload it replaces, under a per-user lock. Two
     * changes running at the same time (two tabs) would otherwise both see the same previous
     * upload, and one could delete the file the other just saved as background.
     *
     * @param  \Closure  $applyChange  Saves the new background.
     * @param  int|null  $newFileId  The uploaded file the background now uses, null for none.
     * @return bool False when another change held the lock for too long; nothing was saved.
     */
    private function changeBackground(\Closure $applyChange, ?int $newFileId): bool
    {
        $userId = (int) session('userdata.id');
        $lock = $this->cache->lock('users.background.'.$userId, self::LOCK_SECONDS);

        try {
            $lock->block(self::LOCK_WAIT_SECONDS);
        } catch (LockTimeoutException $exception) {
            Log::warning('Background change skipped: another change for this user is still running', ['userId' => $userId]);

            return false;
        }

        try {
            $applyChange();
            $this->replaceUploadedBackgroundFile($userId, $newFileId);
        } finally {
            $lock->release();
        }

        return true;
    }

    /**
     * Remember the uploaded file the background now uses (null: none) and delete the one it used
     * before. Only that file is deleted: the "user" files module also holds profile pictures.
     * Runs inside changeBackground's lock.
     */
    private function replaceUploadedBackgroundFile(int $userId, ?int $newFileId): void
    {
        $settingKey = 'usersettings.'.$userId.'.backgroundFileId';
        $previousFileId = (int) $this->settings->getSetting($settingKey, 0);

        if ($newFileId !== null) {
            $this->settings->saveSetting($settingKey, $newFileId);
        } elseif ($previousFileId > 0) {
            $this->settings->deleteSetting($settingKey);
        }

        if ($previousFileId > 0 && $previousFileId !== $newFileId) {
            $this->files->deleteFile($previousFileId);
        }
    }

    /**
     * A request to the Unsplash API authenticated with the configured access key.
     */
    private function unsplashRequest(): PendingRequest
    {
        return $this->http
            ->baseUrl(self::UNSPLASH_API_URL)
            ->withHeaders([
                'Authorization' => 'Client-ID '.trim((string) $this->config->unsplashAccessKey),
                'Accept-Version' => 'v1',
            ])
            ->connectTimeout(5)
            ->timeout(10);
    }

    /**
     * Report a photo download to Unsplash. Failures are logged and otherwise ignored, the
     * background is already set.
     */
    private function trackUnsplashDownload(string $downloadLocation): void
    {
        if (! str_starts_with($downloadLocation, self::UNSPLASH_API_URL.'/')) {
            return;
        }

        try {
            $this->unsplashRequest()->get(substr($downloadLocation, strlen(self::UNSPLASH_API_URL)));
        } catch (\Throwable $exception) {
            Log::warning('Unsplash download tracking failed: '.$exception->getMessage());
        }
    }

    private function isUnsplashImageUrl(string $url): bool
    {
        return parse_url($url, PHP_URL_SCHEME) === 'https'
            && parse_url($url, PHP_URL_HOST) === self::UNSPLASH_IMAGE_HOST;
    }

    /**
     * Whether an uploaded file is a JPEG, PNG, GIF or WebP image, by name and by content.
     * SVG is refused, it can carry scripts.
     *
     * @param  array  $upload  One entry of $_FILES.
     */
    private function isImageUpload(array $upload): bool
    {
        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($upload['tmp_name'])) {
            return false;
        }

        $extension = strtolower(pathinfo((string) ($upload['name'] ?? ''), PATHINFO_EXTENSION));
        if (! in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            return false;
        }

        $imageInfo = @getimagesize((string) $upload['tmp_name']);

        return $imageInfo !== false && in_array($imageInfo[2], self::IMAGE_TYPES, true);
    }

    /**
     * Unsplash asks that links back to them carry the app as referral source.
     */
    private function withReferral(string $url): string
    {
        return $url.(str_contains($url, '?') ? '&' : '?').'utm_source=leantime&utm_medium=referral';
    }
}
