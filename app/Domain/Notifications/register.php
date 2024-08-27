<?php

use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Notifications\Listeners\NotifyProjectUsers;

EventDispatcher::add_event_listener("domain.services.projects.notifyProjectUsers", new NotifyProjectUsers());
