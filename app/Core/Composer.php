<?php

namespace Leantime\Core;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\View\View;
use Illuminate\Support\Fluent;

/**
 *
 */

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
    public static $views;

    /**
     * Current view
     *
     * @var View
     */
    protected $view;

    /**
     * Current view data
     *
     * @var Fluent
     */
    protected $data;

    /**
     * Compose the view before rendering.
     *
     * @param View $view
     * @return void
     * @throws BindingResolutionException
     */
    public function compose(View $view)
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
    protected function merge()
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
    protected function with()
    {
        return [];
    }
}
