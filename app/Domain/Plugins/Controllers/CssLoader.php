<?php

namespace Leantime\Domain\Plugins\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Plugins\Services\Plugins as PluginService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class CssLoader extends Controller
    {
        private PluginService $pluginService;

        /**
         * @param PluginService $pluginService
         * @return void
         */
        public function init(PluginService $pluginService): void
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin]);
            $this->pluginService = $pluginService;
        }

        /**
         * @return Response
         */
        public function get(): Response
        {
            $cssFiles = self::dispatch_filter("pluginCss", []);
            $cssStrs = collect($cssFiles)
                ->filter(fn ($file) => file_exists(APP_ROOT . "/plugins/$file"))
                ->map(fn ($file) => file_get_contents(APP_ROOT . "/plugins/$file"))
                ->all();

            $response = new Response(join('', $cssStrs));
            $response->headers->set('Content-Type', 'text/css');
            return $response;
        }
    }
}
