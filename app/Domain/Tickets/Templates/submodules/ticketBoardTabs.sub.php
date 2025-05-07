

<?php

use Leantime\Core\Controller\Frontcontroller;

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
        <li class="<?= findActive('Kanban'); ?>">
            <a href="<?= BASE_URL ?>/tickets/showKanban<?= $tpl->get('searchParams') ?>" preload="mouseover">
                <?= $tpl->__('links.kanban') ?>
            </a>
        </li>
        <li class="<?= findActive('showAll'); ?>">
            <a href="<?= BASE_URL ?>/tickets/showAll<?= $tpl->get('searchParams') ?>" preload="mouseover">
                <?= $tpl->__('links.table') ?>
            </a>
        </li>
        <li class="<?= findActive('showList'); ?>">
            <a href="<?= BASE_URL ?>/tickets/showList<?= $tpl->get('searchParams') ?>" preload="mouseover">
                <?= $tpl->__('links.list') ?>
            </a>
        </li>
    </ul>
</div>
