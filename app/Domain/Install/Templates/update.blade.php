@extends($layout)

@section('content')

<div class="pageheader">
    <div class="pagetitle">
        <h1><?php echo $tpl->language->__('headlines.update_database'); ?></h1>
    </div>
</div>
<?php echo $tpl->displayInlineNotification(); ?>
<div class="regcontent"  id="login">
    <p><?php echo $tpl->language->__("text.new_db_version"); ?></p><br />
    <form action="{{ BASE_URL }}/install/update" method="post" class="registrationForm">
        <input type="hidden" name="updateDB" value="1" />
        <p>
            <x-global::forms.button type="submit" name="updateAction" onClick="this.form.submit(); this.disabled=true; this.value='Updating…';">
                {{ __('buttons.update_now') }}
            </x-global::forms.button>
                    </p>
    </form>
</div>

@endsection
