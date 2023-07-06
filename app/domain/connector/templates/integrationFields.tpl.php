<?php
    $providerFields = $this->get("providerFields");
    $provider = $this->get("provider");
    $leantimeFields = $this->get("leantimeFields");
    $numberOfFields = $this->get("maxFields");
    $flags = $this->get("flags");
    $urlAppend = '';
    if(isset($integrationId) && is_numeric($integrationId)) {
        $urlAppend = "&integrationId=".$integrationId;
    }
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-plug"></span></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h1><?php echo $this->__("headlines.integrations"); ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

        <h3>Map and Convert Fields Entity Here</h3>
        <?=$provider->name ?><br />

        <p>Please map the fields from the CSV file to the leantime fields</p>

        <?php foreach($flags as $flag){?>
            <p style="color: red;"><?= $flag ?></p>
        <?php } ?>


        <form method="post" action="<?=BASE_URL?>/connector/integration/?provider=<?=$provider->id?>&step=import<?=$urlAppend ?>">
            <table style="width:300px;">
                <thead>
                <tr>
                    <td>Source Field</td>
                    <td>Leantime Field</td>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($providerFields as $key => $entity){?>
                        <tr>
                            <td><?=$entity ?> </td>
                            <td>
                                <select name="field_<?=md5($entity)?>">
                                    <?php foreach($leantimeFields as $key2 => $fields) {?>
                                        <option value="<?=$entity ?>|<?=$key2 ?>"
                                            <?php
                                            if($entity == $fields['name']) echo" selected='selected' ";
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
