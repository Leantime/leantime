<?php

?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-plug"></span></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h1><?php echo $this->__("headlines.plugins"); ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

        <?php if(count($this->get("newPlugins")) > 0) {?>
            <div class="row">
                <div class="col-lg-12">
                    <h5 class="subtitle">
                        New Plugins found
                    </h5>
                    <ul class="sortableTicketList" >
                    <?php foreach($this->get("newPlugins") as $newplugin){?>
                        <li>
                            <div class="ticketBox fixed">
                                <div class="row">

                                    <div class="col-md-4">
                                        <strong><?=$newplugin->name ?><br /></strong>
                                    </div>
                                    <div class="col-md-4">
                                        <?=$newplugin->description ?><br />
                                        Version <?=$newplugin->version ?>
                                        <?php if(is_array($newplugin->authors) && count($newplugin->authors) >0){?>
                                            | By <a href="mailto:<?=$newplugin->authors[0]["email"] ?>"><?=$newplugin->authors[0]["name"] ?></a>
                                        <?php } ?>
                                       | <a href="<?=$newplugin->homepage ?>">Visit Site </a>
                                    </div>
                                    <div class="col-md-4" style="padding-top:5px;">
                                        <a href="<?=BASE_URL ?>/plugins/show?install=<?=$newplugin->foldername ?>" class="btn btn-default pull-right"><?=$this->__('buttons.install') ?></a>

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
                    Installed Plugins
                </h5>
                <ul class="sortableTicketList">
                    <?php foreach($this->get("installedPlugins") as $installedPlugins){?>
                        <li>
                            <div class="ticketBox fixed">
                                <div class="row">

                                    <div class="col-md-4">
                                        <strong><?=$installedPlugins->name ?><br /></strong>
                                        <?php if($installedPlugins->enabled== false){?>
                                            <a href="<?=BASE_URL ?>/plugins/show?enable=<?=$installedPlugins->id ?>" class=""><i class="fa-solid fa-plug-circle-check"></i> <?=$this->__('buttons.enable') ?></a> |
                                            <a href="<?=BASE_URL ?>/plugins/show?remove=<?=$installedPlugins->id ?>" class="delete"><i class="fa fa-trash"></i> <?=$this->__('buttons.remove') ?></a>
                                        <?php }else{ ?>
                                            <a href="<?=BASE_URL ?>/plugins/show?disable=<?=$installedPlugins->id ?>" class="delete"><i class="fa-solid fa-plug-circle-xmark"></i> <?=$this->__('buttons.disable') ?></a>
                                        <?php } ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?=$installedPlugins->description ?><br />
                                        Version <?=$installedPlugins->version ?>
                                        <?php if(is_array($installedPlugins->authors) && count($installedPlugins->authors) >0){?>
                                            | By <a href="mailto:<?=$installedPlugins->authors[0]->email ?>"><?=$installedPlugins->authors[0]->name ?></a>
                                        <?php } ?>
                                        | <a href="<?=$installedPlugins->homepage ?>">Visit Site </a>
                                    </div>
                                    <div class="col-md-4" style="padding-top:5px;">

                                    </div>

                                </div>
                            </div>
                        </li>
                    <?php } ?>

                </ul>


            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

   jQuery(document).ready(function() {


    });

</script>
