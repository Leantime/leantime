@extends($layout)

@section('content')

<?php
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

        @displayNotification()


    </div>
</div>

<script type="text/javascript">

   jQuery(document).ready(function() {


    });

</script>
