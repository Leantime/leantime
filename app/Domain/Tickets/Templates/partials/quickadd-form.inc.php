<?php
/**
 * Opens the standard To-Do modal from each Kanban column.
 *
 * The previous inline quick-add form created a different add-task experience
 * from the main New button. Keep every Add To-Do entry point on the same form.
 */
?>

<div class="quickaddContainer tw-mb-s <?= $isEmpty ? 'quickaddContainer--empty' : '' ?>">
    <a href="<?= BASE_URL ?>/tickets/newTicket"
       class="quickAddLink form-modal <?= $isEmpty ? 'empty-state' : 'inline-add' ?>">
        <i class="fa-solid fa-plus"></i>
        <span>Add To-Do</span>
    </a>
</div>
