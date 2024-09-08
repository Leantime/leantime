<x-global::content.modal.modal-buttons/>

<?php
$ticket = $tpl->get("ticket");
?>

<?php if ($ticket->type == "milestone") {?>
    <h4 class="widgettitle title-light"><?=$tpl->__("headline.move_milestone"); ?> </h4>
<?php } else { ?>
    <h4 class="widgettitle title-light"><?=$tpl->__("headline.move_todo"); ?> </h4>
<?php } ?>


    <x-global::content.modal.form action="{{ BASE_URL }}/tickets/moveTicket/<?=$ticket->id ?>">
        <h3>#<?=$ticket->id ?> - <?=$tpl->escape($ticket->headline); ?></h3> <br />
        <p>
            <?php if ($ticket->type == "milestone") {?>
                {{ __("text.moving_milestones") }}
            <?php } else { ?>
                {{ __("text.moving") }}
            <?php } ?>

            <br /><br />
        </p>

        <select id="projectSelector" name="projectId">
        <?php
        $i = 0;
        $lastClient = '';
        foreach ($tpl->get('projects') as $projectRow) {
            if ($lastClient != $projectRow['clientName']) {
                $lastClient = $projectRow['clientName'];
                if ($i > 1) {
                    echo"</optgroup>";
                }
                echo "<optgroup label='" . $tpl->escape($projectRow['clientName']) . "'> ";
            }
            echo "<option value='" . $projectRow["id"] . "'>" . $tpl->escape($projectRow["name"]) . "</option>";
            $i++;
        }
        ?>
        </select><br /><br /><br /><br />
        <br />
        <input type="submit" value="{{ __("buttons.move") }}" name="move" class="button" />
        <a class="pull-right" href="javascript:void(0);" onclick="jQuery.nmTop().close();">{{ __("buttons.back") }}</a>
        <div class="clearall"></div>
        <br />
    </x-global::content.modal.form>


<script>
    <?php if (isset($_GET['closeModal'])) { ?>
        jQuery.nmTop().close();
    <?php } ?>

    jQuery(document).ready(function(){
        jQuery("#projectSelector").chosen();
    });
</script>



