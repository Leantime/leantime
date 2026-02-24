@php
    $status = $tpl->get('status');
    $values = $tpl->get('values');
    $projects = $tpl->get('relations');
@endphp

<div style="min-width:700px;">

<h4 class="widgettitle title-light"><i class="fa fa-key" aria-hidden="true"></i> {{ $tpl->__('headlines.api_key') }}</h4>

{!! $tpl->displayNotification() !!}

<form action="{{ BASE_URL }}/api/apiKey/{{ (int) $_GET['id'] }}" method="post" class="stdform formModal">
        <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
        <input type="hidden" name="save" value="1" />

        <div class="row">
            <div class="col-md-6">

                <h4 class="widgettitle title-light">{{ $tpl->__('label.basic_information') }}</h4>

                <label>{{ $tpl->__('label.key') }}</label><div class="clearfix"></div>
                lt_{{ substr($values['user'], 0, 5) }}***<br /><br />

                <label for="firstname">{{ $tpl->__('label.key_name') }}</label><div class="clearfix"></div>
                    <input
                    type="text" name="firstname" id="firstname"
                    value="{{ $values['firstname'] }}" /><br />


                <label for="role">{{ $tpl->__('label.role') }}</label><div class="clearfix"></div>
                <x-globals::forms.select name="role" id="role">
                    @foreach ($tpl->get('roles') as $key => $role)
                        <option value="{{ $key }}"
                            @if ($key == $values['role']) selected="selected" @endif>
                            {{ $tpl->__('label.roles.' . $role) }}
                        </option>
                    @endforeach
                </x-globals::forms.select> <br />

                <label for="status">{{ $tpl->__('label.status') }}</label><div class="clearfix"></div>
                <x-globals::forms.select name="status" id="status">
                    <option value="a"
                        @if (strtolower($values['status']) == 'a') selected="selected" @endif>
                        {{ $tpl->__('label.active') }}
                    </option>

                    <option value=""
                        @if (strtolower($values['status']) == '') selected="selected" @endif>
                        {{ $tpl->__('label.deactivated') }}
                    </option>
                </x-globals::forms.select>

                    <div class="clearfix"></div>

                <p class="stdformbutton">
                    <x-globals::forms.button submit type="primary" name="save" id="save">{{ $tpl->__('buttons.save') }}</x-globals::forms.button>
                </p>

            </div>
            <div class="col-md-6">

                <h4 class="widgettitle title-light">{{ $tpl->__('label.project_access') }}</h4>

                <div class="scrollableItemList">
                    @php
                        $currentClient = '';
                        $i = 0;
                        $containerOpen = false;
                    @endphp
                    @foreach ($tpl->get('allProjects') as $row)
                        @if ($currentClient != $row['clientName'])
                            @if ($i > 0 && $containerOpen)
                                {!! '</div>' !!}
                                @php $containerOpen = false; @endphp
                            @endif

                            {!! "<h3 id='accordion_link_" . $i . "'>
                                <a href='#' onclick='accordionToggle(" . $i . ");' id='accordion_toggle_" . $i . "'><i class='fa fa-angle-down'></i> " . $tpl->escape($row['clientName']) . "</a>
                                </h3>
                                <div id='accordion_" . $i . "' class='simpleAccordionContainer'>" !!}
                            @php
                                $currentClient = $row['clientName'];
                                $containerOpen = true;
                            @endphp
                        @endif

                        <div class="item">
                            <x-globals::forms.checkbox name="projects[]" id="project_{{ $row['id'] }}" value="{{ $row['id'] }}"
                                :checked="is_array($projects) === true && in_array($row['id'], $projects) === true"
                                label="{{ $tpl->escape($row['name']) }}" />
                            <div class="clearall"></div>
                        </div>
                        @php $i++; @endphp
                    @endforeach
                </div>

            </div>
        </div>

</form>
</div>
<script>
    jQuery(".noClickProp.dropdown-menu").on("click", function(e) {
        e.stopPropagation();
    });

    function accordionToggle(id) {
        let currentLink = jQuery("#accordion_toggle_"+id).find("i.fa");
        if (currentLink.hasClass("fa-angle-right")){
            currentLink.removeClass("fa-angle-right");
            currentLink.addClass("fa-angle-down");
            jQuery('#accordion_'+id).slideDown("fast");
        } else {
            currentLink.removeClass("fa-angle-down");
            currentLink.addClass("fa-angle-right");
            jQuery('#accordion_'+id).slideUp("fast");
        }
    }
</script>
