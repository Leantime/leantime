<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
    $providerFields = $tpl->get("providerFields");
    $provider = $tpl->get("provider");
    $leantimeFields = $tpl->get("leantimeFields");
    $numberOfFields = $tpl->get("maxFields");
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-plug"></span></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h1><?php echo $tpl->__("headlines.integrations"); ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $tpl->displayNotification(); ?>

        <h3>Map and Convert Fields Entity Here</h3>
        <?=$provider->name ?><br />

        <p>Please mape the fields from the CSV file to the leantime fields</p>

        <table style="width:300px;">
            <thead>
            <tr>
                <td>Source Field</td>
                <td>Leantime Field</td>
            </tr>
            </thead>
            <tbody>
                <?php foreach ($providerFields as $key => $entity) {?>
                    <tr>
                        <td><?=$entity ?> </td>
                        <td>
                            <select name="field_<?=md5($entity)?>">
                                <?php foreach ($leantimeFields as $key => $fields) {?>
                                    <option value="<?=$key ?>"
                                        <?php
                                        if ($entity == $fields['name']) {
                                            echo" selected='selected' ";
                                        }
                                        ?>
                                    ><?=$fields['name'] ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>

                <?php } ?>
            </tbody>

        </table>

            <input type="submit" value="Next" class="btn"/>
        </form>
    </div>
</div>

<script type="text/javascript">

   jQuery(document).ready(function() {


    });

</script>
