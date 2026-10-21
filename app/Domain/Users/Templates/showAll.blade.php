@extends($layout)
@section('content')

<x-global::layout.page-header icon="fa {{ $tpl->getModulePicture() }}" :eyebrow="__('label.administration')">
    {!! __('headlines.users') !!}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

        <div class="row">
            <div class="col-md-6">
                @can('users.create')
                <x-global::forms.button tag="a" link="{{ BASE_URL }}/users/newUser" contentRole="primary" class="userEditModal"><i class='fa fa-plus'></i> {!! __('buttons.add_user') !!} </x-global::forms.button>
                @endcan
            </div>
            <div class="col-md-6 align-right">

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
                    <th class='head1'>{!! __('label.name') !!}</th>
                    <th class='head0'>{!! __('label.email') !!}</th>
                    <th class='head1'>{!! __('label.client') !!}</th>
                    <th class='head1'>{!! __('label.role') !!}</th>
                    <th class='head1'>{!! __('label.status') !!}</th>
                    <th class='head1'>{!! __('headlines.twoFA') !!}</th>
                    <th class='head0 no-sort'></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($allUsers as $row)
                    <tr>
                        <td style="padding:6px 10px;">
                             <a href="{{ BASE_URL }}/users/editUser/{{ $row['id'] }}">{!! sprintf(__('text.full_name'), e($row['firstname']), e($row['lastname'])) !!}</a>
                        </td>
                        <td><a href="{{ BASE_URL }}/users/editUser/{{ $row['id'] }}">{{ $row['username'] }}</a></td>
                        <td>{{ $row['clientName'] }}</td>
                        <td>{{ \Leantime\Domain\Auth\Models\Roles::label($row['role']) }}</td>
                        <td>@if (strtolower($row['status']) == 'a')
                            {!! __('label.active') !!}
                        @elseif (strtolower($row['status']) == 'i')
                            {!! __('label.invited') !!}
                        @else
                            {!! __('label.deactivated') !!}
                        @endif</td>
                        <td>@if ($row['twoFAEnabled'])
                            {!! __('label.yes') !!}
                        @else
                            {!! __('label.no') !!}
                        @endif</td>
                        <td>@can('users.delete')<a href="{{ BASE_URL }}/users/delUser/{{ $row['id'] }}" class="delete"><i class="fa fa-trash"></i> {!! __('links.delete') !!}</a>@endcan</td>
                    </tr>
            @endforeach
            </tbody>
        </table>
    </x-global::layout.content-box>
</div>

@once
@push('scripts')
<script type="text/javascript">
    jQuery(document).ready(function() {
            leantime.usersController.initUserTable();
            leantime.usersController._initModals();
            leantime.usersController.initUserEditModal();

        }
    );

</script>
@endpush
@endonce

@endsection
