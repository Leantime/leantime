<?php

$currentUrlPath = BASE_URL . "/" . str_replace(".", "/", \Leantime\Core\Frontcontroller::getCurrentRoute());

$clients = $tpl->get('clients');
$currentClient = $tpl->get("currentClient");
$currentClientName = $tpl->get("currentClientName");

?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon">
        <span class="fa fa-fw fa-thumb-tack"></span>
    </div>
    <div class="pagetitle">


        <h1><?php echo $tpl->__("headlines.my_portfolio"); ?>

            <?php if (count($clients) > 0) {?>
                //
                <span class="dropdown dropdownWrapper">
                <a href="javascript:void(0)" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                    <?php
                    if ($currentClientName != '') {
                        $tpl->e($currentClientName);
                    } else {
                        echo $tpl->__("headline.all_clients");
                    }
                    ?>
                    <i class="fa fa-caret-down"></i>
                </a>

                <ul class="dropdown-menu">
                    <li><a href="<?=$currentUrlPath ?>"><?=$tpl->__("headline.all_clients"); ?></a></li>
                    <?php foreach ($clients as $key => $value) {
                        echo "<li><a href='" . $currentUrlPath . "?client=" . $key . "'>" . $tpl->escape($value['name']) . "</a></li>";
                    }
                    ?>
                </ul>
            </span>
            <?php } ?>

        </h1>

    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>
