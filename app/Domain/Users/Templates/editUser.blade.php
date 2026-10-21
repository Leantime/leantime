@extends($layout)
@section('content')

@php
    $projects = $relations;
@endphp

{!! $tpl->displayNotification() !!}

<x-global::layout.page-header icon="fa {{ $tpl->getModulePicture() }}" :eyebrow="__('label.administration')">
    {!! __('headlines.edit_user') !!}
</x-global::layout.page-header>

<form action="" method="post" class="stdform userEditModal">
        <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
        <div class="maincontent">
            <div class="row">
                <div class="col-md-7">
                    <x-global::layout.content-box>
                    <x-global::elements.section-header>{!! __('label.profile_information') !!}</x-global::elements.section-header>

                    <x-global::forms.label for="firstname">{!! __('label.firstname') !!}</x-global::forms.label> <x-global::forms.text-input
                        name="firstname" id="firstname"
                        value="{{ $values['firstname'] }}" /><br />

                    <x-global::forms.label for="lastname">{!! __('label.lastname') !!}</x-global::forms.label> <x-global::forms.text-input
                        name="lastname" id="lastname"
                        value="{{ $values['lastname'] }}" /><br />



                    <x-global::forms.label for="role">{!! __('label.role') !!}</x-global::forms.label>
                    <x-global::forms.select name="role" id="role">

                        @foreach ($roles as $key => $role)
                            {{-- Privilege ceiling (parity with newUser): a manager may never
                                 assign the admin(40)/owner(50) roles. Editing users is admin+
                                 today, so this is defense-in-depth against any future widening
                                 of who can reach this screen — a lower role must never be able
                                 to escalate an account above its own authority. --}}
                            @if ($login::userHasRole(\Leantime\Domain\Auth\Models\Roles::$manager) && \Leantime\Domain\Auth\Models\Roles::getRoleLevel($key) > 30)
                                @continue
                            @endif
                            <option value="{{ $key }}"
                                @if ($key == $values['role']) selected="selected" @endif>
                                {{ \Leantime\Domain\Auth\Models\Roles::label($key) }}
                            </option>
                        @endforeach

                    </x-global::forms.select> <br />

                    <x-global::forms.label for="status">{!! __('label.status') !!}</x-global::forms.label>
                    <x-global::forms.select name="status" id="status" class="pull-left">

                        <option value="a"
                            @if (strtolower($values['status']) == 'a') selected="selected" @endif>
                            {!! __('label.active') !!}
                        </option>

                        <option value="i"
                            @if (strtolower($values['status']) == 'i') selected="selected" @endif>
                            {!! __('label.invited') !!}
                        </option>

                        <option value="0"
                            @if (strtolower($values['status']) === '' || $values['status'] === 0 || $values['status'] === '0') selected="selected" @endif>
                            {!! __('label.deactivated') !!}
                        </option>


                    </x-global::forms.select>
                        @if ($values['status'] == 'i')
                        <div class="pull-left dropdownWrapper" style="padding-left:5px; line-height: 29px;">
                            <x-global::actions.dropdown variant="panel" as="span" class="dropdown" menu-class="padding-md" keep-open>
                                <x-slot:trigger class="btn btn-default" href="{{ BASE_URL }}/auth/userInvite/{{ $values['pwReset'] }}"><i class="fa fa-link"></i> {!! __('label.copyinviteLink') !!}</x-slot:trigger>
                                <x-global::forms.text-input id="inviteURL" value="{{ BASE_URL }}/auth/userInvite/{{ $values['pwReset'] }}" />
                                <x-global::forms.button contentRole="primary" onclick="leantime.snippets.copyUrl('inviteURL');">{!! __('links.copy_url') !!}</x-global::forms.button>
                            </x-global::actions.dropdown>
                            <x-global::forms.button tag="a" link="{{ BASE_URL }}/users/editUser/{{ $values['id'] }}?resendInvite=1" contentRole="default" style="margin-left:5px;"><i class="fa fa-envelope"></i> {!! __('buttons.resend_invite') !!}</x-global::forms.button>
                        </div>
                        @endif
                        <div class="clearfix"></div>




                    <x-global::forms.label for="client">{!! __('label.client') !!}</x-global::forms.label>
                    <x-global::forms.select name='client' id="client">
                        @if ($login::userIsAtLeast('manager'))
                            <option value="0" selected="selected">{!! __('label.no_clients') !!}</option>
                        @endif
                        @foreach ($clients as $clientItem)
                            <option value="{{ $clientItem['id'] }}" @if ($clientItem['id'] == $values['clientId']) selected="selected" @endif>{{ $clientItem['name'] }}</option>
                        @endforeach
                    </x-global::forms.select><br/>
                        <br/>

                        <x-global::elements.section-header>{!! __('label.contact_information') !!}</x-global::elements.section-header>

                        <x-global::forms.label for="user">{!! __('label.email') !!}</x-global::forms.label> <x-global::forms.text-input
                            name="user" id="user" value="{{ $values['user'] }}" /><br />

                        <x-global::forms.label for="phone">{!! __('label.phone') !!}</x-global::forms.label> <x-global::forms.text-input
                            name="phone" id="phone"
                            value="{{ $values['phone'] }}" /><br /><br />


                        <x-global::elements.section-header>{!! __('label.employee_information') !!}</x-global::elements.section-header>
                        <x-global::forms.label for="jobTitle">{!! __('label.jobTitle') !!}</x-global::forms.label> <x-global::forms.text-input
                            name="jobTitle" id="jobTitle" value="{{ $values['jobTitle'] }}" /><br />

                        <x-global::forms.label for="jobLevel">{!! __('label.jobLevel') !!}</x-global::forms.label> <x-global::forms.text-input
                            name="jobLevel" id="jobLevel" value="{{ $values['jobLevel'] }}" /><br />

                        <x-global::forms.label for="department">{!! __('label.department') !!}</x-global::forms.label> <x-global::forms.text-input
                            name="department" id="department" value="{{ $values['department'] }}" /><br />


                        @if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$admin))
                            {{-- Capacity attributes — admin-only. Weekly hours is what
                                 downstream resource-planning surfaces (Resource
                                 Allocation, cross-program warnings, portfolio
                                 rollups) use to answer "how much of this person
                                 is available to plan against?". Employment type
                                 sets the semantic (target vs ceiling vs cap vs
                                 best-effort). Both nullable — until an admin
                                 sets them, capacity math treats this user as
                                 unconfigured and skips warnings. --}}
                            <x-global::elements.section-header>{!! __('label.capacity') !!}</x-global::elements.section-header>

                            <x-global::forms.label for="weekly_hours">{!! __('label.weekly_hours') !!}</x-global::forms.label>
                            <x-global::forms.text-input
                                name="weekly_hours"
                                id="weekly_hours"
                                type="number"
                                min="0"
                                max="168"
                                :value="$values['weekly_hours'] ?? ''"
                                placeholder="{{ __('label.weekly_hours_placeholder') }}" /><br />
                            <span class="hint">{!! __('label.weekly_hours_hint') !!}</span><br /><br />

                            <x-global::forms.label for="employment_type">{!! __('label.employment_type') !!}</x-global::forms.label>
                            <x-global::forms.select name="employment_type" id="employment_type">
                                <option value="">{!! __('label.employment_type.unset') !!}</option>
                                @foreach (\Leantime\Domain\Users\Enums\EmploymentType::cases() as $type)
                                    <option value="{{ $type->value }}"
                                        @if (($values['employment_type'] ?? '') === $type->value) selected="selected" @endif>
                                        {!! __($type->langKey()) !!}
                                    </option>
                                @endforeach
                            </x-global::forms.select><br />
                            <span class="hint">{!! __('label.employment_type_hint') !!}</span><br /><br />
                        @endif


                    <p class="stdformbutton">
                        <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" id="save" />
                    </p>
                    </x-global::layout.content-box>
                </div>
                <div class="col-md-5">
                    <x-global::layout.content-box>
                    <x-global::elements.section-header>{!! __('label.project_assignment') !!}</x-global::elements.section-header>

                    <div class="scrollableItemList">
                        @php
                            // One collapsible section per client; projects without a client go under "Not assigned to a client".
                            $projectsByClient = [];
                            foreach ($allProjects as $row) {
                                $clientName = (string) ($row['clientName'] ?? '') !== '' ? $row['clientName'] : __('label.no_clients');
                                $projectsByClient[$clientName][] = $row;
                            }
                        @endphp
                        @foreach ($projectsByClient as $clientName => $clientProjects)
                            <x-global::elements.accordion :title="$clientName" open>
                                @foreach ($clientProjects as $row)
                                    <div class="item" style="padding:10px 0px;">
                                        <input type="checkbox" name="projects[]" id='project_{{ $row['id'] }}' value="{{ $row['id'] }}"
                                            @if (is_array($projects) === true && in_array($row['id'], $projects) === true)
                                                checked='checked'
                                            @endif
                                        />
                                        <span class="projectAvatar" style="width:30px; float:left; margin-right:10px;">
                                            <img src='{{ BASE_URL }}/api/projects?projectAvatar={{ $row['id'] }}&v={{ format($row['modified'])->timestamp() }}' />
                                        </span>

                                        <x-global::forms.label for="project_{{ $row['id'] }}" style="margin-top:-11px">
                                            <small>{{ $row['type'] }}</small><br />
                                                                    {{ $row['name'] }}</x-global::forms.label>
                                        <div class="clearall"></div>
                                    </div>
                                @endforeach
                            </x-global::elements.accordion>
                        @endforeach
                    </div>
                    </x-global::layout.content-box>

                </div>
            </div>
        </div>
</form>


@endsection
