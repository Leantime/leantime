<?php

namespace Leantime\Domain\ContentTemplates;

use Illuminate\Support\ServiceProvider;
use Leantime\Domain\ContentTemplates\Services\Appliers\CanvasItemsApplier;
use Leantime\Domain\ContentTemplates\Services\Appliers\WikiApplier;
use Leantime\Domain\ContentTemplates\Services\ContentTemplateRegistry;

/**
 * Wires the ContentTemplates domain.
 *
 * The registry is bound as a singleton so library roots registered by plugins
 * during their own boot persist across the request. Both built-in appliers
 * (canvas items, wiki) self-register against the registry on boot so callers
 * don't need to wire them up. Plugins can register additional appliers in
 * their own register.php with $registry->registerApplier(...).
 */
class ContentTemplatesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ContentTemplateRegistry::class);
        $this->app->singleton(CanvasItemsApplier::class);
        $this->app->singleton(WikiApplier::class);
    }

    public function boot(): void
    {
        /** @var ContentTemplateRegistry $registry */
        $registry = $this->app->make(ContentTemplateRegistry::class);

        // WikiApplier is registered first so it claims "wiki" before the
        // catch-all CanvasItemsApplier. The registry uses one applier per
        // appliesTo, so order matters here only because supports() on the
        // canvas applier excludes 'wiki' explicitly — kept defensive.
        $registry->registerApplier('wiki', $this->app->make(WikiApplier::class));

        // CanvasItemsApplier is the catch-all for any non-wiki appliesTo. We
        // can't enumerate every canvas type at boot (plugins add their own),
        // so the registry consults supports() at apply time when a template's
        // appliesTo isn't explicitly bound.
        $canvasApplier = $this->app->make(CanvasItemsApplier::class);
        foreach (['logicmodel', 'goal', 'leancanvas', 'swot'] as $canvasType) {
            $registry->registerApplier($canvasType, $canvasApplier);
        }
    }
}
