

<?php

use Leantime\Core\Routing\Frontcontroller;

/**
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 */
function findActive($route): string
{
    if (str_contains(Frontcontroller::getCurrentRoute(), $route)) {
        return 'active';
    }

    return '';
}

findActive('');
?>

<div class="maincontentinner tabs">
    <ul>
        <li class="<?= findActive('roadmap'); ?>">
            <a href="<?= BASE_URL ?>/tickets/roadmap<?= $tpl->get('searchParams') ?>" preload="mouseover">
                <?= $tpl->__('links.timeline') ?>
            </a>
        </li>
        <li class="<?= findActive('showAllMilestones'); ?>">
            <a href="<?= BASE_URL ?>/tickets/showAllMilestones<?= $tpl->get('searchParams') ?>" preload="mouseover">
                <?= $tpl->__('links.table') ?>
            </a>
        </li>
        <li class="<?= findActive('Calendar'); ?>">
            <a href="<?= BASE_URL ?>/tickets/showProjectCalendar<?= $tpl->get('searchParams') ?>" preload="mouseover">
                <?= $tpl->__('links.calendar') ?>
            </a>
        </li>
    </ul>
</div>
