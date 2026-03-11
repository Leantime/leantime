@php
    $project = $tpl->get('project');
@endphp

<x-globals::layout.page-header icon="luggage" subtitle="{{ __('label.administration') }}" headline="{{ __('headline.new_project') }}" />

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <x-globals::navigations.tabs persist="url">
            <x-slot:headings>
                <x-globals::navigations.tabs.heading name="projectdetails">{{ __('tabs.projectdetails') }}</x-globals::navigations.tabs.heading>
            </x-slot:headings>
            <x-slot:contents>
                <x-globals::navigations.tabs.content name="projectdetails">
                    <form action="" method="post" class="">

                        <div class="row">

                            <div class="col-md-8">
                                <div class="form-group">
                                    <x-globals::forms.text-input :bare="true" type="text" name="name" id="name" class="main-title-input tw:w-full" value="{{ e($project['name']) }}" placeholder="{{ __('input.placeholders.enter_title_of_project') }}" />
                                </div>
                                <input type="hidden" name="projectState" id="projectState" value="0" />

                                <br />
                                <p>
                                    {{ __('label.accomplish') }}
                                    {{ __('label.describe_outcome') }}
                                    <br /><br />
                                </p>
                                <textarea name="details" id="details" class="tiptapComplex" rows="5" cols="50">{!! htmlentities($project['details']) !!}</textarea>

                                <div class="padding-top">
                                    @if(isset($project['id']) && $project['id'] != '')
                                        <div class="pull-right padding-top">
                                            <a href="{{ BASE_URL }}/projects/delProject/{{ $project['id'] }}" class="delete"><x-globals::elements.icon name="delete" /> {{ __('buttons.delete') }}</a>
                                        </div>
                                    @endif
                                    <x-globals::forms.button submit type="primary" name="save" id="save">{{ __('buttons.save') }}</x-globals::forms.button>
                                </div>
                            </div>

                            <div class="col-md-4">

                                @if($tpl->get('projectTypes') && count($tpl->get('projectTypes')) > 1)
                                    <x-globals::elements.section-title icon="view_list">Project Type</x-globals::elements.section-title>
                                    <p>The type of the project. This will determine which features are available.</p>
                                    <x-globals::forms.select :bare="true" name="type">
                                        @foreach($tpl->get('projectTypes') as $key => $type)
                                            <option value="{{ e($key) }}"
                                                {{ $project['type'] == $key ? "selected='selected'" : '' }}>{{ __(e($type)) }}</option>
                                        @endforeach
                                    </x-globals::forms.select>
                                    <br /><br />
                                @endif

                                @dispatchEvent('beforeClientPicker', $project)

                                <div class="tw:mb-8">
                                    <x-globals::elements.section-title icon="calendar_today" class="tw:block">{{ __('label.project_dates') }}</x-globals::elements.section-title>
                                    <div>
                                        <label>{{ __('label.project_start') }}</label>
                                        <div class="">
                                            <x-globals::forms.date name="start" value="{{ $project['start'] }}" placeholder="{{ __('language.dateformat') }}" class="dateFrom" />
                                        </div>
                                        <label>{{ __('label.project_end') }}</label>
                                        <div class="">
                                            <x-globals::forms.date name="end" value="{{ $project['end'] }}" placeholder="{{ __('language.dateformat') }}" class="dateTo" />
                                        </div>
                                    </div>
                                </div>

                                <div class="tw:mb-8">
                                    <div class="">
                                        <x-globals::elements.section-title icon="apartment">{{ __('label.client_product') }}</x-globals::elements.section-title>
                                        <x-globals::forms.select :bare="true" name="clientId" id="clientId">
                                            @foreach($tpl->get('clients') as $row)
                                                <option value="{{ $row['id'] }}"
                                                    {{ $project['clientId'] == $row['id'] ? 'selected=selected' : '' }}>{{ e($row['name']) }}</option>
                                            @endforeach
                                        </x-globals::forms.select>
                                        @if($login::userIsAtLeast('manager'))
                                            <br /><a href="{{ BASE_URL }}/clients/newClient" target="_blank">{{ __('label.client_not_listed') }}</a>
                                        @endif
                                    </div>
                                </div>

                                <div class="tw:mb-8">
                                    <div class="">
                                        <x-globals::elements.section-title icon="lock_open">{{ __('labels.defaultaccess') }}</x-globals::elements.section-title>
                                        {{ __('text.who_can_access') }}
                                        <br /><br />

                                        <x-globals::forms.select :bare="true" name="globalProjectUserAccess" class="tw:max-w-xs">
                                            <option value="restricted" {{ $project['psettings'] == 'restricted' ? "selected='selected'" : '' }}>{{ __('labels.only_chose') }}</option>
                                            <option value="clients" {{ $project['psettings'] == 'clients' ? "selected='selected'" : '' }}>{{ __('labels.everyone_in_client') }}</option>
                                            <option value="all" {{ $project['psettings'] == 'all' ? "selected='selected'" : '' }}>{{ __('labels.everyone_in_org') }}</option>
                                        </x-globals::forms.select>

                                    </div>
                                </div>

                            </div>

                        </div>

                    </form>
                </x-globals::navigations.tabs.content>
            </x-slot:contents>
        </x-globals::navigations.tabs>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
        leantime.dateController.initDateRangePicker(".dateFrom", ".dateTo", 2);

        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initComplexEditor();
        }
    });
</script>
