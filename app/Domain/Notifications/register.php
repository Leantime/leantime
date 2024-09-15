<?php

use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Notifications\Listeners\NotifyProjectUsers;

EventDispatcher::addEventListener('domain.services.projects.notifyProjectUsers', new NotifyProjectUsers);
