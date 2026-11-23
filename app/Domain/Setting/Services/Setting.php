<?php

namespace Leantime\Domain\Setting\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\Files\Contracts\FileManagerInterface;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Notifications\Services\PushRelay;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Setting\Permissions\SettingPermissions;
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

    /** Whether the text editor suggests emojis when people type ":". On unless stored as '0'. */
    public const SETTING_EMOJI_SUGGESTIONS = 'companysettings.editor.emojiSuggestions';

    private FileManagerInterface $fileManager;

    private TicketRepository $ticketsRepo;

    private IdeaRepository $ideaRepo;

    public function __construct(
        public SettingRepository $settingsRepo,
        FileManagerInterface $fileManager,
        TicketRepository $ticketsRepo,
        IdeaRepository $ideaRepo
    ) {
        $this->fileManager = $fileManager;
        $this->ticketsRepo = $ticketsRepo;
        $this->ideaRepo = $ideaRepo;
    }

    /**
     * @throws BindingResolutionException
     *
     * @internal Not @api: invoked only by Setting\Controllers\Logo, which authorizes
     *           the upload (admin+). The company logo is a global setting, so this
     *           must not be callable over JSON-RPC by any authenticated user.
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
     * @internal Internal-only; resets the company logo. Reached only via the company-settings
     *           page (gated company.settings.view/edit) — not on the JSON-RPC surface.
     */
    public function resetLogo(): void
    {

        $this->settingsRepo->deleteSetting('companysettings.logoPath');
        session()->forget('companysettings.logoPath');
        session(['companysettings.logoPath' => '']);
    }

    /**
     * @internal Foundational key/value primitive — writes ANY setting (company.*, usersettings.*,
     *           projectsettings.*, licenses, …). Internal-only; deliberately excluded from the
     *           JSON-RPC surface (RPC write-any-setting would be a privilege-escalation hole).
     *           Callers that touch sensitive keys gate at their own boundary.
     */
    public function saveSetting($key, $value): bool
    {
        return $this->settingsRepo->saveSetting($key, $value);
    }

    /**
     * @return false|mixed
     *
     * @internal Foundational key/value primitive — reads ANY setting. Internal-only;
     *           deliberately excluded from the JSON-RPC surface (RPC read-any-setting could
     *           leak license keys / OIDC/LDAP config secrets stored as settings).
     */
    public function getSetting($key, $default = false): mixed
    {
        return $this->settingsRepo->getSetting($key, $default);
    }

    /**
     * @internal Foundational key/value primitive — deletes ANY setting. Internal-only;
     *           deliberately excluded from the JSON-RPC surface.
     */
    public function deleteSetting($key): void
    {
        $this->settingsRepo->deleteSetting($key);
    }

    /**
     * @internal Infrastructure accessor (returns the repository instance) — not an API surface.
     */
    public function getSettingsRepo(): SettingRepository
    {
        return $this->settingsRepo;
    }

    /**
     * @internal Infrastructure mutator (swaps the repository dependency) — not an API surface.
     */
    public function setSettingsRepo(SettingRepository $settingsRepo): void
    {
        $this->settingsRepo = $settingsRepo;
    }

    /**
     * The instance's anonymous company id (setting companysettings.telemetry.anonymousId),
     * created on first read. A random UUID, not sensitive: analytics clients (the mobile app)
     * scope their PostHog distinct ids with it as `{companyId}:{userId}`, matching the web.
     *
     * Intentionally no #[RequiresPermission]: any authenticated user may read it. JSON-RPC
     * still requires an authenticated caller (AuthCheck).
     *
     * @api
     */
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
     * (ticket state labels or idea labels)
     * for the given project.
     *
     * @param  string  $module  The label module (ticketlabels|idealabels).
     * @param  int  $labelKey  The label key within the module's label set.
     * @param  int  $projectId  The project the labels belong to.
     * @return string The current label name, or an empty string when not found.
     *
     * @api
     */
    #[RequiresPermission(SettingPermissions::PROJECT_LABELS, projectIdParam: 'projectId')]
    public function getProjectLabel(string $module, int|string $labelKey, int $projectId): string
    {
        if ($module === 'ticketlabels') {
            $stateLabels = $this->ticketsRepo->getStateLabels();
            if (isset($stateLabels[$labelKey]['name'])) {
                return $stateLabels[$labelKey]['name'];
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
     * @param  string  $module  The label module (ticketlabels|idealabels).
     * @param  int  $labelKey  The label key within the module's label set.
     * @param  string  $newLabel  The new (already sanitized) label value.
     * @param  int  $projectId  The project the labels belong to.
     *
     * @api
     */
    #[RequiresPermission(SettingPermissions::PROJECT_LABELS, projectIdParam: 'projectId')]
    public function saveProjectLabel(string $module, int|string $labelKey, string $newLabel, int $projectId): void
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

        if ($module === 'idealabels') {
            $stateLabels = $this->ideaRepo->getCanvasLabels();

            // Only rename a label that actually exists, mirroring the ticketlabels branch
            // above. Writing an unknown key persisted junk into the project setting, which is
            // how the board ended up permanently broken (#3685).
            if (! isset($stateLabels[$labelKey])) {
                return;
            }

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
     * frequency, the editor's emoji suggestions, default notification event
     * types, and the default notification relevance level.
     *
     * @param  string  $logoUrl  The resolved logo URL from the theme.
     * @return array{
     *     companySettings: array<string, mixed>,
     *     defaultNotificationTypes: array<int, string>,
     *     defaultRelevance: string,
     *     pushRelayEnabled: bool,
     *     pushRelayContent: string
     * }
     *
     * @api
     */
    #[RequiresPermission(SettingPermissions::COMPANY_VIEW, global: true)]
    public function getCompanySettings(string $logoUrl): array
    {
        $companySettings = [
            'logo' => $logoUrl,
            'primarycolor' => session('companysettings.primarycolor') ?? '',
            'secondarycolor' => session('companysettings.secondarycolor') ?? '',
            'name' => session('companysettings.sitename'),
            'language' => session('companysettings.language'),
            'telemetryActive' => $this->settingsRepo->getSetting('companysettings.telemetry.optOut') !== 'true',
            'telemetryAllowedByConfig' => (app('config')->allowTelemetry ?? true) === true,
            'messageFrequency' => '',
            'emojiSuggestions' => $this->settingsRepo->getSetting(self::SETTING_EMOJI_SUGGESTIONS) !== '0',
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
            // Mobile push: the relay switch (self-hosted only; Cloud sends directly), and what
            // phone notifications show, which applies to every push.
            'pushRelayEnabled' => $this->settingsRepo->getSetting(PushRelay::SETTING_ENABLED) === '1',
            'pushRelayContent' => self::pushContentLevel($this->settingsRepo->getSetting(PushRelay::SETTING_CONTENT)),
        ];
    }

    /**
     * Persist company settings from a submitted form.
     *
     * Owns the post-time persistence: look & feel (color save + legacy mainColor
     * cleanup + session sync), main details (sitename/language/messageFrequency),
     * localization cache invalidation, notification event-type filtering and
     * relevance validation, session sync, telemetry opt-out orchestration, and
     * the editor's emoji suggestions switch.
     *
     * @param  array<string, mixed>  $params  The submitted form parameters.
     * @return bool True when a settings block was persisted, false when nothing changed.
     *
     * @throws ValidationException When emojiSuggestions is sent but is not a boolean. Nothing is saved then.
     * @throws \Exception When telemetry opt-out fails.
     *
     * @api
     */
    #[RequiresPermission(SettingPermissions::COMPANY_EDIT, global: true)]
    public function saveCompanySettings(array $params): bool
    {
        $saved = false;

        // Emoji suggestions in the text editor. The switch is on by default, so it is written only
        // when sent: a caller without it (API, other forms) must not switch it off. The form posts
        // a hidden '0' before the checkbox, so an unchecked box arrives as '0'. A value that is not
        // a boolean is rejected here, before any other setting is saved, so it can neither switch
        // the suggestions off nor leave the form half saved.
        if (array_key_exists('emojiSuggestions', $params)) {
            $emojiSuggestions = $params['emojiSuggestions'] === null
                ? null
                : filter_var($params['emojiSuggestions'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($emojiSuggestions === null) {
                $message = 'emojiSuggestions must be a boolean: true, false, 1 or 0.';

                throw new ValidationException(['emojiSuggestions' => [$message]], $message);
            }

            $this->settingsRepo->saveSetting(self::SETTING_EMOJI_SUGGESTIONS, $emojiSuggestions ? '1' : '0');
            $saved = true;
        }

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

            // Mobile push relay: opt-in, and what leaves the server. The value is read as a
            // boolean (an RPC caller may send false or '0'); an unchecked box isn't posted, so a
            // missing key means off. The content level is written only when sent, so a save
            // without it keeps the stored choice (PushRelay::contentLevel() reads full by default).
            $relayEnabled = isset($params['pushRelayEnabled']) && filter_var($params['pushRelayEnabled'], FILTER_VALIDATE_BOOLEAN);
            $this->settingsRepo->saveSetting(PushRelay::SETTING_ENABLED, $relayEnabled ? '1' : '0');
            if (array_key_exists('pushRelayContent', $params)) {
                $this->settingsRepo->saveSetting(PushRelay::SETTING_CONTENT, self::pushContentLevel($params['pushRelayContent']));
            }

            session(['companysettings.sitename' => htmlspecialchars(addslashes($params['name']))]);
            session(['companysettings.language' => htmlentities(addslashes($params['language']))]);

            $saved = true;
        }

        // Telemetry toggle. Independent of the main details so an opt-out is never lost to an
        // unrelated validation. Callers that don't send the toggle (API, forms without it)
        // leave telemetry untouched. ReportService is resolved lazily to avoid a circular
        // service dependency (it depends on this Setting service).
        if (array_key_exists('telemetryActive', $params)) {
            $wasOptedOut = $this->settingsRepo->getSetting('companysettings.telemetry.optOut') === 'true';
            if (! empty($params['telemetryActive'])) {
                if ($wasOptedOut) {
                    app()->make(ReportService::class)->optInTelemetry();
                    $saved = true;
                }
            } elseif (! $wasOptedOut) {
                // Only on the transition, so the opt-out notice goes out once.
                app()->make(ReportService::class)->optOutTelemetry();
                $saved = true;
            }
        }

        return $saved;
    }

    /** A known push content level, or full. */
    private static function pushContentLevel(mixed $value): string
    {
        return in_array($value, [PushRelay::CONTENT_NAMES, PushRelay::CONTENT_MINIMAL], true) ? $value : PushRelay::CONTENT_FULL;
    }
}
