@php
    $status = $tpl->get('status');
    $values = $tpl->get('values');
    $projects = $tpl->get('relations');
@endphp

{!! $tpl->displayNotification() !!}

<div class="pageheader">
    <div class="pageicon"><span class="fa {{ $tpl->getModulePicture() }}"></span></div>
    <div class="pagetitle">
        <h5>{{ __('label.administration') }}</h5>
        <h1>{{ __('headlines.edit_user') }}</h1>
    </div>
</div>

<form action="" method="post" class="stdform userEditModal">
        <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
        <div class="maincontent">
            <div class="tw:grid tw:grid-cols-[7fr_5fr] tw:gap-6">
                <div>
                    <div class="maincontentinner">
                    <h4 class="widgettitle title-light">{{ __('label.profile_information') }}</h4>

                    <label for="firstname">{{ __('label.firstname') }}</label> <x-global::forms.input name="firstname" id="firstname" value="{{ e($values['firstname']) }}" /><br />

                    <label for="lastname">{{ __('label.lastname') }}</label> <x-global::forms.input name="lastname" id="lastname" value="{{ e($values['lastname']) }}" /><br />

                    <label for="role">{{ __('label.role') }}</label>
                    <x-global::forms.select name="role" id="role">
                        @foreach($tpl->get('roles') as $key => $role)
                            <option value="{{ $key }}"
                                {{ $key == $values['role'] ? 'selected="selected"' : '' }}>
                                {{ __('label.roles.' . $role) }}
                            </option>
                        @endforeach
                    </x-global::forms.select> <br />

                    <label for="status">{{ __('label.status') }}</label>
                    <x-global::forms.select name="status" id="status" class="tw:float-left">
                        <option value="a"
                            {{ strtolower($values['status']) == 'a' ? 'selected="selected"' : '' }}>
                            {{ __('label.active') }}
                        </option>
                        <option value="i"
                            {{ strtolower($values['status']) == 'i' ? 'selected="selected"' : '' }}>
                            {{ __('label.invited') }}
                        </option>
                        <option value="0"
                            {{ strtolower($values['status']) === '' || $values['status'] === 0 || $values['status'] === '0' ? 'selected="selected"' : '' }}>
                            {{ __('label.deactivated') }}
                        </option>
                    </x-global::forms.select>
                        @if($values['status'] == 'i')
                        <div class="tw:float-left dropdownWrapper" style="padding-left:5px; line-height: 29px;">
                            <a class="dropdown-toggle btn btn-default" data-toggle="dropdown" href="{{ BASE_URL }}/auth/userInvite/{{ $values['pwReset'] }}"><i class="fa fa-link"></i> {{ __('label.copyinviteLink') }}</a>
                            <div class="dropdown-menu padding-md noClickProp">
                                <input type="text" id="inviteURL" value="{{ BASE_URL }}/auth/userInvite/{{ $values['pwReset'] }}" />
                                <x-global::button tag="button" type="primary" onclick="leantime.snippets.copyUrl('inviteURL');">{{ __('links.copy_url') }}</x-global::button>
                            </div>
                            <x-global::button link="{{ BASE_URL }}/users/editUser/{{ $values['id'] }}?resendInvite" type="secondary" style="margin-left:5px;" icon="fa fa-envelope">{{ __('buttons.resend_invite') }}</x-global::button>
                        </div>
                        @endif
                        <div class="clearfix"></div>

                    <label for="client">{{ __('label.client') }}</label>
                    <x-global::forms.select name="client" id="client">
                        @if($login::userIsAtLeast('manager'))
                            <option value="0" selected="selected">{{ __('label.no_clients') }}</option>
                        @endif
                        @foreach($tpl->get('clients') as $client)
                            <option value="{{ $client['id'] }}" {{ $client['id'] == $values['clientId'] ? 'selected="selected"' : '' }}>{{ e($client['name']) }}</option>
                        @endforeach
                    </x-global::forms.select><br/>
                        <br/>

                        <h4 class="widgettitle title-light">{{ __('label.contact_information') }}</h4>

                        <label for="user">{{ __('label.email') }}</label> <x-global::forms.input name="user" id="user" value="{{ e($values['user']) }}" /><br />

                        <label for="phone">{{ __('label.phone') }}</label> <x-global::forms.input name="phone" id="phone" value="{{ e($values['phone']) }}" /><br /><br />

                        <h4 class="widgettitle title-light">{{ __('label.employee_information') }}</h4>
                        <label for="jobTitle">{{ __('label.jobTitle') }}</label> <x-global::forms.input name="jobTitle" id="jobTitle" value="{{ e($values['jobTitle']) }}" /><br />

                        <label for="jobLevel">{{ __('label.jobLevel') }}</label> <x-global::forms.input name="jobLevel" id="jobLevel" value="{{ e($values['jobLevel']) }}" /><br />

                        <label for="department">{{ __('label.department') }}</label> <x-global::forms.input name="department" id="department" value="{{ e($values['department']) }}" /><br />

                    <p class="stdformbutton">
                        <x-global::button submit type="primary" name="save" id="save">{{ __('buttons.save') }}</x-global::button>
                    </p>
                    </div>
                </div>
                <div>
                    <div class="maincontentinner">
                    <h4 class="widgettitle title-light">{{ __('label.project_assignment') }}</h4>

                    <div class="scrollableItemList">
                        @php
                            $currentClient = '';
                            $i = 0;
                        @endphp
                        @foreach($tpl->get('allProjects') as $row)
                            @php
                                if ($row['clientName'] == null) {
                                    $row['clientName'] = 'Not assigned to client';
                                }
                                if ($currentClient != $row['clientName']) {
                                    if ($i > 0) {
                                        echo '</div>';
                                    }
                                    echo "<h3 id='accordion_link_" . $i . "'>
                                        <a href='#' onclick='accordionToggle(" . $i . ");' id='accordion_toggle_" . $i . "'><i class='fa fa-angle-down'></i> " . e($row['clientName']) . "</a>
                                        </h3>
                                        <div id='accordion_" . $i . "' class='simpleAccordionContainer'>";
                                    $currentClient = $row['clientName'];
                                }
                            @endphp
                            <div class="item" style="padding:10px 0px;">
                                <x-global::forms.checkbox name="projects[]" id="project_{{ $row['id'] }}" value="{{ $row['id'] }}"
                                    :checked="is_array($projects) && in_array($row['id'], $projects)" />
                                <span class="projectAvatar" style="width:30px; float:left; margin-right:10px;">
                                    <img src='{{ BASE_URL }}/api/projects?projectAvatar={{ $row['id'] }}&v={{ format($row['modified'])->timestamp() }}' />
                                </span>

                                <label for="project_{{ $row['id'] }}" style="margin-top:-11px">
                                    <small>{{ e($row['type']) }}</small><br />
                                    {{ e($row['name']) }}</label>
                                <div class="clearall"></div>
                            </div>
                            @php $i++; @endphp
                        @endforeach
                    </div>
                    </div>
                </div>
            </div>
        </div>
</form>

<script>
    jQuery(".noClickProp.dropdown-menu").on("click", function(e) {
        e.stopPropagation();
    });

    function accordionToggle(id) {
        let currentLink = jQuery("#accordion_toggle_"+id).find("i.fa");
        if(currentLink.hasClass("fa-angle-right")){
            currentLink.removeClass("fa-angle-right");
            currentLink.addClass("fa-angle-down");
            jQuery('#accordion_'+id).slideDown("fast");
        }else{
            currentLink.removeClass("fa-angle-down");
            currentLink.addClass("fa-angle-right");
            jQuery('#accordion_'+id).slideUp("fast");
        }
    }
</script>
