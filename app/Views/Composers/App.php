<?php

namespace Leantime\Views\Composers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Core\UI\Composer;
use Leantime\Core\UI\Theme;
use Leantime\Domain\Menu\Repositories\Menu;

class App extends Composer
{
    use DispatchesEvents;

    public static array $views = [
        'global::layouts.app',
    ];

    private Menu $menuRepo;

    private Theme $themeCore;

    private TrustedAppUrl $trustedAppUrl;

    public function init(Menu $menuRepo, Theme $themeCore, TrustedAppUrl $trustedAppUrl): void
    {
        $this->menuRepo = $menuRepo;
        $this->themeCore = $themeCore;
        $this->trustedAppUrl = $trustedAppUrl;
    }

    /**
     * @throws BindingResolutionException
     */
    public function with(): array
    {
        // These needs to live in the main app since the menu open or closed changes the entire html layout
        if (session()->exists('userdata')) {
            session(['menuState' => $this->menuRepo->getSubmenuState('mainMenu') ?: 'open']);
        }

        $menuType = $this->menuRepo->getSectionMenuType(FrontcontrollerCore::getCurrentRoute(), 'project');

        $announcement = null;
        $announcement = self::dispatch_filter('appAnnouncement', $announcement);

        return [
            'module' => strtolower(FrontcontrollerCore::getModuleName()),
            'section' => $menuType,
            'appAnnouncement' => $announcement,
            'appUrlWarning' => $this->appUrlWarning(),
            'themeBgUrl' => $this->themeCore->getBackgroundImage(),
        ];
    }

    /**
     * Warning for owners/admins when email links have no trusted app URL, or when the recorded
     * URL differs from the host they are using right now. Null when nothing needs saying.
     */
    private function appUrlWarning(): ?string
    {
        if (! session()->exists('userdata')) {
            return null;
        }

        try {
            $warning = $this->trustedAppUrl->adminWarning(session('userdata.role'), request());

            if ($warning === TrustedAppUrl::WARNING_MISSING) {
                return __('text.app_url_missing_warning');
            }

            if ($warning === TrustedAppUrl::WARNING_MISMATCH) {
                return sprintf(
                    __('text.app_url_mismatch_warning'),
                    $this->trustedAppUrl->get(),
                    TrustedAppUrl::urlFromRequest(request())
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Could not check the application URL: '.$e->getMessage());
        }

        return null;
    }
}
