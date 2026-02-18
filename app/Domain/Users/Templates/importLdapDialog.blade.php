
<div class="showDialogOnLoad" style="display:none;">

    <h4 class="widgettitle title-light"><i class="fa fa-arrow-circle-o-right"></i>
        {{ __('headlines.import_ldap_users') }}
    </h4>

    {!! $tpl->displayNotification() !!}

    @if($tpl->get('confirmUsers'))
        <form class="importModal userImportModal" method="post" action="{{ BASE_URL }}/users/import">
            @foreach($tpl->get('allLdapUsers') as $user)
                <input type="checkbox" value="{{ e($user['user']) }}" id="{{ e($user['user']) }}" name="users[]" checked="checked"/>
                <label for="{{ e($user['user']) }}" style="display:inline;">{{ e($user['user']) }} - {{ e($user['firstname']) }}, {{ e($user['lastname']) }}<br />
            @endforeach
            <br />
            <input type="hidden" name="importSubmit" value="1"/>
            <x-global::button submit type="primary">{{ __('buttons.import') }}</x-global::button>
        </form>
    @else
        <form class="importModal userImportModal" method="post" action="{{ BASE_URL }}/users/import">
            <label>{{ __('label.please_enter_password') }} </label>
            <x-global::forms.input type="password" name="password" />
            <input type="hidden" name="pwSubmit" value="1"/>
            <x-global::button submit type="primary">{{ __('buttons.find_users') }}</x-global::button>
        </form>
    @endif

</div>
