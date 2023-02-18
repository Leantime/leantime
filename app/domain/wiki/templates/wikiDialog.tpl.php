<?php
  $currentWiki = $this->get('wiki');
?>

<h4 class="widgettitle title-light"><i class="fa fa-book"></i> <?=$this->__('label.wiki') ?> <?php echo $this->escape($currentWiki->title) ?></h4>

<?php echo $this->displayNotification();

$id = "";
if (isset($currentWiki->id)) {
    $id = $currentWiki->id;
}
?>

<form class="formModal" method="post" action="<?=BASE_URL ?>/wiki/wikiModal/<?php echo $id;?>">

    <label><?=$this->__('label.wiki_title') ?></label>
    <input type="text" name="title" id="wikiTitle" value="<?php echo $this->escape($currentWiki->title) ?>" placeholder="<?=$this->__('input.placeholders.wiki_title') ?>"/><br />

    <br />

    <div class="row">
        <div class="col-md-6">
            <input type="submit" value="<?=$this->__('buttons.save') ?>" id="saveBtn"/>
        </div>
        <div class="col-md-6 align-right padding-top-sm">
            <?php if (isset($currentWiki->id) && $currentWiki->id != '' && $login::userIsAtLeast($roles::$editor)) { ?>
                <a href="<?=BASE_URL ?>/wiki/delWiki/<?php echo $currentWiki->id; ?>" class="delete formModal"><i class="fa fa-trash"></i> <?=$this->__('links.delete_wiki') ?></a>
            <?php } ?>
        </div>
    </div>

</form>

<script>
    jQuery(document).ready(function(){

        <?php if (isset($_GET['closeModal'])) { ?>
            jQuery.nmTop().close();
        <?php } ?>

       if(jQuery("#wikiTitle").val().length >= 2) {
           jQuery("#saveBtn").removeAttr("disabled");
       }else{
           jQuery("#saveBtn").attr("disabled", "disabled");
       }

        jQuery("#wikiTitle").keypress(function(){

            if(jQuery("#wikiTitle").val().length >= 2) {
                jQuery("#saveBtn").removeAttr("disabled");
            }else{
                jQuery("#saveBtn").attr("disabled", "disabled");
            }
        })
    });
</script>

