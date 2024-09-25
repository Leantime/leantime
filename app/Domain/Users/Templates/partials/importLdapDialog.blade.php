@extends($layout)

@section('content')

<?php
?>

<div class="showDialogOnLoad" style="display:none;">

    <h4 class="widgettitle title-light"><i class="fa fa-arrow-circle-o-right"></i>
        {{ __("headlines.import_ldap_users") }}
    </h4>

    @displayNotification()

    <?php if ($tpl->get("confirmUsers")) { ?>
        <form class="importModal userImportModal" method="post" action="{{ BASE_URL }}/users/import">
            <?php foreach ($tpl->get("allLdapUsers") as $user) { ?>
                <x-global::forms.checkbox
                    name="users[]"
                    :id="$user['user']"
                    :value="$user['user']"
                    checked
                    :labelText="$user['user'].' - '.$user['firstname'].', '.$user['lastname']"
                    labelPosition="right"
                />
                    <br />
            <?php } ?>
            <br />
            <input type="hidden" name="importSubmit" value="1"/>
            <x-global::forms.button type="submit">
                {{ __('buttons.import') }}
            </x-global::forms.button>
        </form>

    <?php } else { ?>
        <form class="importModal userImportModal" method="post" action="{{ BASE_URL }}/users/import">
            <label><?=$tpl->__("label.please_enter_password") ?> </label>
            <input type="password" name="password" />
            <input type="hidden" name="pwSubmit" value="1"/>
            <x-global::forms.button type="submit">
                {{ __('buttons.find_users') }}
            </x-global::forms.button>
        </form>

    <?php } ?>

</div>
