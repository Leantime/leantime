@extends($layout)

@section('content')

@php
    $apiKeyValues = $apiKeyValues ?? false;
@endphp

<div style="min-width:700px;">

<x-global::actions.modal-header>
    <x-slot:title><i class="fa fa-key"></i> {!! __('headlines.new_api_key') !!}</x-slot:title>
</x-global::actions.modal-header>{!! $tpl->displayNotification() !!}
    @if ($apiKeyValues !== false && isset($apiKeyValues['id']))
        <p>{{ __('text.api_key_created_copy_now') }}</p>
        <x-global::forms.text-input width="full" id="apiKey" value="lt_{{ $apiKeyValues['user'] }}_{{ $apiKeyValues['passwordClean'] }}" />
        <x-global::forms.button contentRole="primary" onclick="leantime.snippets.copyUrl('apiKey');">{!! __('links.copy_key') !!}</x-global::forms.button>
    @else
    <form action="{{ BASE_URL }}/api/newApiKey" method="post" class="stdform formModal" >

        <input type="hidden" name="save" value="1" />

        <div class="row" >
            <div class="col-md-6">

                <x-global::elements.section-header>{!! __('label.basic_information') !!}</x-global::elements.section-header>

                <x-global::forms.label for="firstname">{!! __('label.key_name') !!}</x-global::forms.label><div class="clearfix"></div>
                    <x-global::forms.text-input
                    name="firstname" id="firstname"
                    value="" /><br />


                <x-global::forms.label for="role">{!! __('label.role') !!}</x-global::forms.label><div class="clearfix"></div>
                <x-global::forms.select name="role" id="role">

                    @foreach ($roles as $key => $role)
                        <option value="{{ $key }}"
                            @if ($key == $values['role'])
                                selected="selected"
                            @endif
                        >
                            {{ \Leantime\Domain\Auth\Models\Roles::label($key) }}
                        </option>
                    @endforeach

                </x-global::forms.select> <br />

                <x-global::forms.label for="status">{!! __('label.status') !!}</x-global::forms.label><div class="clearfix"></div>
                <x-global::forms.select name="status" id="status">
                    <option value="a"
                        @if (strtolower($values['status']) == 'a')
                            selected="selected"
                        @endif
                    >
                        {!! __('label.active') !!}
                    </option>

                    <option value=""
                        @if (strtolower($values['status']) == '')
                            selected="selected"
                        @endif
                    >
                        {!! __('label.deactivated') !!}
                    </option>

                </x-global::forms.select>

                    <div class="clearfix"></div>

                <p class="stdformbutton">
                    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" id="save" />
                </p>

            </div>
            <div class="col-md-6">

                <x-global::elements.section-header>{!! __('label.project_access') !!}</x-global::elements.section-header>

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
                                <div class="item">
                                    <input type="checkbox" name="projects[]" id="project_{{ $row['id'] }}" value="{{ $row['id'] }}"
                                        @if (is_array($relations) === true && in_array($row['id'], $relations) === true)
                                            checked="checked"
                                        @endif
                                    /><x-global::forms.label for="project_{{ $row['id'] }}">{{ $tpl->escape($row['name']) }}</x-global::forms.label>
                                    <div class="clearall"></div>
                                </div>
                            @endforeach
                        </x-global::elements.accordion>
                    @endforeach
                </div>

            </div>
        </div>
    @endif
</form>
</div>


@endsection
