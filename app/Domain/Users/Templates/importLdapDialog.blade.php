@extends($layout)
@section('content')

<div class="showDialogOnLoad" style="display:none;">

    <h4 class="widgettitle title-light"><i class="fa fa-arrow-circle-o-right"></i>
        {!! __('headlines.import_ldap_users') !!}
    </h4>

    {!! $tpl->displayNotification() !!}

    @if ($confirmUsers)
        <form class="importModal userImportModal" method="post" action="{{ BASE_URL }}/users/import">
            @foreach ($allLdapUsers as $user)
                <input type="checkbox" value="{{ $user['user'] }}" id="{{ $user['user'] }}" name="users[]" checked="checked"/>
                <label for="{{ $user['user'] }}" style="display:inline;">{{ $user['user'] }} - {{ $user['firstname'] }},  {{ $user['lastname'] }}<br />
            @endforeach
            <br />
            <input type="hidden" name="importSubmit" value="1"/>
            <input type="submit" value="{{ __('buttons.import') }}" />
        </form>

    @else
        <form class="importModal userImportModal" method="post" action="{{ BASE_URL }}/users/import">
            <label>{!! __('label.please_enter_password') !!} </label>
            <input type="password" name="password" />
            <input type="hidden" name="pwSubmit" value="1"/>
            <input type="submit" value="{{ __('buttons.find_users') }}" />
        </form>

    @endif

</div>

@endsection
