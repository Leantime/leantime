<?php

namespace Leantime\Domain\Dev\Controllers;

use Leantime\Core\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Component preview controller for development.
 * Only accessible when LEAN_DEBUG=1.
 */
class ComponentPreview extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function init(): void {}

    /**
     * Render the component preview page.
     */
    public function get(): Response
    {
        return $this->tpl->display('dev.componentPreview', 'app', 200);
    }
}
