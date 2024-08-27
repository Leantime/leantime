<?php

namespace Leantime\Core\Controller;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Fluent;
use Illuminate\View\View;

/**
 *
 */
abstract class Composer
{
    /**
     * List of views to receive data by this composer
     *
     * @var string[]
     */
    public static array $views;

    /**
     * Current view
     *
     * @var View
     */
    protected View $view;

    /**
     * Current view data
     *
     * @var Fluent
     */
    protected Fluent $data;

    /**
     * Compose the view before rendering.
     *
     * @param View $view
     * @return void
     * @throws BindingResolutionException
     */
    public function compose(View $view): void
    {
        $this->view = $view;
        $this->data = new Fluent($view->getData());

        if (method_exists($this, 'init')) {
            app()->call([$this, 'init']);
        }

        $view->with($this->merge());
    }

    /**
     * Data to be merged and passed to the view before rendering.
     *
     * @return array
     */
    protected function merge(): array
    {
        return array_merge(
            $this->view->getData(),
            $this->with()
        );
    }

    /**
     * Data to be passed to view before rendering
     *
     * @return array
     */
    protected function with(): array
    {
        return [];
    }
}
