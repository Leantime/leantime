@php
    $project = $tpl->get('project');
@endphp

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-suitcase"></span></div>
    <div class="pagetitle">
        <h5>{{ __('label.administration') }}</h5>
        <h1>{{ __('headline.new_project') }}</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="lt-tabs tabbedwidget projectTabs" data-tabs>

            <ul role="tablist">
                <li><a href="#projectdetails">{{ __('tabs.projectdetails') }}</a></li>
            </ul>

            <div id="projectdetails">
                <form action="" method="post" class="">

                    <div class="row">

                        <div class="col-md-8">
                            <div class="form-group">
                                <x-global::forms.input :bare="true" type="text" name="name" id="name" class="main-title-input" style="width:99%" value="{{ e($project['name']) }}" placeholder="{{ __('input.placeholders.enter_title_of_project') }}" />
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
                                        <a href="{{ BASE_URL }}/projects/delProject/{{ $project['id'] }}" class="delete"><i class="fa fa-trash"></i> {{ __('buttons.delete') }}</a>
                                    </div>
                                @endif
                                <x-global::button submit type="primary" name="save" id="save">{{ __('buttons.save') }}</x-global::button>
                            </div>
                        </div>

                        <div class="col-md-4">

                            @if($tpl->get('projectTypes') && count($tpl->get('projectTypes')) > 1)
                                <h4 class="widgettitle title-light"><i class="fa-regular fa-rectangle-list"></i> Project Type</h4>
                                <p>The type of the project. This will determine which features are available.</p>
                                <x-global::forms.select :bare="true" name="type">
                                    @foreach($tpl->get('projectTypes') as $key => $type)
                                        <option value="{{ e($key) }}"
                                            {{ $project['type'] == $key ? "selected='selected'" : '' }}>{{ __(e($type)) }}</option>
                                    @endforeach
                                </x-global::forms.select>
                                <br /><br />
                            @endif

                            @dispatchEvent('beforeClientPicker', $project)

                            <div style="margin-bottom: 30px;">
                                <h4 class="widgettitle title-light tw:block"><span
                                        class="fa fa-calendar"></span>{{ __('label.project_dates') }}</h4>
                                <div>
                                    <label>{{ __('label.project_start') }}</label>
                                    <div class="">
                                        <x-global::forms.date name="start" value="{{ $project['start'] }}" placeholder="{{ __('language.dateformat') }}" style="width:100px;" class="dateFrom" />
                                    </div>
                                    <label>{{ __('label.project_end') }}</label>
                                    <div class="">
                                        <x-global::forms.date name="end" value="{{ $project['end'] }}" placeholder="{{ __('language.dateformat') }}" style="width:100px;" class="dateTo" />
                                    </div>
                                </div>
                            </div>

                            <div style="margin-bottom: 30px;">
                                <div class="">
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-building"></span>{{ __('label.client_product') }}</h4>
                                    <x-global::forms.select :bare="true" name="clientId" id="clientId">
                                        @foreach($tpl->get('clients') as $row)
                                            <option value="{{ $row['id'] }}"
                                                {{ $project['clientId'] == $row['id'] ? 'selected=selected' : '' }}>{{ e($row['name']) }}</option>
                                        @endforeach
                                    </x-global::forms.select>
                                    @if($login::userIsAtLeast('manager'))
                                        <br /><a href="{{ BASE_URL }}/clients/newClient" target="_blank">{{ __('label.client_not_listed') }}</a>
                                    @endif
                                </div>
                            </div>

                            <div style="margin-bottom: 30px;">
                                <div class="">
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-lock-open"></span>{{ __('labels.defaultaccess') }}</h4>
                                    {{ __('text.who_can_access') }}
                                    <br /><br />

                                    <x-global::forms.select :bare="true" name="globalProjectUserAccess" style="max-width:300px;">
                                        <option value="restricted" {{ $project['psettings'] == 'restricted' ? "selected='selected'" : '' }}>{{ __('labels.only_chose') }}</option>
                                        <option value="clients" {{ $project['psettings'] == 'clients' ? "selected='selected'" : '' }}>{{ __('labels.everyone_in_client') }}</option>
                                        <option value="all" {{ $project['psettings'] == 'all' ? "selected='selected'" : '' }}>{{ __('labels.everyone_in_org') }}</option>
                                    </x-global::forms.select>

                                </div>
                            </div>

                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery("#projectdetails select").chosen();
        leantime.dateController.initDateRangePicker(".dateFrom", ".dateTo", 2);

        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initComplexEditor();
        }
    });
</script>
