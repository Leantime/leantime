@extends($layout)

@section('content')

<?php
$client = $tpl->get('client');
?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5>{{ __("label.administration") }}</h5>
        <h1><?php echo sprintf($tpl->__('headline.delete_client'), $client['name']); ?></h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">

        @displayNotification()

        <h4 class="widget widgettitle">{{ __("subtitles.delete") }}</h4>
        <div class="widgetcontent">

            <form method="post">

                <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>

                <p>{{ __("text.confirm_client_deletion") }}<br /></p>
                <x-global::forms.button type="submit" name="del" class="button">
                    {{ __('buttons.yes_delete') }}
                </x-global::forms.button>

                <x-global::forms.button tag="a" href="/clients/showClient/{{ $client['id'] }}" class="btn btn-primary">
                    {{ __('buttons.back') }}
                </x-global::forms.button>


                <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>

            </form>
        </div>

    </div>
</div>
