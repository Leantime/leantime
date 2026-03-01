@php
    $status = $tpl->get('status');
    $values = $tpl->get('values');
    $projects = $tpl->get('relations');
    $apiKeyValues = $tpl->get('apiKeyValues');
@endphp

<div style="min-width:700px;">

<h4 class="widgettitle title-light"><x-global::elements.icon name="key" /> {{ $tpl->__('headlines.new_api_key') }}</h4>

{!! $tpl->displayNotification() !!}
    @if ($apiKeyValues !== false && isset($apiKeyValues['id']))
        <p>Your API Key was successfully created. Please copy the key below. This is your only chance to copy it.</p>
        <x-globals::forms.input id="apiKey" name="apiKey" value="lt_{{ $apiKeyValues['user'] }}_{{ $apiKeyValues['passwordClean'] }}" style="width:100%;" />
        <x-globals::forms.button tag="button" type="primary" onclick="leantime.snippets.copyUrl('apiKey');">{{ $tpl->__('links.copy_key') }}</x-globals::forms.button>
    @else
    <form action="{{ BASE_URL }}/api/newApiKey" method="post" class="stdform formModal">

        <input type="hidden" name="save" value="1" />

        <div class="row">
            <div class="col-md-6">

                <h4 class="widgettitle title-light">{{ $tpl->__('label.basic_information') }}</h4>

                <label for="firstname">{{ $tpl->__('label.key_name') }}</label><div class="clearfix"></div>
                    <x-globals::forms.input name="firstname" id="firstname" value="" /><br />


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
                                <a href='#' onclick='accordionToggle(" . $i . ");' id='accordion_toggle_" . $i . "'><x-global::elements.icon name="expand_more" /> " . $tpl->escape($row['clientName']) . "</a>
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
    @endif
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
