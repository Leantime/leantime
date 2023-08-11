<?php
foreach ($__data as $var => $val) $$var = $val; // necessary for blade refactor
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-plug"></span></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h1><?php echo $tpl->__("headlines.plugins"); ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $tpl->displayNotification(); ?>

        <?php if (count($tpl->get("newPlugins")) > 0) {?>
            <div class="row">
                <div class="col-lg-12">
                    <h5 class="subtitle">

                        <?=$tpl->__("text.new_plugins")?>
                    </h5>
                    <ul class="sortableTicketList" >
                    <?php foreach ($tpl->get("newPlugins") as $newplugin) {?>
                        <li>
                            <div class="ticketBox fixed">
                                <div class="row">

                                    <div class="col-md-4">
                                        <strong><?=$newplugin->name ?><br /></strong>
                                    </div>
                                    <div class="col-md-4">
                                        <?=$newplugin->description ?><br />
                                        <?=$tpl->__("text.version")?> <?=$newplugin->version ?>
                                        <?php if (is_array($newplugin->authors) && count($newplugin->authors) > 0) {?>
                                            | <?=$tpl->__("text.by")?> <a href="mailto:<?=$newplugin->authors[0]["email"] ?>"><?=$newplugin->authors[0]["name"] ?></a>
                                        <?php } ?>
                                       | <a href="<?=$newplugin->homepage ?>"> <?=$tpl->__("text.visit_site")?> </a>
                                    </div>
                                    <div class="col-md-4" style="padding-top:5px;">
                                        <a href="<?=BASE_URL ?>/plugins/show?install=<?=$newplugin->foldername ?>" class="btn btn-default pull-right"><?=$tpl->__('buttons.install') ?></a>

                                    </div>

                                </div>
                            </div>
                        </li>
                    <?php } ?>
                    </ul>
                </div>
            </div><br />
        <?php } ?>
        <div class="row">
            <div class="col-lg-12">
                <h5 class="subtitle">
                    <?=$tpl->__("text.installed_plugins")?>
                </h5>
                    <div class="row sortableTicketList">
                    <?php foreach ($tpl->get("installedPlugins") as $installedPlugins) {?>
                        <div class="col-md-4">
                            <div class="ticketBox fixed">
                                <div class="row">
                                    <div class="col-md-12" style="max-height:150px; overflow:hidden;margin-bottom:15px; text-align:center;">

                                            <img src="<?=$installedPlugins->getPluginImageData()?>" style="max-height:350px"/>

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="subtitle"><?=$installedPlugins->name ?><br /></h5>
                                    </div>
                                </div>
                                <div class="row" style="margin-bottom:15px;">

                                    <div class="col-md-4">

                                        <?=$tpl->__("text.version")?> <?=$installedPlugins->version ?>

                                    </div>
                                    <div class="col-md-8">
                                        <?=$installedPlugins->description ?><br />
                                        <?php if (is_array($installedPlugins->authors) && count($installedPlugins->authors) > 0) { ?>
                                            <?=$tpl->__("text.by")?> <a href="mailto:<?=$installedPlugins->authors[0]->email ?>"><?=$installedPlugins->authors[0]->name ?></a>
                                        <?php } ?>
                                        | <a href="<?=$installedPlugins->homepage ?>" target="_blank"> <?=$tpl->__("text.visit_site")?> </a><br />
                                    </div>
                                </div>
                                <div class="row" style="border-top:1px solid var(--main-border-color);">
                                    <div class="col-md-8" style="padding-top:10px;">
                                        <?php if ($installedPlugins->enabled == false) {?>
                                            <a href="<?=BASE_URL ?>/plugins/show?enable=<?=$installedPlugins->id ?>" class=""><i class="fa-solid fa-plug-circle-check"></i> <?=$tpl->__('buttons.enable') ?></a> |
                                            <a href="<?=BASE_URL ?>/plugins/show?remove=<?=$installedPlugins->id ?>" class="delete"><i class="fa fa-trash"></i> <?=$tpl->__('buttons.remove') ?></a>
                                        <?php } else { ?>
                                            <a href="<?=BASE_URL ?>/plugins/show?disable=<?=$installedPlugins->id ?>" class="delete"><i class="fa-solid fa-plug-circle-xmark"></i> <?=$tpl->__('buttons.disable') ?></a>
                                        <?php } ?>
                                    </div>
                                    <div class="col-md-4" style="padding-top:10px; text-align:right;">
                                        <?php
                                        if (file_exists(APP_ROOT . '/app/plugins/' . $installedPlugins->foldername . '/controllers/class.settings.php')) {?>
                                        <a href="<?=BASE_URL ?>/<?=$installedPlugins->foldername?>/settings"><i class="fa fa-cog"></i> Settings</a>
                                        <?php } ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($tpl->get("installedPlugins") === false || count($tpl->get("installedPlugins")) == 0) {?>
                        <?=$tpl->__("text.no_plugins_installed") ?>
                    <?php } ?>




            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

   jQuery(document).ready(function() {


    });

</script>
