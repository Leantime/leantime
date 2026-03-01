
<div class="showDialogOnLoad" style="display:none;">

    <h4 class="widgettitle title-light"><x-global::elements.icon name="arrow_circle_right" />
        {{ __('headlines.import_ldap_users') }}
    </h4>

    {!! $tpl->displayNotification() !!}

    @if($tpl->get('confirmUsers'))
        <form class="importModal userImportModal" method="post" action="{{ BASE_URL }}/users/import">
            @foreach($tpl->get('allLdapUsers') as $user)
                <x-globals::forms.checkbox value="{{ e($user['user']) }}" id="{{ e($user['user']) }}" name="users[]" :checked="true" />
                <label for="{{ e($user['user']) }}" style="display:inline;">{{ e($user['user']) }} - {{ e($user['firstname']) }}, {{ e($user['lastname']) }}<br />
            @endforeach
            <br />
            <input type="hidden" name="importSubmit" value="1"/>
            <x-globals::forms.button submit type="primary">{{ __('buttons.import') }}</x-globals::forms.button>
        </form>
    @else
        <form class="importModal userImportModal" method="post" action="{{ BASE_URL }}/users/import">
            <label>{{ __('label.please_enter_password') }} </label>
            <x-globals::forms.input type="password" name="password" />
            <input type="hidden" name="pwSubmit" value="1"/>
            <x-globals::forms.button submit type="primary">{{ __('buttons.find_users') }}</x-globals::forms.button>
        </form>
    @endif

</div>
