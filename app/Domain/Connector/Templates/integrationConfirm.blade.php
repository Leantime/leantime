<?php
$providerFields = $tpl->get("providerFields");
$provider = $tpl->get("provider");
$leantimeFields = $tpl->get("leantimeFields");
$numberOfFields = $tpl->get("maxFields");
$urlAppend = '';
if (isset($integrationId) && is_numeric($integrationId)) {
    $urlAppend = "&integrationId=" . $integrationId;
}
?>

<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-circle-nodes"></i></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h1>{{ __("headlines.connector") }} // <?=$provider->name ?></h1>
            </div>
        </div>
    </div>
</div>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">
        @include("connector::includes.importProgress")
    </div>
    <div class="maincontentinner">
        <?php
        echo"<div class='center'>";
        echo"<div  style='width:30%' class='svgContainer'>";
        echo file_get_contents(ROOT . "/dist/images/svg/undraw_party_re_nmwj.svg");
        echo"</div>";
        echo"<br />";

        echo "<h3>Integration Success</h3>";
        echo "<p>Your data was synced successfully.</p>";
        echo "<br />";
        echo "<a class='btn btn-default' href='" . BASE_URL . "/connector/show'>Go back to integrations</a>";


        echo"</div>";
        ?>


    </div>
</div>

