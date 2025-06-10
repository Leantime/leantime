<?php

namespace Leantime\Domain\Setting\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Files\Contracts\FileManagerInterface;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @api
 */
class Setting
{
    use DispatchesEvents;

    private FileManagerInterface $fileManager;

    public function __construct(
        public SettingRepository $settingsRepo,
        FileManagerInterface $fileManager
    ) {
        $this->fileManager = $fileManager;
    }

    /**
     * @throws BindingResolutionException
     *
     * @api
     */
    public function setLogo($file): bool
    {
        try {
            $uploadedFile = $file['file'];

            // Create a UploadedFile instance
            $symfonyFile = new UploadedFile(
                $uploadedFile['tmp_name'],
                $uploadedFile['name'],
                $uploadedFile['type'],
                $uploadedFile['error'],
                true
            );

            $logo = $this->fileManager->upload($symfonyFile, 'public');

            if ($logo['newPath'] !== false) {

                // Save the setting
                $this->settingsRepo->saveSetting('companysettings.logoPath', $logo['newPath']);

                $logoPath = $this->fileManager->getFileUrl($logo['newPath'], 'public', (60 * 24));
                // Update the session
                session(['companysettings.logoPath' => $logoPath]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error($e);

            return false;
        }
    }

    /**
     * @api
     */
    public function resetLogo(): void
    {

        $this->settingsRepo->deleteSetting('companysettings.logoPath');
        session()->forget('companysettings.logoPath');
        session(['companysettings.logoPath' => '']);
    }

    /**
     * @api
     */
    public function saveSetting($key, $value): bool
    {
        return $this->settingsRepo->saveSetting($key, $value);
    }

    /**
     * @return false|mixed
     *
     * @api
     */
    public function getSetting($key, $default = false): mixed
    {
        return $this->settingsRepo->getSetting($key, $default);
    }

    /**
     * @api
     */
    public function deleteSetting($key): void
    {
        $this->settingsRepo->deleteSetting($key);
    }

    /**
     * @api
     */
    public function getSettingsRepo(): SettingRepository
    {
        return $this->settingsRepo;
    }

    /**
     * @api
     */
    public function setSettingsRepo(SettingRepository $settingsRepo): void
    {
        $this->settingsRepo = $settingsRepo;
    }

    /**
     * Gets the company id (Sets if it's not set)
     *
     **/
    public function getCompanyId(): string
    {
        $companyId = $this->getSetting('companysettings.telemetry.anonymousId');

        if (! $companyId) {
            $companyId = Uuid::uuid4()->toString();
            $this->saveSetting('companysettings.telemetry.anonymousId', $companyId);
        }

        return $companyId;
    }

    public function onboardingHandler()
    {

        $completedOnboarding = $this->settingsRepo->getSetting('companysettings.completedOnboarding');
        $isFirstLogin = $this->settingsRepo->getSetting('user.'.session('userdata.id').'.firstLoginCompleted');

        if ($isFirstLogin && $completedOnboarding) {
            $isFirstLogin = false;
        }

        return self::dispatchFilter('completeOnboardingHandler', $isFirstLogin);

    }
}
