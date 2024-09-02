@extends($layout)

@section('content')

<?php
$provider = $tpl->get("provider")
?>

<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-circle-nodes"></i></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h1><?php echo $tpl->__("headlines.integrations"); ?> // <?=$provider->name ?> </h1>
            </div>
        </div>
    </div>
</div>



<div class="maincontent">
    <div class="maincontentinner">
        <?php $tpl->displaySubmodule('connector-importProgress') ?>
    </div>

    <div class="maincontentinner center">

        <?php echo $tpl->displayNotification(); ?>

        <img width="200" src="<?=BASE_URL?>/<?=$provider->image?>" />
        <h5 class="subtitle">New Integration</h5>

        <?=$provider->name ?><br />
        <?=$provider->description ?><br /><br />

        <a class="btn btn-primary" href="<?=BASE_URL?>/connector/integration?provider=<?=$provider->id?>&step=connect">Click Here to Connect</a>

    </div>
</div>

<script type="text/javascript">

   jQuery(document).ready(function() {


    });

</script>
