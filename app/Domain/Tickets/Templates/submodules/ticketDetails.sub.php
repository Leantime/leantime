<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $tpl->get('ticket');
$remainingHours = $tpl->get('remainingHours');
$statusLabels = $tpl->get('statusLabels');
$ticketTypes = $tpl->get('ticketTypes');
$efforts = $tpl->get('efforts');
$priorities = $tpl->get('priorities');
$assignedUsers = $tpl->get('users');
$timezone = $tpl->get('timezone');
?>
<input type="hidden" value="<?php $tpl->e($ticket->id); ?>" name="id" autocomplete="off" readonly/>

<div class="row">
    <div class="col-md-9">
        <div class="row marginBottom">
            <div class="col-md-12">

                <div class="form-group">
                    <input type="text" value="<?php $tpl->e($ticket->headline); ?>" name="headline" class="main-title-input" autocomplete="off" style="width:99%; margin-bottom:10px;" placeholder="<?= $tpl->__('input.placeholders.enter_title_of_todo')?>"/>
                </div>
               <?php
use Leantime\Domain\Users\Services\Users;

$userService = app()->make(Users::class);

$currentUserId = session('userdata.id') ?? null;
$currentUser = $currentUserId ? $userService->getUser($currentUserId) : null;

if ($currentUser && isset($currentUser['firstname'], $currentUser['lastname'])) {
    $currentUserName = $currentUser['firstname'] . ' ' . $currentUser['lastname'];
} else {
    $currentUserName = 'Unknown User';
}
?>


                <!-- Status -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.todo_status'); ?></label>
                    <div class="">
                        <select
                            id="status-select"
                            class="autosave-field"
                            name="status"
                            data-old-status="<?php echo $statusLabels[$ticket->status]['name'] ?>"
                            data-user="<?= htmlspecialchars($currentUserName) ?>"
                            data-placeholder="<?php echo isset($ticket->status) ? $statusLabels[$ticket->status]['name'] ?? '' : ''; ?>"
                        >
                            <?php foreach ($statusLabels as $key => $label) {?>
                                <option value="<?php echo $key; ?>"
                                    <?php if ($ticket->status == $key) {
                                        echo "selected='selected'";
                                    } ?>
                                ><?php echo $tpl->escape($label['name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- Priority -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.priority'); ?></label>
                    <div class="">
                        <select id='priority' name='priority' class="autosave-field" data-old-status="<?php echo $priorities[$ticket->priority] ?? 'Priority not defined'; ?>"
                            data-user="<?= htmlspecialchars($currentUserName) ?>">
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
                        <select id='storypoints' name='storypoints' class="autosave-field" data-old-status="<?php echo $efforts[$ticket->storypoints] ?? 'Effort not defined'; ?>"
                            data-user="<?= htmlspecialchars($currentUserName) ?>">
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
                        <?php
                        $oldEditorName = '';
                        foreach ($assignedUsers as $user) {
                            if ($user['id'] == $ticket->editorId) {
                                $oldEditorName = $user['firstname'] . ' ' . $user['lastname'];
                                break;
                            }
                        }
                        ?>

                        <select data-placeholder="<?php echo $tpl->__('label.filter_by_user'); ?>" style="width:175px;"
                                name="editorId" id="editorId" class="user-select tw-mr-sm autosave-field" data-old-status="<?php if($oldEditorName == '') echo 'Not assigned'; else echo $oldEditorName;?>"
                            data-user="<?= htmlspecialchars($currentUserName) ?>">
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
                        <a href="javascript:void(0);" onclick="assignToMe();"><?php echo $tpl->__('label.assign_to_me'); ?></a>                        <?php } ?>
                    </div>
                </div>

                <!-- Due Date -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.due_date'); ?></label>
                    <div class="">
                        <input type="text" class="dates autosave-field" style="width:110px;" id="deadline" autocomplete="off"
                               value="<?= format($ticket->dateToFinish)->date(); ?>"
                               name="dateToFinish" placeholder="<?= $tpl->__('language.dateformat') ?>"
                               <?php if (date('d/m/Y', strtotime($ticket->dateToFinish)) == '11/30/-0001') {
                                   $oldDate = 'No value';
                                 } else {
                                   $oldDate = date('d/m/Y', strtotime($ticket->dateToFinish));
                               } ?>
                               data-old-status="<?php echo $oldDate; ?>"
                               data-user="<?= htmlspecialchars($currentUserName) ?>"/>

                        <input type="time" class="timepicker tw-mr-sm autosave-after-lost-focus" style="width:120px;" id="dueTime" autocomplete="off"
                               value="<?= format($ticket->dateToFinish)->time24(); ?>"
                               name="timeToFinish"
                               <?php
                                $dt = new \DateTime($ticket->dateToFinish, new \DateTimeZone('UTC'));
                                if($timezone){
                                    $dt->setTimezone(new \DateTimeZone($timezone));
                                }
                                $formattedTime = $dt->format('H:i');
                                ?>
                               data-old-status="<?= $formattedTime ?>"
                               data-user="<?= htmlspecialchars($currentUserName) ?>"/>
                    </div>
                    <div style="padding-top:6px;">
                        <?php $tpl->dispatchTplEvent('afterDates', ['ticket' => $ticket]);
?>
                    </div>
                </div>

                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px] tags"><?php echo $tpl->__('label.tags'); ?></label>
                    <div class="">
                        <input class="autosave-after-lost-focus" type="text" value="<?php $tpl->e($ticket->tags); ?>" name="tags" id="tags" />
                    </div>
                </div>
                <br />

                <div class="form-group" id="descriptionEditor">
                    <textarea name="description" id="ticketDescription"
                              class="complexEditor"><?php echo $ticket->description !== null ? htmlentities($ticket->description) : ''; ?></textarea><br/>
                </div>
                <input type="hidden" name="acceptanceCriteria" value=""/>

            </div>
        </div>

        <?php if ($ticket->id) {?>
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

<div class="status-change-log" id="status-change-log" data-ticket-id="<?= $ticket->id ?>"></div>

<script>
jQuery(document).ready(function($) {
    const $statusChangeDiv = $('#status-change-log');

    function loadStatusHistory(ticketId) {
        $.ajax({
            url: '<?= BASE_URL ?>/tickets/ticketHistoryController/getStatusChanges',
            method: 'GET',
            data: { ticketId: ticketId },
            success: function(html) {
                $statusChangeDiv.html(html);
            },
            error: function() {
                $statusChangeDiv.html('<p style="color:red;">Greška pri učitavanju statusa.</p>');
            }
        });
    }
window.assignToMe = function() {
    const userId = <?php echo session('userdata.id'); ?>;
    const $editorSelect = $('#editorId');
    const currentEditorId = $editorSelect.val();
    
    if (currentEditorId == userId) {
        console.log('Already assigned to current user');
        return; 
    }
    
    const ticketId = $('#status-change-log').data('ticket-id');
    const user = $('#status-select').data('user') || 'Unknown User';
    
    const oldStatusText = $editorSelect.data('old-status');
    const newStatusText = '<?php echo addslashes($currentUserName); ?>';
    
    $editorSelect.val(userId).trigger('chosen:updated');
    
    $.ajax({
        url: '<?= BASE_URL ?>/tickets/ticketHistoryController/logStatusChange',
        method: 'POST',
        data: {
            ticketId: ticketId,
            oldStatus: currentEditorId,
            newStatus: userId,
            oldStatusText: oldStatusText,
            newStatusText: newStatusText,
            user: user,
            detailsAttributeId: 'editorId'
        },
        success: function(response) {
            console.log('Status change saved', response);
            loadStatusHistory(ticketId);
            $editorSelect.data('old-status', newStatusText);
        },
        error: function(xhr, status, error) {
            console.error('Error saving status:', error);
            console.error('Response:', xhr.responseText);
        }
    });
};

    const ticketIdOnLoad = $('#status-change-log').data('ticket-id');
    if (ticketIdOnLoad) {
        loadStatusHistory(ticketIdOnLoad);
    }
    
    window.initialTicketDescription = '';
    
    if (typeof tinymce !== 'undefined') {
        tinymce.on('AddEditor', function(e) {
            if (e.editor.id === 'ticketDescription') {
                e.editor.on('init', function() {
                    window.initialTicketDescription = e.editor.getContent();
                });
                
                e.editor.off('SaveContent');
                
                e.editor.on('SaveContent', function() {
                    const currentDescription = e.editor.getContent();
                    
                    if (window.initialTicketDescription !== currentDescription) {
                        const ticketId = $('#status-change-log').data('ticket-id');
                        const user = $('#status-select').data('user') || 'Unknown User';
                        
                        $.ajax({
                            url: '<?= BASE_URL ?>/tickets/ticketHistoryController/logStatusChange',
                            method: 'POST',
                            data: {
                                ticketId: ticketId,
                                oldStatus: '',
                                newStatus: '',
                                oldStatusText: window.initialTicketDescription,
                                newStatusText: currentDescription,
                                user: user,
                                detailsAttributeId: 'ticketDescription'
                            },
                            success: function(response) {
                                window.initialTicketDescription = currentDescription;
                                loadStatusHistory(ticketId);
                            },
                            error: function(xhr, status, error) {
                                console.error('Error logging description:', error);
                            }
                        });
                    }
                });
            }
        });
    }

    $('#status-select, #priority, #storypoints, #editorId, #deadline').on('change', function() {
        var changedElementId = $(this).attr('id');
        const ticketId = $('#status-change-log').data('ticket-id');
        const oldStatusKey = $(this).data('old-status');
        const newStatusKey = $(this).val();
        const oldStatusText = $(this).data('old-status');
        const newStatusText = $(this).find('option:selected').text() || $(this).val();
        const user = $(this).data('user') || 'Unknown User';

        $.ajax({
            url: '<?= BASE_URL ?>/tickets/ticketHistoryController/logStatusChange',
            method: 'POST',
            data: {
                ticketId: ticketId,
                oldStatus: oldStatusKey,
                newStatus: newStatusKey,
                oldStatusText: oldStatusText,
                newStatusText: newStatusText,
                user: user,
                detailsAttributeId: changedElementId
            },
            success: function(response) {
                console.log('Status change saved', response);
                loadStatusHistory(ticketId);
            },
            error: function(xhr, status, error) {
                console.error('Error saving status:', error);
                console.error('Response:', xhr.responseText);
            }
        });

        $(this).data('old-status', newStatusKey);
    });
    
    $('#dueTime').on('blur', function() {
        var changedElementId = $(this).attr('id');
        const ticketId = $('#status-change-log').data('ticket-id');
        const oldStatusKey = $(this).data('old-status');
        const newStatusKey = $(this).val();
        const oldStatusText = $(this).data('old-status');
        const newStatusText = $(this).val();
        const user = $(this).data('user') || 'Unknown User';
        console.log('Date: ', oldStatusText, newStatusText);

        $.ajax({
            url: '<?= BASE_URL ?>/tickets/ticketHistoryController/logStatusChange',
            method: 'POST',
            data: {
                ticketId: ticketId,
                oldStatus: oldStatusKey,
                newStatus: newStatusKey,
                oldStatusText: oldStatusText,
                newStatusText: newStatusText,
                user: user,
                detailsAttributeId: changedElementId
            },
            success: function(response) {
                console.log('Status change saved', response);
                loadStatusHistory(ticketId);
            },
            error: function(xhr, status, error) {
                console.error('Error saving status:', error);
                console.error('Response:', xhr.responseText);
            }
        });

        $(this).data('old-status', newStatusKey);
    });
});
</script>


        <div class="sticky-modal-footer">
            <div class="row">
                <div class="col-md-12" style="margin-top:15px;">
                    <input type="hidden" name="saveTicket" value="1" />
                    <input type="hidden" id="saveAndCloseButton" name="saveAndCloseTicket" value="0" />

                    <input type="submit" name="saveTicket" class="saveTicketBtn" value="<?php echo $tpl->__('buttons.save'); ?>"/>
                    <input type="submit" name="saveAndCloseTicket" class="btn btn-outline" onclick="jQuery('#saveAndCloseButton').val('1');" value="<?php echo $tpl->__('buttons.save_and_close'); ?>"/>
                </div>
            </div>
        </div>

        <?php if ($ticket->id) {?>
            <br />
            <hr />
            <?php $tpl->dispatchTplEvent('beforeSubtasks', ['ticketId' => $ticket->id]); ?>
            <h4 class="widgettitle title-light"><i class="fa-solid fa-sitemap"></i> <?php echo $tpl->__('subtitles.subtasks'); ?></h4>

            <div
                id="ticketSubtasks"
                hx-get="<?= BASE_URL ?>/tickets/subtasks/get?ticketId=<?= $ticket->id ?>"
                hx-trigger="load, subtasksUpdated from:body"
                hx-indicator=".subtaskIndicator"
            ></div>
            <div class="htmx-indicator subtaskIndicator">
                Loading Subtasks ...<br /><br />
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
                            </select><br/>
                        </div>
                    </div>

                    <!-- Project -->
                    <div class="form-group">
                        <label class="control-label"><?= $tpl->__('label.project') ?></label>
                        <select name="projectId" class="tw-w-full">
                            <?php foreach ($allAssignedprojects as $project) { ?>
                                <option value="<?= $project['id'] ?>"
                                    <?php
                                    if ($ticket->projectId == $project['id']) {
                                        echo 'selected';
                                    } elseif (session('currentProject') == $project['id']) {
                                        echo 'selected';
                                    }
                                ?>
                                ><?= $tpl->escape($project['name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Milestones -->
                    <div class="form-group">
                        <label class="control-label"><?php echo $tpl->__('label.milestone'); ?></label>
                        <div class="">
                            <div class="form-group">
                                <select  name="milestoneid"  class="span11" >
                                    <option value=""><?php echo $tpl->__('label.not_assigned_to_milestone'); ?></option>
                                    <?php foreach ($tpl->get('milestones') as $milestoneRow) {     ?>
                                        <?php echo "<option value='".$milestoneRow->id."'";

                                        if (($ticket->milestoneid == $milestoneRow->id)) {
                                            echo " selected='selected' ";
                                        }

                                        echo '>'.$tpl->escape($milestoneRow->headline).'</option>'; ?>

                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sprint -->
                    <div class="form-group">
                        <label class="control-label"><?php echo $tpl->__('label.sprint'); ?></label>
                        <div class="">

                            <select id="sprint-select" class="span11" name="sprint"
                                    data-placeholder="<?php echo $ticket->sprint ?>">
                                <option value=""><?php echo $tpl->__('label.backlog'); ?></option>
                                <?php
                                if ($tpl->get('sprints')) {
                                    foreach ($tpl->get('sprints') as $sprintRow) { ?>
                                        <option value="<?php echo $sprintRow->id; ?>"
                                            <?php if ($ticket->sprint == $sprintRow->id) {
                                                echo "selected='selected'";
                                            } ?>
                                        ><?php $tpl->e($sprintRow->name); ?></option>
                                    <?php }
                                    } ?>
                            </select>
                        </div>
                    </div>

                    <!-- Related -->
                    <div class="form-group">
                        <label class="control-label"><?php echo $tpl->__('label.related_to'); ?></label>
                        <div class="">
                            <div class="form-group">
                                <select  name="dependingTicketId"  class="span11" >
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
                                    }?>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="row marginBottom">
            <div class="col-md-12">
                <h5 class="accordionTitle" id="accordion_link_tickets-dates" style="padding-bottom:15px; font-size:var(--font-size-l)">
                    <a href="javascript:void(0)"
                       class="accordion-toggle"
                       id="accordion_toggle_tickets-dates"
                       onclick="leantime.snippets.accordionToggle('tickets-dates');">
                        <i class="fa fa-angle-down"></i>
                        <span class="fa fa-calendar"></span>
                        <?php echo $tpl->__('subtitles.schedule'); ?>
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-tickets-dates" style="padding-left:0">
                    <div class="form-group">
                        <label class=" control-label"><?php echo $tpl->__('label.working_date_from'); ?></label>
                        <div class="">
                            <input type="text" class="editFrom" style="width:100px;" name="editFrom" autocomplete="off"
                                   value="<?= format($ticket->editFrom)->date(); ?>" placeholder="<?= $tpl->__('language.dateformat') ?>"/>
                            <input type="time" class="timepicker" style="width:120px;" id="timeFrom" autocomplete="off"
                                   value="<?= format($ticket->editFrom)->time24(); ?>"
                                   name="timeFrom"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label"><?php echo $tpl->__('label.working_date_to'); ?></label>
                        <div class="">
                            <input type="text" class="editTo" style="width:100px;" name="editTo" autocomplete="off"
                                   value="<?= format($ticket->editTo)->date() ?>" placeholder="<?= $tpl->__('language.dateformat') ?>"/>
                            <input type="time" class="timepicker" style="width:120px;" id="timeTo" autocomplete="off"
                                   value="<?= format($ticket->editTo)->time24() ?>"
                                   name="timeTo"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label"><?php echo $tpl->__('label.planned_hours'); ?> / <?php echo $tpl->__('label.estimated_hours_remaining'); ?></label>
                        <div class="">
                            <input type="text" value="<?php $tpl->e($ticket->planHours); ?>" name="planHours" style="width:45px;"/>&nbsp;/&nbsp;
                            <input type="text" value="<?php $tpl->e($ticket->hourRemaining); ?>" name="hourRemaining" style="width:45px;"/>
                            <a href="javascript:void(0)" class="infoToolTip" data-placement="left" data-toggle="tooltip" data-tippy-content="<?php echo $tpl->__('tooltip.how_many_hours_remaining'); ?>">
                                &nbsp;<i class="fa fa-question-circle"></i>&nbsp;
                            </a>
                        </div>
                    </div>


                </div>

            </div>
        </div>

        <?php $tpl->dispatchTplEvent('beforeEndRightColumn', ['ticket' => $ticket]); ?>
    </div>
</div>
<script>

    jQuery(document).ready(function(){
        //Set accordion states
        //All accordions start open
        leantime.editorController.initComplexEditor();

    });

    leantime.editorController.initComplexEditor();

    Prism.highlightAll();

</script>

<script>
jQuery(document).ready(function($) {
    $('.autosave-field').on('change', function() {
        $('.saveTicketBtn').trigger('click');
    });

    $('.autosave-after-lost-focus').on('blur', function() {
        $('.saveTicketBtn').trigger('click');
    });
   $(document).on('keyup blur', 'div.tagsinput input.ui-autocomplete-input', function(e) {
    if (e.type === 'blur' || e.keyCode === 13 || e.keyCode === 188) {
        setTimeout(function() {
            $('.saveTicketBtn').trigger('click');
        }, 10);
    }
});

    document.addEventListener('click', function(e) {
        if (e.target.matches('.tagsinput .tag a')) {
            setTimeout(function() {
                $('.saveTicketBtn').trigger('click');
            }, 10);
        }
    }, true);
});
</script>

