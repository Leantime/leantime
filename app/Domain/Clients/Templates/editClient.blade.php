@extends($layout)

@section('content')

<?php
$values = $tpl->get('values');
?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5>Administration</h5>
        <h1>{{ __("EDIT_CLIENT") }}</h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">

        @displayNotification()

        <form action="" method="post" class="stdform">

            <div class="widget">
            <h4 class="widgettitle">{{ __("OVERVIEW") }}</h4>
            <div class="widgetcontent">

                <label for="name">{{ __("NAME") }}</label>
                <input type="text" name="name" id="name" value="<?php echo $values['name'] ?>" /><br />

                <label for="email">{{ __("EMAIL") }}</label>
                <input type="text" name="email" id="email" value="<?php echo $values['email'] ?>" /><br />

                <label for="internet">{{ __("URL") }}</label> <input
                    type="text" name="internet" id="internet"
                    value="<?php echo $values['internet'] ?>" /><br />

                <label for="street">{{ __("STREET") }}</label> <input
                    type="text" name="street" id="street"
                    value="<?php echo $values['street'] ?>" /><br />

                <label for="zip">{{ __("ZIP") }}</label> <input type="text"
                    name="zip" id="zip" value="<?php echo $values['zip'] ?>" /><br />

                <label for="city">{{ __("CITY") }}</label> <input type="text"
                    name="city" id="city" value="<?php echo $values['city'] ?>" /><br />

                <label for="state">{{ __("STATE") }}</label> <input
                    type="text" name="state" id="state"
                    value="<?php echo $values['state'] ?>" /><br />

                <label for="country">{{ __("COUNTRY") }}</label> <input
                    type="text" name="country" id="country"
                    value="<?php echo $values['country'] ?>" /><br />

                <label for="phone">{{ __("PHONE") }}</label> <input
                    type="text" name="phone" id="phone"
                    value="<?php echo $values['phone'] ?>" /><br />

                    <x-global::forms.button type="submit" name="save" id="save">
                        {{ __('SAVE') }}
                    </x-global::forms.button>
                </div>
            </div>

        </form>

    </div>
</div>
