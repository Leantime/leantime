<x-global::content.modal.modal-buttons/>

<?php

$currentWiki = $tpl->get('wiki');
?>

<h4 class="widgettitle title-light"><i class="fa fa-book"></i> <?=$tpl->__('label.wiki') ?> <?php echo $tpl->escape($currentWiki->title) ?></h4>

@displayNotification()

<?php

$id = "";
if (isset($currentWiki->id)) {
    $id = $currentWiki->id;
}
?>

<x-global::content.modal.form action="{{ BASE_URL }}/wiki/wikiModal/{{ $id }}">

    <x-global::forms.text-input 
        type="text" 
        name="title" 
        id="wikiTitle" 
        value="{!! $tpl->escape($currentWiki->title) !!}" 
        placeholder="{!! $tpl->__('input.placeholders.wiki_title') !!}" 
        labelText="{!! $tpl->__('label.wiki_title') !!}" 
        variant="title" 
    />
    <br />

    <br />

    <div class="row">
        <div class="col-md-6">
            <x-global::forms.button type="submit" id="saveBtn">
                {{ __('buttons.save') }}
            </x-global::forms.button>
        </div>
        <div class="col-md-6 align-right padding-top-sm">
            <?php if (isset($currentWiki->id) && $currentWiki->id != '' && $login::userIsAtLeast($roles::$editor)) { ?>
                <a href="{{ BASE_URL }}/wiki/delWiki/<?php echo $currentWiki->id; ?>" class="delete formModal"><i class="fa fa-trash"></i> <?=$tpl->__('links.delete_wiki') ?></a>
            <?php } ?>
        </div>
    </div>

</x-global::content.modal.form>

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

