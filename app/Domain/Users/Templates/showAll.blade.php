@php
    $roles = $tpl->get('roles');
@endphp

<x-globals::layout.page-header :icon="$tpl->getModulePicture()" headline="{{ __('headlines.users') }}" subtitle="{{ __('label.administration') }}" />

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="tw:flex tw:items-center tw:flex-wrap tw:gap-2 tw:mb-4">
            <x-globals::forms.button link="{{ BASE_URL }}/users/newUser" type="primary" class="userEditModal" icon="add">{{ __('buttons.add_user') }}</x-globals::forms.button>
        </div>

        <x-globals::elements.table id="allUsersTable">
            <x-slot:head>
                <colgroup>
                    <col class="con1">
                    <col class="con0">
                    <col class="con1">
                    <col class="con0">
                    <col class="con1">
                    <col class="con0">
                    <col class="con1">
                </colgroup>
                <tr>
                    <th class='head1'>{{ __('label.name') }}</th>
                    <th class='head0'>{{ __('label.email') }}</th>
                    <th class='head1'>{{ __('label.client') }}</th>
                    <th class='head1'>{{ __('label.role') }}</th>
                    <th class='head1'>{{ __('label.status') }}</th>
                    <th class='head1'>{{ __('headlines.twoFA') }}</th>
                    <th class='head0 no-sort'></th>
                </tr>
            </x-slot:head>
            @foreach($tpl->get('allUsers') as $row)
                <tr>
                    <td class="tw:px-2.5 tw:py-1.5">
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
                    <td><a href="{{ BASE_URL }}/users/delUser/{{ $row['id'] }}" class="delete"><x-globals::elements.icon name="delete" /> {{ __('links.delete') }}</a></td>
                </tr>
            @endforeach
        </x-globals::elements.table>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
        leantime.usersController.initUserTable();
        leantime.usersController._initModals();
        leantime.usersController.initUserEditModal();
    });
</script>
