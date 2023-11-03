<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
?>

<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-circle-nodes"></i></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h1>Integrations</h1>
            </div>
        </div>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $tpl->displayNotification(); ?>
        <h5 class="subtitle">Sync Leantime with your external applications</h5>
        <p>Available Integrations</p>


        <div class="row">
            <?php foreach ($tpl->get("providers") as $provider) { ?>
                <div class="col-md-3">
                    <div class="profileBox">
                        <div class="commentImage">
                            <img src="<?=BASE_URL ?>/<?=$provider->image ?>"/>
                        </div>
                        <span class="userName">
                            <?=$provider->name ?>
                            <br /><small>Things you can sync: <?php //implode(", ", $provider->entities);?></small>
                            <br /><small>Available methods: <?=implode(", ", $provider->methods); ?></small>
                        </span>
                        <br />

                        <?php if(isset($provider->button)) { ?>
                            <a href="<?=$provider->button["url"] ?>" class="btn btn-primary"><?=$provider->button["text"] ?></a>
                       <?php }else{ ?>
                            <a class="btn btn-primary" href="<?=BASE_URL?>/connector/integration?provider=<?=$provider->id ?>">Create New Integration</a>
                        <?php } ?>

                        <div class="clearall"></div>
                    </div>
                </div>

            <?php } ?>
        </div>

    </div>

    <div class="maincontentinner">

        <h5 class="subtitle">Existing Integrations</h5>


    </div>
</div>

<script type="text/javascript">

   jQuery(document).ready(function() {


    });

</script>
