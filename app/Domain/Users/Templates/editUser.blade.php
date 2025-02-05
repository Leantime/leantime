<x-global::content.modal.modal-buttons/>

<?php
$status = $tpl->get('status');
$values = $tpl->get('values');
$projects = $tpl->get('relations');
?>

@displayNotification()

{{-- <div class="pageheader"> --}}
    {{-- <div class="pageicon"><span class="fa fa-people-group"></span></div> --}}
    {{-- <div class="pagetitle">
        <h5>{{ __("label.administration") }}</h5>
        <h1>{{ __("headlines.edit_user") }}</h1>
    </div> --}}
{{-- </div> --}}
<!--pageheader-->

<form action="{{ BASE_URL }}/users/editUser" method="post" class="stdform userEditModal">
        <input type="hidden" name="<?=session("formTokenName")?>" value="<?=session("formTokenValue")?>" />
            <div class="row row-fluid">
                <div class="col-md-7">
                    <h4 class="widgettitle title-light">{{ __("label.profile_information") }}</h4>

                        <x-global::forms.text-input
                            inputType="text"
                            name="firstname"
                            id="firstname"
                            value="{{ $values['firstname'] }}"
                            labelText="{{ __('label.firstname') }}"
                        />

                        <x-global::forms.text-input
                            inputType="text"
                            name="lastname"
                            id="lastname"
                            value="{{ $values['lastname'] }}"
                            labelText="{{ __('label.lastname') }}"
                        />


                        <x-global::forms.select name="role" id="role" :labelText="__('label.role')">
                            @foreach ($tpl->get('roles') as $key => $role)
                                <x-global::forms.select.select-option :value="$key" :selected="$key == $values['role']">
                                    {!! __('label.roles.' . $role) !!}
                                </x-global::forms.select.select-option>
                            @endforeach
                        </x-global::forms.select>
                        <br />
                        
                        <x-global::forms.select name="status" id="status" class="pull-left" :labelText="__('label.status')">
                            <x-global::forms.select.select-option value="a" :selected="strtolower($values['status']) == 'a'">
                                {!! __('label.active') !!}
                            </x-global::forms.select.select-option>
                        
                            <x-global::forms.select.select-option value="i" :selected="strtolower($values['status']) == 'i'">
                                {!! __('label.invited') !!}
                            </x-global::forms.select.select-option>
                        
                            <x-global::forms.select.select-option value="" :selected="strtolower($values['status']) == ''">
                                {!! __('label.deactivated') !!}
                            </x-global::forms.select.select-option>
                        </x-global::forms.select>
                        
                    <?php if ($values['status'] == 'i') { ?>
                    <div class="pull-left dropdownWrapper" style="padding-left:5px; line-height: 29px;">
                        <x-global::actions.dropdown label-text="<i class='fa fa-link'></i> {!! __('label.copyinviteLink') !!}"
                            contentRole="link" position="bottom" align="start">
                            <x-slot:menu class="padding-md noClickProp">
                                <x-global::actions.dropdown.item>
                                    <x-global::forms.text-input
                                    type="text"
                                    id="inviteURL"
                                    value="{{ BASE_URL }}/auth/userInvite/{{ $values['pwReset'] }}"
                                    class="w-full"
                                />

                                <x-global::forms.button
                                    type="button"
                                    class="btn btn-primary"
                                    onclick="leantime.snippets.copyUrl('inviteURL');"
                                >
                                    {{ __('links.copy_url') }}
                                </x-global::forms.button>
                                                                </x-global::actions.dropdown.item>
                            </x-slot:menu>
                        </x-global::actions.dropdown>

                        <a href="{{ BASE_URL }}/users/editUser/{{ $values['id'] }}?resendInvite"
                            class="btn btn-default" style="margin-left:5px;">
                            <i class="fa fa-envelope"></i> {!! __('buttons.resend_invite') !!}
                        </a>
                    </div>

                    <?php } ?>
                    <div class="clearfix"></div>



                    <x-global::forms.select name="client" id="client" :labelText="__('label.client')">
                        @if ($login::userIsAtLeast('manager'))
                            <x-global::forms.select.select-option value="0" selected="selected">
                                {{ __('label.no_clients') }}
                            </x-global::forms.select.select-option>
                        @endif
                    
                        @foreach ($tpl->get('clients') as $client)
                            <x-global::forms.select.select-option :value="$client->id" :selected="$client->id == $values['clientId']">
                                {!! $tpl->escape($client->name) !!}
                            </x-global::forms.select.select-option>
                        @endforeach
                    </x-global::forms.select>
                    <br />
                    
                    <br />

                        <h4 class="widgettitle title-light">{{ __("label.contact_information") }}</h4>

                        <x-global::forms.text-input
                            inputType="text"
                            name="user"
                            id="user"
                            value="{{ $values['user'] }}"
                            labelText="{{ __('label.email') }}"
                        />
                        
                        <x-global::forms.text-input
                            inputType="text"
                            name="phone"
                            id="phone"
                            value="{{ $values['phone'] }}"
                            labelText="{{ __('label.phone') }}"
                        />

                        <h4 class="widgettitle title-light">{{ __("label.employee_information") }}</h4>
                        
                        <x-global::forms.text-input
                            inputType="text"
                            name="phone"
                            id="phone"
                            value="{{ $values['phone'] }}"
                            labelText="{{ __('label.phone') }}"
                        />

                        <x-global::forms.text-input
                            inputType="text"
                            name="jobLevel"
                            id="jobLevel"
                            value="{{ $values['jobLevel'] }}"
                            labelText="{{ __('label.jobLevel') }}"
                        />

                        <x-global::forms.text-input
                            inputType="text"
                            name="department"
                            id="department"
                            value="{{ $values['department'] }}"
                            labelText="{{ __('label.department') }}"
                        />


                    <p class="stdformbutton">
                        <x-global::forms.button type="submit" name="save" id="save" class="button">
                            {{ __('buttons.save') }}
                        </x-global::forms.button>
                                            </p>
                </div>
                <div class="col-md-5">
                    <h4 class="widgettitle title-light">{{ __("label.project_assignment") }}</h4>

                    <div class="scrollableItemList">
                        <?php
                        $currentClient = '';
                        $i = 0;
                        foreach ($tpl->get('allProjects') as $row) {
                            if ($row['clientName'] == null) {
                                $row['clientName'] = "Not assigned to client";
                            }
                            if ($currentClient != $row['clientName']) {
                                if ($i > 0) {
                                    echo"</div>";
                                }
                                echo "<h3 id='accordion_link_" . $i . "'>
                            <a href='#' onclick='accordionToggle(" . $i . ");' id='accordion_toggle_" . $i . "'><i class='fa fa-angle-down'></i> " . $tpl->escape($row['clientName']) . "</a>
                            </h3>
                            <div id='accordion_" . $i . "' class='simpleAccordionContainer'>";
                                $currentClient = $row['clientName'];
                            } ?>
                            <div class="item" style="padding:10px 0px;">
                                <x-global::forms.checkbox
                                    name="projects[]"
                                    :id="'project_' . $row['id']"
                                    :value="$row['id']"
                                    :checked="is_array($projects) && in_array($row['id'], $projects)"
                                />
                                
                                <span class="projectAvatar" style="width:30px; float:left; margin-right:10px;">
                                    <img src='{{ BASE_URL }}/api/projects?projectAvatar=<?=$row["id"] ?>&v=<?=format($row['modified'])->timestamp() ?>' />
                                </span>

                            <label for="project_<?php echo $row['id']; ?>" style="margin-top:-11px">
                                <small><?php $tpl->e($row['type']); ?></small><br />
                                <?php $tpl->e($row['name']); ?></label>
                            <div class="clearall"></div>
                        </div>
                        <?php $i++; ?>
                        <?php } ?>

                    </div>
                </div>
            </div>
</form>

<script>
    jQuery(".noClickProp.dropdown-menu").on("click", function(e) {
        e.stopPropagation();
    });

    function accordionToggle(id) {

        let currentLink = jQuery("#accordion_toggle_" + id).find("i.fa");

        if (currentLink.hasClass("fa-angle-right")) {
            currentLink.removeClass("fa-angle-right");
            currentLink.addClass("fa-angle-down");
            jQuery('#accordion_' + id).slideDown("fast");
        } else {
            currentLink.removeClass("fa-angle-down");
            currentLink.addClass("fa-angle-right");
            jQuery('#accordion_' + id).slideUp("fast");
        }

    }
</script>
