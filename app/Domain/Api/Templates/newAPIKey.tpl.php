<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$status = $tpl->get('status');
$values = $tpl->get('values');
$projects = $tpl->get('relations');
$apiKeyValues = $tpl->get('apiKeyValues');
?>

<div style="min-width:700px;">

<h4 class="widgettitle title-light"><i class="fa fa-key"></i> <?php echo $tpl->__('headlines.new_api_key'); ?></h4>

<?php echo $tpl->displayNotification() ?>
    <?php if ($apiKeyValues !== false && isset($apiKeyValues['id'])) {?>
        <p>Your API Key was successfully created. Please copy the key below. This is your only chance to copy it.</p>
        <input type="text" id="apiKey" value="lt_<?=$apiKeyValues['user'] ?>_<?=$apiKeyValues['passwordClean']?>"  style="width:100%;"/>
        <button class="btn btn-primary" onclick="leantime.snippets.copyUrl('apiKey');"><?=$tpl->__('links.copy_key') ?></button>
    <?php } else { ?>
    <form action="<?=BASE_URL?>/api/newApiKey" method="post" class="stdform formModal" >

        <input type="hidden" name="save" value="1" />

        <div class="row" >
            <div class="col-md-6">

                <h4 class="widgettitle title-light"><?php echo $tpl->__('label.basic_information'); ?></h4>

                <label for="firstname"><?php echo $tpl->__('label.key_name'); ?></label><div class="clearfix"></div>
                    <input
                    type="text" name="firstname" id="firstname"
                    value="" /><br />


                <label for="role"><?php echo $tpl->__('label.role'); ?></label><div class="clearfix"></div>
                <select name="role" id="role">

                    <?php foreach ($tpl->get('roles') as $key => $role) { ?>
                        <option value="<?php  echo $key; ?>"
                            <?php if ($key == $values['role']) {
                                ?> selected="selected" <?php
                            } ?>>
                            <?=$tpl->__("label.roles." . $role) ?>
                        </option>
                    <?php } ?>

                </select> <br />

                <label for="status"><?php echo $tpl->__('label.status'); ?></label><div class="clearfix"></div>
                <select name="status" id="status">
                    <option value="a"
                        <?php if (strtolower($values['status']) == "a") {
                            ?> selected="selected" <?php
                        } ?>>
                        <?=$tpl->__("label.active") ?>
                    </option>

                    <option value=""
                        <?php if (strtolower($values['status']) == "") {
                            ?> selected="selected" <?php
                        } ?>>
                        <?=$tpl->__("label.deactivated") ?>
                    </option>

                </select>

                    <div class="clearfix"></div>

                <p class="stdformbutton">
                    <input type="submit" name="save" id="save" value="<?php echo $tpl->__('buttons.save'); ?>" class="button" />
                </p>

            </div>
            <div class="col-md-6">

                <h4 class="widgettitle title-light"><?php echo $tpl->__('label.project_access'); ?></h4>

                <div class="scrollableItemList">
                    <?php
                    $currentClient = '';
                    $i = 0;
                    $containerOpen = false;
                    foreach ($tpl->get('allProjects') as $row) {
                        if ($currentClient != $row['clientName']) {
                            if ($i > 0 && $containerOpen) {
                                echo"</div>";
                                $containerOpen = false;
                            }
                            echo "<h3 id='accordion_link_" . $i . "'>
                        <a href='#' onclick='accordionToggle(" . $i . ");' id='accordion_toggle_" . $i . "'><i class='fa fa-angle-down'></i> " . $tpl->escape($row['clientName']) . "</a>
                        </h3>
                        <div id='accordion_" . $i . "' class='simpleAccordionContainer'>";
                            $currentClient = $row['clientName'];
                            $containerOpen = true;
                        } ?>
                        <div class="item">
                            <input type="checkbox" name="projects[]" id='project_<?php echo $row['id'] ?>' value="<?php echo $row['id'] ?>"
                                <?php if (is_array($projects) === true && in_array($row['id'], $projects) === true) {
                                    echo "checked='checked';";
                                } ?>
                            /><label for="project_<?php echo $row['id'] ?>"><?php $tpl->e($row['name']); ?></label>
                            <div class="clearall"></div>
                        </div>
                                    <?php $i++; ?>
                    <?php } ?>

                </div>

            </div>
        </div>
    <?php } ?>
</form>
</div>
<script>
    jQuery(".noClickProp.dropdown-menu").on("click", function(e) {
        e.stopPropagation();
    });

    function accordionToggle(id) {
        let currentLink = jQuery("#accordion_toggle_"+id).find("i.fa");
        if (currentLink.hasClass("fa-angle-right")){
            currentLink.removeClass("fa-angle-right");
            currentLink.addClass("fa-angle-down");
            jQuery('#accordion_'+id).slideDown("fast");
        } else {
            currentLink.removeClass("fa-angle-down");
            currentLink.addClass("fa-angle-right");
            jQuery('#accordion_'+id).slideUp("fast");
        }
    }
</script>
