<?php
  $currentWiki = $this->get('wiki');
?>

<h4 class="widgettitle title-light"><i class="fa fa-book"></i> <?=$this->__('label.wiki') ?> <?php echo $currentWiki->title?></h4>

<?php echo $this->displayNotification();

$id = "";
if(isset($currentWiki->id)) {$id = $currentWiki->id;
}
?>

<form class="formModal" method="post" action="<?=BASE_URL ?>/wiki/wikiModal/<?php echo $id;?>">

    <label><?=$this->__('label.wiki_title') ?></label>
    <input type="text" name="title" value="<?php echo $currentWiki->title?>" placeholder="<?=$this->__('input.placeholders.wiki_title') ?>"/><br />

    <br />

    <div class="row">
        <div class="col-md-6">
            <input type="submit" value="<?=$this->__('buttons.save') ?>"/>
        </div>
        <div class="col-md-6 align-right padding-top-sm">
            <?php if (isset($currentWiki->id) && $currentWiki->id != '' && $login::userIsAtLeast($roles::$editor)) { ?>
                <a href="<?=BASE_URL ?>/wiki/delWiki/<?php echo $currentWiki->id; ?>" class="delete formModal"><i class="fa fa-trash"></i> <?=$this->__('links.delete_wiki') ?></a>
            <?php } ?>
        </div>
    </div>

</form>

