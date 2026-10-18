@extends($layout)
@section('content')

@php
    $projects = $relations;
@endphp

<x-global::actions.modal-header>
    <x-slot:title><i class="fa fa-people-group"></i> {!! __('headlines.new_user') !!}</x-slot:title>
</x-global::actions.modal-header>{!! $tpl->displayNotification() !!}

<form action="{{ BASE_URL }}/users/newUser" method="post" class="stdform userEditModal formModal">
    <div class="row" style="width:800px;">
        <div class="col-md-7">

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
                    @if ($login::userHasRole(\Leantime\Domain\Auth\Models\Roles::$manager) && \Leantime\Domain\Auth\Models\Roles::getRoleLevel($key) > 30)
                        @continue
                    @endif
                        <option value="{{ $key }}"
                        @if ($key == $values['role']) selected="selected" @endif>
                        {{ \Leantime\Domain\Auth\Models\Roles::label($key) }}
                    </option>
                @endforeach

            </x-global::forms.select> <br />

            <x-global::forms.label for="client">{!! __('label.client') !!}</x-global::forms.label>
            <x-global::forms.select name='client' id="client">
                @if ($login::userIsAtLeast('admin'))
                    <option value="0" selected="selected">{!! __('label.no_clients') !!}</option>
                @endif
                @foreach ($clients as $clientItem)
                    @if ($login::userHasRole(\Leantime\Domain\Auth\Models\Roles::$manager) && $clientItem['id'] !== session('userdata.clientId'))
                        @continue
                    @endif
                    <option value="{{ $clientItem['id'] }}"
                            @if ($clientItem['id'] == $values['clientId'] || $preSelectedClient == $clientItem['id']) selected="selected" @endif>{{ $clientItem['name'] }}</option>
                @endforeach
            </x-global::forms.select><br/>
            <br/>


                <x-global::elements.section-header>{!! __('label.contact_information') !!}</x-global::elements.section-header>


                    <x-global::forms.label for="user">{!! __('label.email') !!}</x-global::forms.label> <x-global::forms.text-input
                        name="user" id="user" value="{{ $values['user'] }}" /><br />

                    <x-global::forms.label for="phone">{!! __('label.phone') !!}</x-global::forms.label> <x-global::forms.text-input
                        name="phone" id="phone"
                        value="{{ $values['phone'] }}" /><br />
            <br/>

            <x-global::elements.section-header>{!! __('label.employee_information') !!}</x-global::elements.section-header>
                <x-global::forms.label for="jobTitle">{!! __('label.jobTitle') !!}</x-global::forms.label> <x-global::forms.text-input
                    name="jobTitle" id="jobTitle" value="{{ $values['jobTitle'] }}" /><br />

                <x-global::forms.label for="jobLevel">{!! __('label.jobLevel') !!}</x-global::forms.label> <x-global::forms.text-input
                    name="jobLevel" id="jobLevel" value="{{ $values['jobLevel'] }}" /><br />

                <x-global::forms.label for="department">{!! __('label.department') !!}</x-global::forms.label> <x-global::forms.text-input
                    name="department" id="department" value="{{ $values['department'] }}" /><br />


                    <p class="stdformbutton">
                        <input type="hidden" name="save" value="1" />
                        <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.invite_user')" name="save" id="save" />
                    </p>

        </div>
        <div class="col-md-5">

                <x-global::elements.section-header>{!! __('label.project_assignment') !!}</x-global::elements.section-header>

                <div class="scrollableItemList">
                    @php
                        // One collapsible section per client; projects without a client go under "Not assigned to a client".
                        $projectsByClient = [];
                        foreach ($allProjects as $row) {
                            if ($login::userHasRole(\Leantime\Domain\Auth\Models\Roles::$manager) && $row['clientId'] !== session('userdata.clientId')) {
                                continue;
                            }
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


        </div>
    </div>
</form>

@once
@push('scripts')
<script type="text/javascript">

    jQuery(document).ready(function(){
        // Dual Box Select
        var db = jQuery('#dualselect').find('.ds_arrow button');    //get arrows of dual select
        var sel1 = jQuery('#dualselect select#selectOrigin');        //get first select element
        var sel2 = jQuery('#dualselect select#selectDest');            //get second select element
        var projects = jQuery('#projects');
        //sel2.empty(); //empty it first from dom.

        db.click(function(){

            var t = (jQuery(this).hasClass('ds_prev'))? 0 : 1;    // 0 if arrow prev otherwise arrow next

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

@endpush
@endonce

@endsection
