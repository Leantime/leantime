@php
    $roles = $tpl->get('roles');
@endphp

<div class="pageheader">
    <div class="pageicon"><span class="fa {{ $tpl->getModulePicture() }}"></span></div>
    <div class="pagetitle">
        <h5>{{ __('label.administration') }}</h5>
        <h1>{{ __('headlines.users') }}</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="tw:grid tw:grid-cols-2 tw:gap-6">
            <div>
                <a href="{{ BASE_URL }}/users/newUser" class="btn btn-primary userEditModal"><i class='fa fa-plus'></i> {{ __('buttons.add_user') }} </a>
            </div>
            <div class="tw:text-right">
            </div>
        </div>

        <table class="table table-bordered" id="allUsersTable">
            <colgroup>
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
            </colgroup>
            <thead>
                <tr>
                    <th class='head1'>{{ __('label.name') }}</th>
                    <th class='head0'>{{ __('label.email') }}</th>
                    <th class='head1'>{{ __('label.client') }}</th>
                    <th class='head1'>{{ __('label.role') }}</th>
                    <th class='head1'>{{ __('label.status') }}</th>
                    <th class='head1'>{{ __('headlines.twoFA') }}</th>
                    <th class='head0 no-sort'></th>
                </tr>
            </thead>
            <tbody>
            @foreach($tpl->get('allUsers') as $row)
                <tr>
                    <td style="padding:6px 10px;">
                         <a href="{{ BASE_URL }}/users/editUser/{{ $row['id'] }}">{{ sprintf(__('text.full_name'), e($row['firstname']), e($row['lastname'])) }}</a>
                    </td>
                    <td><a href="{{ BASE_URL }}/users/editUser/{{ $row['id'] }}">{{ e($row['username']) }}</a></td>
                    <td>{{ e($row['clientName']) }}</td>
                    <td>{{ __('label.roles.' . $roles[$row['role']]) }}</td>
                    <td>
                        @if(strtolower($row['status']) == 'a')
                            {{ __('label.active') }}
                        @elseif(strtolower($row['status']) == 'i')
                            {{ __('label.invited') }}
                        @else
                            {{ __('label.deactivated') }}
                        @endif
                    </td>
                    <td>
                        @if($row['twoFAEnabled'])
                            {{ __('label.yes') }}
                        @else
                            {{ __('label.no') }}
                        @endif
                    </td>
                    <td><a href="{{ BASE_URL }}/users/delUser/{{ $row['id'] }}" class="delete"><i class="fa fa-trash"></i> {{ __('links.delete') }}</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
        leantime.usersController.initUserTable();
        leantime.usersController._initModals();
        leantime.usersController.initUserEditModal();
    });
</script>
