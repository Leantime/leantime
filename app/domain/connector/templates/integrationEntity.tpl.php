<?php
    $providerEntities = $this->get("providerEntities");
    $provider = $this->get("provider");
    $leantimeEntities = $this->get("leantimeEntities");
    $integrationId = $this->get("integrationId");
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

        <h3>Align Systems Here</h3>
        <?=$provider->name ?><br />

        <p>What entities to you want to map</p>

        <form method="post" action="<?=BASE_URL?>/connector/integration/?provider=<?=$provider->id?>&step=fields<?=$urlAppend ?>">
            Leantime
            <select name="leantimeEntities">
                <?php foreach($leantimeEntities as $key => $entity){?>
                    <option value="<?=$key ?>"><?=$entity['name'] ?></option>
                <?php } ?>
            </select>

            <?=$provider->name ?>
            <select name="providerEntities">
                <?php foreach($providerEntities as $key => $entity){?>
                    <option value="<?=$key?>"><?=$entity['name'] ?></option>
                <?php } ?>
            </select>

            <input type="submit" value="Next" class="btn"/>
        </form>
    </div>
</div>

<script type="text/javascript">

   jQuery(document).ready(function() {


    });

</script>
