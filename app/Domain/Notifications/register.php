<?php

use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Notifications\Listeners\NotifyProjectUsers;

EventDispatcher::addEventListener('leantime.domain.projects.services.projects.notifyProjectUsers.notifyProjectUsers', NotifyProjectUsers::class);
