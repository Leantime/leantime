<?php

namespace Leantime\Core\UI;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Fluent;
use Illuminate\View\View;

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
     */
    protected View $view;

    /**
     * Current view data
     */
    protected Fluent $data;

    /**
     * Compose the view before rendering.
     *
     * @param  View  $view
     *
     * @throws BindingResolutionException
     */
    final public function compose(mixed $parameters): void
    {
        if (is_array($parameters)) {
            $this->view = $parameters[0];
        }

        if ($parameters instanceof \Illuminate\Contracts\View\View) {
            $this->view = $parameters;
        }

        $this->data = new Fluent($this->view->getData());

        if (method_exists($this, 'init')) {
            app()->call([$this, 'init']);
        }

        $this->view->with($this->merge());
    }

    /**
     * Data to be merged and passed to the view before rendering.
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
     */
    protected function with(): array
    {
        return [];
    }
}
