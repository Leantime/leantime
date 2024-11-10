<?php

use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Notifications\Listeners\NotifyProjectUsers;

EventDispatcher::add_event_listener("leantime.domain.projects.services.projects.notifyProjectUsers.notifyProjectUsers", new NotifyProjectUsers());
