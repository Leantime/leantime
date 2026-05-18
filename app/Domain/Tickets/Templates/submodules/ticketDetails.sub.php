<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $tpl->get('ticket');
$remainingHours = $tpl->get('remainingHours');
$statusLabels = $tpl->get('statusLabels');
$ticketTypes = $tpl->get('ticketTypes');

?>
<input type="hidden" value="<?php $tpl->e($ticket->id); ?>" name="id" autocomplete="off" readonly />

<div class="row">
    <div class="col-md-9">
        <div class="row marginBottom">
            <div class="col-md-12">

                <div class="form-group">
                    <input type="text" value="<?php $tpl->e($ticket->headline); ?>" name="headline" class="main-title-input" autocomplete="off" style="width:99%; margin-bottom:10px;" placeholder="<?= $tpl->__('input.placeholders.enter_title_of_todo') ?>" />
                </div>
                <!-- Status -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.todo_status'); ?></label>
                    <div class="">
                        <select
                            id="status-select"
                            class=""
                            name="status"
                            data-placeholder="<?php echo isset($ticket->status) ? $statusLabels[$ticket->status]['name'] ?? '' : ''; ?>">
                            <?php foreach ($statusLabels as $key => $label) { ?>
                                <option value="<?php echo $key; ?>"
                                    <?php if ($ticket->status == $key) {
                                        echo "selected='selected'";
                                    } ?>><?php echo $tpl->escape($label['name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- Priority -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.priority'); ?></label>
                    <div class="">
                        <select id='priority' name='priority' class="">
                            <option value=""><?php echo $tpl->__('label.priority_not_defined'); ?></option>
                            <?php foreach ($tpl->get('priorities') as $priorityKey => $priorityValue) {
                                echo "<option value='".$priorityKey."' ";
                                if ($priorityKey == $ticket->priority) {
                                    echo "selected='selected'";
                                }
                                echo '>'.$priorityValue.'</option>';
                            } ?>
                        </select>
                    </div>
                </div>

                <!-- Effort -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.effort'); ?></label>
                    <div class="">
                        <select id='storypoints' name='storypoints' class="">
                            <option value=""><?php echo $tpl->__('label.effort_not_defined'); ?></option>
                            <?php foreach ($tpl->get('efforts') as $effortKey => $effortValue) {
                                echo "<option value='".$effortKey."' ";
                                if ($effortKey == $ticket->storypoints) {
                                    echo "selected='selected'";
                                }
                                echo '>'.$effortValue.'</option>';
                            } ?>
                        </select>
                    </div>
                </div>

                <!-- Editor -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.editor'); ?></label>
                    <div class="">

                        <select data-placeholder="<?php echo $tpl->__('label.filter_by_user'); ?>" style="width:175px;"
                            name="editorId" id="editorId-select" class="user-select tw-mr-sm">
                            <option value=""><?php echo $tpl->__('label.not_assigned_to_user'); ?></option>
                            <?php foreach ($tpl->get('users') as $userRow) { ?>
                                <?php echo "<option value='".$userRow['id']."'";

                                if ($ticket->editorId == $userRow['id']) {
                                    echo " selected='selected' ";
                                }

                                echo '>'.$tpl->escape($userRow['firstname'].' '.$userRow['lastname']).'</option>'; ?>

                            <?php } ?>
                        </select>&nbsp;
                    </div>
                    <div style="padding-top:6px;">
                        <?php if ($login::userIsAtLeast($roles::$editor)) {  ?>
                            <a href="javascript:void(0);" onclick="jQuery('#editorId-select').val(<?php echo session('userdata.id'); ?>).trigger('chosen:updated');"><?php echo $tpl->__('label.assign_to_me'); ?></a>
                        <?php } ?>
                    </div>
                </div>

                <!-- Collaborators -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.collaborators'); ?></label>
                    <div class="">
                        <select data-placeholder="<?php echo $tpl->__('label.filter_by_user'); ?>"
                            style="width:175px;"
                            name="collaborators[]"
                            id="collaborators-select"
                            class="user-select tw-mr-sm"
                            multiple>
                            <?php foreach ($tpl->get('users') as $userRow) { ?>
                                <option value="<?php echo $userRow['id']; ?>"
                                    <?php if (in_array($userRow['id'], $ticket->collaborators ?? [])) {
                                        echo "selected='selected'";
                                    } ?>>
                                    <?php echo $tpl->escape($userRow['firstname'].' '.$userRow['lastname']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- Due Date -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.due_date'); ?></label>
                    <div class="">
                        <input type="text" class="dates" style="width:110px;" id="deadline" autocomplete="off"
                            value="<?= format($ticket->dateToFinish)->date(); ?>"
                            name="dateToFinish" placeholder="<?= $tpl->__('language.dateformat') ?>" />

                        <input type="time" class="timepicker tw-mr-sm" style="width:120px;" id="dueTime" autocomplete="off"
                            value="<?= format($ticket->dateToFinish)->time24(); ?>"
                            name="timeToFinish" />
                    </div>
                    <div style="padding-top:6px;">
                        <?php $tpl->dispatchTplEvent('afterDates', ['ticket' => $ticket]); ?>
                    </div>
                </div>

                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.tags'); ?></label>
                    <div class="">
                        <input type="text" value="<?php $tpl->e($ticket->tags); ?>" name="tags" id="tags" />
                    </div>
                </div>

                <?php if (empty($ticket->id)) { ?>
                    <div class="form-group tw-flex tw-w-3/5">
                        <label class="control-label tw-mx-m tw-w-[100px]">
                            <?php echo $tpl->__('label.select_file'); ?>
                            <br />
                            <small><?php echo $tpl->__('label.optional'); ?></small>
                        </label>
                        <div>
                            <div class="fileupload fileupload-new" data-provides="fileupload">
                                <input type="hidden" />
                                <div class="input-append">
                                    <div class="uneditable-input span3">
                                        <i class="fa-file fileupload-exists"></i>
                                        <span class="fileupload-preview"></span>
                                    </div>
                                    <span class="btn btn-file">
                                        <span class="fileupload-new"><?php echo $tpl->__('buttons.select_file'); ?></span>
                                        <span class="fileupload-exists"><?php echo $tpl->__('buttons.change'); ?></span>
                                        <input type="file" name="referenceFile" />
                                    </span>
                                    <a href="#" class="btn fileupload-exists" data-dismiss="fileupload">
                                        <?php echo $tpl->__('buttons.remove'); ?>
                                    </a>
                                </div>
                            </div>
                            <small><?php echo $tpl->__('text.attach_reference_file_to_todo'); ?></small>
                        </div>
                    </div>
                <?php } ?>
                <br />

                <div class="form-group" id="descriptionEditor">
                    <textarea name="description" id="ticketDescription"
                        class="tiptapComplex"><?php echo $ticket->description !== null ? htmlentities($ticket->description) : ''; ?></textarea><br />
                </div>
                <input type="hidden" name="acceptanceCriteria" value="" />

            </div>
        </div>

        <div class="sticky-modal-footer">
            <div class="row">
                <div class="col-md-12" style="margin-top:15px;">
                    <input type="hidden" name="saveTicket" value="1" />
                    <input type="hidden" id="saveAndCloseButton" name="saveAndCloseTicket" value="0" />

                    <input type="submit" name="saveTicket" class="saveTicketBtn" value="<?php echo $tpl->__('buttons.save'); ?>" />
                    <input type="submit" name="saveAndCloseTicket" class="btn btn-outline" onclick="jQuery('#saveAndCloseButton').val('1');" value="<?php echo $tpl->__('buttons.save_and_close'); ?>" />
                </div>
            </div>
        </div>

        <?php if ($ticket->id) { ?>
            <br />
            <hr />
            <?php $tpl->dispatchTplEvent('beforeSubtasks', ['ticketId' => $ticket->id]); ?>
            <h4 class="widgettitle title-light"><i class="fa-solid fa-sitemap"></i> <?php echo $tpl->__('subtitles.subtasks'); ?></h4>

            <div
                id="ticketSubtasks"
                hx-get="<?= BASE_URL ?>/tickets/subtasks/get?ticketId=<?= $ticket->id ?>"
                hx-trigger="load, subtasksUpdated from:body"
                hx-indicator=".subtaskIndicator"></div>
            <div class="htmx-indicator subtaskIndicator">
                Loading Subtasks ...<br /><br />
            </div>

            <h4 class="widgettitle title-light"><span
                    class="fa-solid fa-comments"></span><?php echo $tpl->__('subtitles.discussion'); ?></h4>

            <div class="row-fluid">
                <form method="post" action="<?= BASE_URL ?>/tickets/showTicket/<?php echo $ticket->id; ?>" class="formModal">
                    <input type="hidden" name="comment" value="1" />
                    <?php
                    $tpl->assign('formUrl', ''.BASE_URL.'/tickets/showTicket/'.$ticket->id.'');

            $tpl->displaySubmodule('comments-generalComment');
            ?>
                </form>
            </div>
        <?php } ?>
    </div>
    <div class="col-md-3">

        <div class="row marginBottom">
            <div class="col-md-12">
                <h5 class="accordionTitle" id="accordion_link_tickets-organization" style="padding-bottom:15px; font-size:var(--font-size-l)">
                    <a href="javascript:void(0)"
                        class="accordion-toggle"
                        id="accordion_toggle_tickets-organization"
                        onclick="leantime.snippets.accordionToggle('tickets-organization');">
                        <i class="fa fa-angle-down"></i>
                        <span class="fa fa-folder-open"></span>
                        <?php echo $tpl->__('subtitles.organization'); ?>
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-tickets-organization" style="padding-left:0">

                    <!-- Type -->
                    <div class="form-group">
                        <label class="control-label"><?php echo $tpl->__('label.todo_type'); ?></label>
                        <div class="">
                            <select id='type' name='type' class="span11">
                                <?php foreach ($ticketTypes as $types) {
                                    echo "<option value='".strtolower($types)."' ";
                                    if (strtolower($types) == strtolower($ticket->type ?? '')) {
                                        echo "selected='selected'";
                                    }

                                    echo '>'.$tpl->__('label.'.strtolower($types)).'</option>';
                                } ?>
                            </select><br />
                        </div>
                    </div>

                    <!-- Project -->
                    <div class="form-group">
                        <label class="control-label"><?= $tpl->__('label.project') ?>
                            <?php if ($tpl->get('lockProjectMilestone')) { ?>
                                <span style="font-size:10px;opacity:.5;margin-left:4px;"><i class="fa fa-lock"></i> locked</span>
                            <?php } ?>
                        </label>
                        <?php if ($tpl->get('lockProjectMilestone')) { ?>
                            <input type="hidden" name="projectId" value="<?= (int) $ticket->projectId ?>" />
                            <div class="tw-w-full" style="padding:6px 10px; background:var(--secondary-background); border:1px solid rgba(0,0,0,.1); border-radius:var(--input-radius,4px); font-size:13px; opacity:.75;">
                                <?= $tpl->escape($tpl->get('lockedProjectName') ?? '') ?>
                            </div>
                        <?php } else { ?>
                            <select name="projectId" id="projectId-select" class="tw-w-full">
                                <?php foreach ($allAssignedprojects as $project) { ?>
                                    <option value="<?= $project['id'] ?>"
                                        <?php
                                        if ($ticket->projectId == $project['id']) {
                                            echo 'selected';
                                        } elseif (session('currentProject') == $project['id']) {
                                            echo 'selected';
                                        }
                                    ?>><?= $tpl->escape($project['name']); ?></option>
                                <?php } ?>
                            </select>
                        <?php } ?>
                    </div>

                    <!-- Milestones -->
                    <div class="form-group">
                        <label class="control-label"><?php echo $tpl->__('label.milestone'); ?>
                            <?php if ($tpl->get('lockProjectMilestone')) { ?>
                                <span style="font-size:10px;opacity:.5;margin-left:4px;"><i class="fa fa-lock"></i> locked</span>
                            <?php } ?>
                        </label>
                        <?php if ($tpl->get('lockProjectMilestone')) { ?>
                            <input type="hidden" name="milestoneid" value="<?= (int) $ticket->milestoneid ?>" />
                            <div class="span11" style="padding:6px 10px; background:var(--secondary-background); border:1px solid rgba(0,0,0,.1); border-radius:var(--input-radius,4px); font-size:13px; opacity:.75;">
                                <?= $tpl->escape($tpl->get('lockedMilestoneName') ?? '') ?>
                            </div>
                        <?php } else { ?>
                            <div class="">
                                <div class="form-group">
                                    <select name="milestoneid" id="milestoneid-select" class="span11">
                                        <option value=""><?php echo $tpl->__('label.not_assigned_to_milestone'); ?></option>
                                        <?php foreach ($tpl->get('milestones') as $milestoneRow) { ?>
                                            <?php echo "<option value='".$milestoneRow->id."'";
                                            if ($ticket->milestoneid == $milestoneRow->id) {
                                                echo " selected='selected' ";
                                            }
                                            echo '>'.$tpl->escape($milestoneRow->headline).'</option>'; ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Related -->
                    <div class="form-group">
                        <label class="control-label"><?php echo $tpl->__('label.related_to'); ?></label>
                        <div class="">
                            <div class="form-group">
                                <select name="dependingTicketId" class="span11">
                                    <option value=""><?php echo $tpl->__('label.not_related'); ?></option>
                                    <?php
                                    if (is_array($tpl->get('ticketParents'))) {
                                        foreach ($tpl->get('ticketParents') as $ticketRow) {
                                            ?>
                                            <?php echo "<option value='".$ticketRow->id."'";

                                            if (($ticket->dependingTicketId == $ticketRow->id)) {
                                                echo " selected='selected' ";
                                            }

                                            echo '>'.$tpl->escape($ticketRow->headline).'</option>'; ?>

                                    <?php
                                        }
                                    } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <?php $tpl->dispatchTplEvent('beforeEndRightColumn', ['ticket' => $ticket]); ?>
    </div>
</div>

<script>
    jQuery(document).ready(function() {
        //Set accordion states
        //All accordions start open
        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initComplexEditor();
        }

        Prism.highlightAll();

        var baseUrl = '<?= BASE_URL ?>';
        var currentUserId = <?= (int) session('userdata.id') ?>;

        function reloadChosen($el) {
            if (typeof jQuery.fn.chosen !== 'undefined') {
                $el.trigger('chosen:updated');
            }
        }

        // Reload milestones when project changes
        function loadMilestonesForProject(projectId) {
            var $sel = jQuery('#milestoneid-select');
            $sel.prop('disabled', true);
            fetch(baseUrl + '/hx/tickets/milestones/byProject?projectId=' + encodeURIComponent(projectId), {
                credentials: 'include',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.text(); })
            .then(function (html) { $sel.html(html).prop('disabled', false); reloadChosen($sel); })
            .catch(function () { $sel.prop('disabled', false); });
        }

        // Reload assigned-to and collaborators when project changes
        function loadUsersForProject(projectId) {
            var $editor = jQuery('#editorId-select');
            var $collabs = jQuery('#collaborators-select');
            [$editor, $collabs].forEach(function ($s) { $s.prop('disabled', true); });

            fetch(baseUrl + '/hx/tickets/milestones/usersByProject?projectId=' + encodeURIComponent(projectId), {
                credentials: 'include',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (users) {
                // Build editor options
                var editorOpts = '<option value=""><?= $tpl->__('label.not_assigned_to_user') ?></option>';
                // Build collaborator options
                var collabOpts = '';
                users.forEach(function (u) {
                    var escaped = jQuery('<span>').text(u.name).html();
                    editorOpts += '<option value="' + u.id + '">' + escaped + '</option>';
                    collabOpts += '<option value="' + u.id + '">' + escaped + '</option>';
                });
                $editor.html(editorOpts).prop('disabled', false);
                $collabs.html(collabOpts).prop('disabled', false);
                reloadChosen($editor);
                reloadChosen($collabs);
            })
            .catch(function () {
                [$editor, $collabs].forEach(function ($s) { $s.prop('disabled', false); });
            });
        }

        <?php if (! $tpl->get('lockProjectMilestone')) { ?>
        // On project dropdown change reload both milestones and users
        jQuery('#projectId-select').on('change', function () {
            var pid = jQuery(this).val();
            loadMilestonesForProject(pid);
            loadUsersForProject(pid);
        });

        // Initial load — use the server-rendered project ID as the authoritative source
        var initialProjectId = '<?= (int) ($ticket->projectId ?: session('currentProject')) ?>';
        if (initialProjectId) {
            loadMilestonesForProject(initialProjectId);
            loadUsersForProject(initialProjectId);
        }
        <?php } else { ?>
        // Fields are locked — only reload users for the locked project
        var initialProjectId = '<?= (int) ($ticket->projectId ?: session('currentProject')) ?>';
        if (initialProjectId) {
            loadUsersForProject(initialProjectId);
        }
        <?php } ?>

    });
</script>
