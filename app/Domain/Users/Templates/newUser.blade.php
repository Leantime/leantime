@php
    $roles = $tpl->get('roles');
    $values = $tpl->get('values');
    $projects = $tpl->get('relations');
@endphp

<script type="text/javascript">

    jQuery(document).ready(function(){
        // Dual Box Select
        var db = jQuery('#dualselect').find('.ds_arrow button');
        var sel1 = jQuery('#dualselect select#selectOrigin');
        var sel2 = jQuery('#dualselect select#selectDest');
        var projects = jQuery('#projects');

        db.click(function(){
            var t = (jQuery(this).hasClass('ds_prev'))? 0 : 1;

            if(t) {
                sel1.find('option').each(function(){
                    if(jQuery(this).is(':selected')) {
                        jQuery(this).attr('selected',false);
                        sel2.append(jQuery(this).clone());
                        jQuery('#projects').append(jQuery(this));
                        jQuery('#projects option').attr("selected", "selected");
                    }
                });
            } else {
                sel2.find('option').each(function(){
                    if(jQuery(this).is(':selected')) {
                        jQuery(this).attr('selected',false);
                        index = jQuery(this).index();
                        sel1.append(jQuery(this));
                        jQuery('#projects option:eq('+index+')').remove();
                        jQuery('#projects option').attr("selected", "selected");
                    }
                });
            }
            return false;
        });
    });

</script>

<h4 class="widgettitle title-light"><x-global::elements.icon name="groups" /> {{ __('headlines.new_user') }}</h4>

{!! $tpl->displayNotification() !!}

<form action="{{ BASE_URL }}/users/newUser" method="post" class="stdform userEditModal formModal">
    <div class="row" style="width:800px;">
        <div class="col-md-7">

                <h4 class="widgettitle title-light">{{ __('label.profile_information') }}</h4>

                    <label for="firstname">{{ __('label.firstname') }}</label> <input
                        type="text" name="firstname" id="firstname"
                        value="{{ $values['firstname'] }}" /><br />

                    <label for="lastname">{{ __('label.lastname') }}</label> <input
                        type="text" name="lastname" id="lastname"
                        value="{{ $values['lastname'] }}" /><br />

            <label for="role">{{ __('label.role') }}</label>
            <x-globals::forms.select name="role" id="role">
                @foreach($tpl->get('roles') as $key => $role)
                    @if($login::userHasRole(\Leantime\Domain\Auth\Models\Roles::$manager) && $key > 30)
                        @continue
                    @endif
                    <option value="{{ $key }}"
                        {{ $key == $values['role'] ? 'selected="selected"' : '' }}>
                        {{ __('label.roles.' . $role) }}
                    </option>
                @endforeach
            </x-globals::forms.select> <br />

            <label for="client">{{ __('label.client') }}</label>
            <x-globals::forms.select name="client" id="client">
                @if($login::userIsAtLeast('admin'))
                    <option value="0" selected="selected">{{ __('label.no_clients') }}</option>
                @endif
                @foreach($tpl->get('clients') as $client)
                    @if($login::userHasRole(\Leantime\Domain\Auth\Models\Roles::$manager) && $client['id'] !== session('userdata.clientId'))
                        @continue
                    @endif
                    <option value="{{ $client['id'] }}"
                        {{ $client['id'] == $values['clientId'] || $tpl->get('preSelectedClient') == $client['id'] ? 'selected="selected"' : '' }}>{{ e($client['name']) }}</option>
                @endforeach
            </x-globals::forms.select><br/>
            <br/>

                <h4 class="widgettitle title-light">{{ __('label.contact_information') }}</h4>

                    <label for="user">{{ __('label.email') }}</label> <input
                        type="text" name="user" id="user" value="{{ $values['user'] }}" /><br />

                    <label for="phone">{{ __('label.phone') }}</label> <input
                        type="text" name="phone" id="phone"
                        value="{{ $values['phone'] }}" /><br />
            <br/>

            <h4 class="widgettitle title-light">{{ __('label.employee_information') }}</h4>
                <label for="jobTitle">{{ __('label.jobTitle') }}</label> <input
                    type="text" name="jobTitle" id="jobTitle" value="{{ $values['jobTitle'] }}" /><br />

                <label for="jobLevel">{{ __('label.jobLevel') }}</label> <input
                    type="text" name="jobLevel" id="jobLevel" value="{{ $values['jobLevel'] }}" /><br />

                <label for="department">{{ __('label.department') }}</label> <input
                    type="text" name="department" id="department" value="{{ $values['department'] }}" /><br />

                    <p class="stdformbutton">
                        <input type="hidden" name="save" value="1" />
                        <x-globals::forms.button submit type="primary" name="save" id="save">{{ __('buttons.invite_user') }}</x-globals::forms.button>
                    </p>

        </div>
        <div class="col-md-5">

                <h4 class="widgettitle title-light">{{ __('label.project_assignment') }}</h4>

                <div class="scrollableItemList">
                    @php
                        $currentClient = '';
                        $i = 0;
                    @endphp
                    @foreach($tpl->get('allProjects') as $row)
                        @if($login::userHasRole(\Leantime\Domain\Auth\Models\Roles::$manager) && $row['clientId'] !== session('userdata.clientId'))
                            @continue
                        @endif
                        @php
                            if ($row['clientName'] == '') {
                                $row['clientName'] = 'Not assigned to client';
                            }
                            if ($currentClient != $row['clientName']) {
                                if ($i > 0) {
                                    echo '</div>';
                                }
                                echo "<h3 id='accordion_link_" . $i . "'>
                                    <a href='#' onclick='accordionToggle(" . $i . ");' id='accordion_toggle_" . $i . "'><x-global::elements.icon name="expand_more" /> " . e($row['clientName']) . "</a>
                                    </h3>
                                    <div id='accordion_" . $i . "' class='simpleAccordionContainer'>";
                                $currentClient = $row['clientName'];
                            }
                        @endphp
                        <div class="item" style="padding:10px 0px;">
                            <x-globals::forms.checkbox name="projects[]" id="project_{{ $row['id'] }}" value="{{ $row['id'] }}"
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
</form>

<script>
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
