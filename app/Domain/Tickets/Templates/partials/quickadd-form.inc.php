<?php
/**
 * Quick-add form partial for Kanban columns
 *
 * @var int $statusId - Status column ID
 * @var string|null $swimlaneKey - Swimlane identifier
 * @var bool $isEmpty - Whether column is empty
 * @var array|null $reopenState - Session flash data
 */
$isActive = ! empty($reopenState)
    && $reopenState['status'] == $statusId
    && ($reopenState['swimlane'] ?? null) == ($swimlaneKey ?? null);

$savedHeadline = $isActive ? ($reopenState['headline'] ?? '') : '';
$hasError = $isActive && ! empty($reopenState['error']);
?>

<div class="quickaddContainer <?= $isEmpty ? 'quickaddContainer--empty' : '' ?>" data-status="<?= $statusId ?>" data-swimlane="<?= $swimlaneKey ?? '' ?>">
    <a href="javascript:void(0);"
       class="quickAddLink <?= $isEmpty ? 'empty-state' : 'inline-add' ?>"
       onclick="leantime.kanbanController.toggleQuickAdd(this)"
       aria-expanded="<?= $isActive ? 'true' : 'false' ?>"
       aria-controls="quickadd-form-<?= $statusId ?>-<?= $swimlaneKey ?? 'default' ?>"
       style="<?= $isActive ? 'display:none;' : '' ?>">
        <i class="fa-solid fa-plus"></i>
        <span>Add To-Do</span>
    </a>

    <form method="post"
          class="quickAddForm <?= $isActive ? 'active' : '' ?>"
          id="quickadd-form-<?= $statusId ?>-<?= $swimlaneKey ?? 'default' ?>"
          data-quickadd-form
          style="<?= $isActive ? '' : 'display:none;' ?>"
          data-submitting="false">
        <input type="hidden" name="quickadd" value="1" />
        <input type="hidden" name="status" value="<?= $statusId ?>" />
        <input type="hidden" name="swimlane" value="<?= $swimlaneKey ?? '' ?>" />
        <input type="hidden" name="groupBy" value="<?= $currentGroupBy ?? '' ?>" />
        <input type="hidden" name="milestone" value="<?= $searchCriteria['milestone'] ?? '' ?>" />
        <input type="hidden" name="sprint" value="<?= session('currentSprint') ?? '' ?>" />
        <input type="hidden" name="stay_open" value="0" data-stay-open-input />

        <i class="fa fa-circle-question quickAddHelp"
           data-tippy-content="<strong>Keyboard Shortcuts:</strong><br>• Enter - Save and add another<br>• Shift+Enter - Save and close<br>• Esc - Cancel"
           tabindex="0"
           aria-label="Keyboard shortcuts help"></i>

        <div class="form-group">
            <label for="headline-<?= $statusId ?>-<?= $swimlaneKey ?? 'default' ?>" class="sr-only">Task name</label>
            <input type="text"
                   name="headline"
                   id="headline-<?= $statusId ?>-<?= $swimlaneKey ?? 'default' ?>"
                   class="form-control quickAddInput <?= $hasError ? 'error' : '' ?>"
                   placeholder="What are you working on? ↵"
                   value="<?= htmlspecialchars($savedHeadline) ?>"
                   <?= $isActive ? 'autofocus' : '' ?>
                   data-quickadd-input />

            <?php if ($hasError) { ?>
                <div class="error-message" role="alert"><?= htmlspecialchars($reopenState['error']) ?></div>
            <?php } ?>

            <div id="quick-add-help-<?= $statusId ?>-<?= $swimlaneKey ?? 'default' ?>" class="sr-only">
                Press Enter to save and close. Press Shift plus Enter to save and add another task. Press Escape to cancel.
            </div>
        </div>

        <div class="formButtonContainer">
            <button type="submit" class="btn btn-primary" onclick="this.closest('form').dataset.submitting = 'true'; this.closest('form').querySelector('[data-stay-open-input]').value = '0';">Save</button>
            <button type="button" class="btn btn-secondary"
                    onclick="leantime.kanbanController.toggleQuickAdd(this.closest('.quickaddContainer').querySelector('.quickAddLink'))">
                Cancel
            </button>
            <i class="fa fa-circle-question quickAddHelp"
               tabindex="0"
               title="Enter: Save and close&#10;Shift+Enter: Save and add another&#10;Esc: Cancel"
               aria-label="Enter: Save and close, Shift+Enter: Save and add another, Esc: Cancel"></i>
        </div>
    </form>
</div>
