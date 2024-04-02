<?php

use Leantime\Core\Events;
use Leantime\Domain\Notifications\Listeners\NotifyProjectUsers;

Events::add_event_listener("domain.services.projects.notifyProjectUsers", new NotifyProjectUsers());
