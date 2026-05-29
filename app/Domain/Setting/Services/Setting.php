<?php

namespace Leantime\Domain\Setting\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Files\Contracts\FileManagerInterface;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
use Leantime\Domain\Leancanvas\Repositories\Leancanvas as LeancanvaRepository;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Retroscanvas\Repositories\Retroscanvas as RetroscanvaRepository;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @api
 */
class Setting
{
    use DispatchesEvents;

    private FileManagerInterface $fileManager;

    private TicketRepository $ticketsRepo;

    private LeancanvaRepository $canvasRepo;

    private RetroscanvaRepository $retroRepo;

    private IdeaRepository $ideaRepo;

    public function __construct(
        public SettingRepository $settingsRepo,
        FileManagerInterface $fileManager,
        TicketRepository $ticketsRepo,
        LeancanvaRepository $canvasRepo,
        RetroscanvaRepository $retroRepo,
        IdeaRepository $ideaRepo
    ) {
        $this->fileManager = $fileManager;
        $this->ticketsRepo = $ticketsRepo;
        $this->canvasRepo = $canvasRepo;
        $this->retroRepo = $retroRepo;
        $this->ideaRepo = $ideaRepo;
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

    /**
     * Resolve the current label value for a given module/label key.
     *
     * Reads the current label name from the appropriate source label set
     * (ticket state labels, retro/research canvas labels, or idea labels)
     * for the given project.
     *
     * @param  string  $module  The label module (ticketlabels|retrolabels|researchlabels|idealabels).
     * @param  int  $labelKey  The label key within the module's label set.
     * @param  int  $projectId  The project the labels belong to.
     * @return string The current label name, or an empty string when not found.
     *
     * @api
     */
    public function getProjectLabel(string $module, int $labelKey, int $projectId): string
    {
        if ($module === 'ticketlabels') {
            $stateLabels = $this->ticketsRepo->getStateLabels();
            if (isset($stateLabels[$labelKey]['name'])) {
                return $stateLabels[$labelKey]['name'];
            }

            return '';
        }

        if ($module === 'retrolabels') {
            $stateLabels = $this->retroRepo->getCanvasLabels();
            if (isset($stateLabels[$labelKey])) {
                return $stateLabels[$labelKey];
            }

            return '';
        }

        if ($module === 'researchlabels') {
            $stateLabels = $this->canvasRepo->getCanvasLabels();
            if (isset($stateLabels[$labelKey])) {
                return $stateLabels[$labelKey];
            }

            return '';
        }

        if ($module === 'idealabels') {
            $stateLabels = $this->ideaRepo->getCanvasLabels();
            if (isset($stateLabels[$labelKey]['name'])) {
                return $stateLabels[$labelKey]['name'];
            }

            return '';
        }

        return '';
    }

    /**
     * Persist a renamed project label for a given module/label key.
     *
     * Fetches the source label set, updates the requested label, serializes the
     * result, and writes it back under the project's settings key. Also handles
     * the per-module cache/session invalidation and (for idea labels) the
     * normalization of the label array shape.
     *
     * @param  string  $module  The label module (ticketlabels|retrolabels|researchlabels|idealabels).
     * @param  int  $labelKey  The label key within the module's label set.
     * @param  string  $newLabel  The new (already sanitized) label value.
     * @param  int  $projectId  The project the labels belong to.
     *
     * @api
     */
    public function saveProjectLabel(string $module, int $labelKey, string $newLabel, int $projectId): void
    {
        if ($module === 'ticketlabels') {
            $currentStateLabels = $this->ticketsRepo->getStateLabels();

            if (isset($currentStateLabels[$labelKey]) && is_array($currentStateLabels[$labelKey])) {
                $currentStateLabels[$labelKey]['name'] = $newLabel;

                $this->settingsRepo->saveSetting(
                    'projectsettings.'.$projectId.'.ticketlabels',
                    serialize($currentStateLabels)
                );

                Cache::forget('projectsettings.'.$projectId.'.ticketlabels');
            }

            return;
        }

        if ($module === 'retrolabels') {
            $stateLabels = $this->retroRepo->getCanvasLabels();
            $stateLabels[$labelKey] = $newLabel;
            session()->forget('projectsettings.retrolabels');
            $this->settingsRepo->saveSetting(
                'projectsettings.'.$projectId.'.retrolabels',
                serialize($stateLabels)
            );

            return;
        }

        if ($module === 'researchlabels') {
            $stateLabels = $this->canvasRepo->getCanvasLabels();
            $stateLabels[$labelKey] = $newLabel;
            session()->forget('projectsettings.researchlabels');
            $this->settingsRepo->saveSetting(
                'projectsettings.'.$projectId.'.researchlabels',
                serialize($stateLabels)
            );

            return;
        }

        if ($module === 'idealabels') {
            $stateLabels = $this->ideaRepo->getCanvasLabels();
            $newStateLabels = [];
            foreach ($stateLabels as $key => $label) {
                $newStateLabels[$key] = $label['name'];
            }
            $newStateLabels[$labelKey] = $newLabel;

            session()->forget('projectsettings.idealabels');
            $this->settingsRepo->saveSetting(
                'projectsettings.'.$projectId.'.idealabels',
                serialize($newStateLabels)
            );
        }
    }

    /**
     * Build the company settings view model.
     *
     * Encapsulates the company-settings defaulting/fallback chain: colors
     * (including legacy mainColor fallback), sitename, language, message
     * frequency, default notification event types, and the default notification
     * relevance level.
     *
     * @param  string  $logoUrl  The resolved logo URL from the theme.
     * @return array{
     *     companySettings: array<string, mixed>,
     *     defaultNotificationTypes: array<int, string>,
     *     defaultRelevance: string
     * }
     *
     * @api
     */
    public function getCompanySettings(string $logoUrl): array
    {
        $companySettings = [
            'logo' => $logoUrl,
            'primarycolor' => session('companysettings.primarycolor') ?? '',
            'secondarycolor' => session('companysettings.secondarycolor') ?? '',
            'name' => session('companysettings.sitename'),
            'language' => session('companysettings.language'),
            'telemetryActive' => true,
            'messageFrequency' => '',
        ];

        $mainColor = $this->settingsRepo->getSetting('companysettings.mainColor');
        if ($mainColor !== false) {
            $companySettings['primarycolor'] = '#'.$mainColor;
            $companySettings['secondarycolor'] = '#'.$mainColor;
        }

        $primaryColor = $this->settingsRepo->getSetting('companysettings.primarycolor');
        if ($primaryColor !== false) {
            $companySettings['primarycolor'] = $primaryColor;
        }

        $secondaryColor = $this->settingsRepo->getSetting('companysettings.secondarycolor');
        if ($secondaryColor !== false) {
            $companySettings['secondarycolor'] = $secondaryColor;
        }

        $sitename = $this->settingsRepo->getSetting('companysettings.sitename');
        if ($sitename !== false) {
            $companySettings['name'] = $sitename;
        }

        $language = $this->settingsRepo->getSetting('companysettings.language');
        if ($language !== false) {
            $companySettings['language'] = $language;
        }

        $messageFrequency = $this->settingsRepo->getSetting('companysettings.messageFrequency');
        if ($messageFrequency !== false) {
            $companySettings['messageFrequency'] = $messageFrequency;
        }

        // Load default notification event types
        $defaultNotificationTypes = $this->settingsRepo->getSetting('companysettings.defaultNotificationEventTypes');
        $allCategories = array_keys(Notification::NOTIFICATION_CATEGORIES);
        if ($defaultNotificationTypes) {
            $defaultNotificationTypes = json_decode($defaultNotificationTypes, true);
        }
        if (! is_array($defaultNotificationTypes)) {
            $defaultNotificationTypes = $allCategories;
        }

        // Load default notification relevance level
        $defaultRelevance = $this->settingsRepo->getSetting('companysettings.defaultNotificationRelevance');
        if (! $defaultRelevance || ! Notification::isValidRelevanceLevel($defaultRelevance)) {
            $defaultRelevance = Notification::RELEVANCE_ALL;
        }

        return [
            'companySettings' => $companySettings,
            'defaultNotificationTypes' => $defaultNotificationTypes,
            'defaultRelevance' => $defaultRelevance,
        ];
    }

    /**
     * Persist company settings from a submitted form.
     *
     * Owns the post-time persistence: look & feel (color save + legacy mainColor
     * cleanup + session sync), main details (sitename/language/messageFrequency),
     * localization cache invalidation, notification event-type filtering and
     * relevance validation, session sync, and telemetry opt-out orchestration.
     *
     * @param  array<string, mixed>  $params  The submitted form parameters.
     * @return bool True when a settings block was persisted, false when nothing changed.
     *
     * @throws \Exception When telemetry opt-out fails.
     *
     * @api
     */
    public function saveCompanySettings(array $params): bool
    {
        $saved = false;

        // Look & feel updates
        if (isset($params['primarycolor']) && $params['primarycolor'] != '') {
            $this->settingsRepo->saveSetting('companysettings.primarycolor', htmlentities(addslashes($params['primarycolor'])));
            $this->settingsRepo->saveSetting('companysettings.secondarycolor', htmlentities(addslashes($params['secondarycolor'])));

            // Check if main color is still in the system
            // if so remove. This call should be removed in a few versions.
            $mainColor = $this->settingsRepo->getSetting('companysettings.mainColor');
            if ($mainColor !== false) {
                $this->settingsRepo->deleteSetting('companysettings.mainColor');
            }

            session(['companysettings.primarycolor' => htmlentities(addslashes($params['primarycolor']))]);
            session(['companysettings.secondarycolor' => htmlentities(addslashes($params['secondarycolor']))]);

            $saved = true;
        }

        // Main Details
        if (isset($params['name']) && $params['name'] != '' && isset($params['language']) && $params['language'] != '') {
            $this->settingsRepo->saveSetting('companysettings.sitename', htmlspecialchars(addslashes($params['name'])));
            $this->settingsRepo->saveSetting('companysettings.language', htmlentities(addslashes($params['language'])));
            $this->settingsRepo->saveSetting('companysettings.messageFrequency', (int) $params['messageFrequency']);

            // Clear the localization cache so middleware re-fetches on next request
            session()->forget('localization.cached');

            // Save default notification event types
            $defaultEventTypes = $params['defaultNotificationEventTypes'] ?? [];
            if (! is_array($defaultEventTypes)) {
                $defaultEventTypes = [];
            }
            $validCategories = array_keys(Notification::NOTIFICATION_CATEGORIES);
            $defaultEventTypes = array_values(array_intersect($defaultEventTypes, $validCategories));
            $this->settingsRepo->saveSetting(
                'companysettings.defaultNotificationEventTypes',
                json_encode($defaultEventTypes)
            );

            // Save default notification relevance level
            $defaultRelevance = $params['defaultNotificationRelevance'] ?? Notification::RELEVANCE_ALL;
            if (! Notification::isValidRelevanceLevel($defaultRelevance)) {
                $defaultRelevance = Notification::RELEVANCE_ALL;
            }
            $this->settingsRepo->saveSetting('companysettings.defaultNotificationRelevance', $defaultRelevance);

            session(['companysettings.sitename' => htmlspecialchars(addslashes($params['name']))]);
            session(['companysettings.language' => htmlentities(addslashes($params['language']))]);

            if (! empty($params['telemetryActive'])) {
                $this->settingsRepo->saveSetting('companysettings.telemetry.active', 'true');
            } else {
                // Set remote telemetry to false.
                // Resolved lazily to avoid a circular service dependency
                // (ReportService depends on this Setting service).
                app()->make(ReportService::class)->optOutTelemetry();
            }

            $saved = true;
        }

        return $saved;
    }
}
