<?php

?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-connectdevelop"></span></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h1><?php echo $this->__("headlines.connector"); ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>
        <h3>Sync Leantime with your external applications</h3>
        <p>Available providers</p>
        <div class="row">
            <?php foreach($this->get("providers") as $provider) { ?>
                <div class="col-md-3">
                    <div class="profileBox">
                        <div class="commentImage">
                            <img src="<?=BASE_URL ?>/<?=$provider->image ?>"/>
                        </div>
                        <span class="userName">
                            <?=$provider->name ?>
                            <br /><small>Things you can sync: <?=implode(", ", $provider->entities); ?></small>
                            <br /><small>Available methods: <?=implode(", ", $provider->methods); ?></small>
                        </span>
                        <br />
                        <a class="btn btn-primary" href="<?=BASE_URL?>/connector/integration?provider=<?=$provider->id ?>">Create New Integration</a>

                        <div class="clearall"></div>
                    </div>
                </div>

            <?php } ?>
        </div>

    </div>

    <div class="maincontentinner">

        <h3>Existing Integrations</h3>


    </div>
</div>

<script type="text/javascript">

   jQuery(document).ready(function() {


    });

</script>
