@extends($layout)
@section('content')

<x-global::layout.page-header icon="fa fa-cogs" :eyebrow="__('label.administration')">
    {!! __('headlines.company_settings') !!}
</x-global::layout.page-header>

<div class="maincontent">
    {!! $tpl->displayNotification() !!}
    <x-global::layout.content-box>
        <div class="row">
            <div class="col-md-12">

                <div class="tabbedwidget tab-primary companyTabs">

                    <ul>
                        <li><a href="#details"><span class="fa fa-building"></span> {!! __('tabs.details') !!}</a></li>
                        <li><a href="#apiKeys"><i class="fa-solid fa-key"></i> {!! __('tabs.apiKeys') !!}</a></li>
                        @dispatchEvent('tabs')
                    </ul>


                    <div id="details">


                        <div class="row">
                            <div class="col-md-8">
                                <form class="" method="post" id="" action="{{ BASE_URL }}/setting/editCompanySettings#details" >
                                    <p>{!! __('text.these_are_system_wide_settings') !!}</p>
                                    <br />
                                    <input type="hidden" value="1" name="saveSettings" />

                                    <x-global::elements.section-header icon="fa fa-building">{!! __('subtitles.companydetails') !!}</x-global::elements.section-header>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <x-global::forms.label>{!! __('label.language') !!}</x-global::forms.label>
                                        </div>
                                        <div class="col-md-8">
                                            <x-global::forms.select name="language" id="language">
                                                @foreach ($languageList as $languagKey => $languageValue)
                                                    <option
                                                        value="{{ $languagKey }}"
                                                        @if ($companySettings['language'] == $languagKey) selected='selected' @endif>{{ $languageValue }}</option>
                                                @endforeach
                                            </x-global::forms.select>


                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <x-global::forms.label>{!! __('label.company_name') !!}</x-global::forms.label>
                                        </div>
                                        <div class="col-md-8">
                                            <x-global::forms.text-input name="name" id="companyName"  value="{{ $companySettings['name'] }}" class="pull-left" />
                                            <small>{!! __('text.company_name_helper') !!}</small>
                                        </div>
                                    </div>

                                    @dispatchEvent('beforeTelemetrySettings')

                                    @if ($companySettings['telemetryAllowedByConfig'])
                                        <div class="row" id="telemetryContainer">
                                            <div class="col-md-2">
                                                <label for="telemetryActive">{!! __('label.send_telemetry') !!}</label>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="hidden" name="telemetryToggleShown" value="1" />
                                                <x-global::forms.checkbox variant="toggle" name="telemetryActive" id="telemetryActive" :checked="(bool) $companySettings['telemetryActive']" :aria-label="strip_tags(__('label.send_telemetry'))" />
                                                <br />
                                                <small>{!! __('label.telemetry_background') !!}</small>
                                            </div>
                                        </div>
                                    @endif
                                    <br />
                                    <x-global::elements.section-header icon="fa fa-cog">{!! __('subtitles.defaults') !!}</x-global::elements.section-header>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <x-global::forms.label for="messageFrequency">{!! __('label.messages_frequency') !!}</x-global::forms.label>
                                        </div>
                                        <div class="col-md-8">
                                                            <span class='field'>
                                                                <x-global::forms.select name="messageFrequency" class="input" id="messageFrequency">
                                                                    <option value="">--{!! __('label.choose_option') !!}--</option>
                                                                    <option value="300" @if ($companySettings['messageFrequency'] == '300') selected @endif>{!! __('label.5min') !!}</option>
                                                                    <option value="900" @if ($companySettings['messageFrequency'] == '900') selected @endif>{!! __('label.15min') !!}</option>
                                                                    <option value="1800" @if ($companySettings['messageFrequency'] == '1800') selected @endif>{!! __('label.30min') !!}</option>
                                                                    <option value="3600" @if ($companySettings['messageFrequency'] == '3600') selected @endif>{!! __('label.1h') !!}</option>
                                                                    <option value="10800" @if ($companySettings['messageFrequency'] == '10800') selected @endif>{!! __('label.3h') !!}</option>
                                                                    <option value="36000" @if ($companySettings['messageFrequency'] == '36000') selected @endif>{!! __('label.6h') !!}</option>
                                                                    <option value="43200" @if ($companySettings['messageFrequency'] == '43200') selected @endif>{!! __('label.12h') !!}</option>
                                                                    <option value="86400" @if ($companySettings['messageFrequency'] == '86400') selected @endif>{!! __('label.24h') !!}</option>
                                                                    <option value="172800" @if ($companySettings['messageFrequency'] == '172800') selected @endif>{!! __('label.48h') !!}</option>
                                                                    <option value="604800" @if ($companySettings['messageFrequency'] == '604800') selected @endif>{!! __('label.1w') !!}</option>
                                                                </x-global::forms.select> <br/>
                                                            </span>
                                        </div>
                                    </div>
                                    <br />
                                    <x-global::elements.section-header icon="fa fa-bell">{!! __('label.default_notification_types') !!}</x-global::elements.section-header>
                                    <p>{!! __('label.default_notification_types_description') !!}</p>
                                    <div class="row">
                                        <div class="col-md-8">
                                            @php
                                                $categoryLabels = [
                                                    'tasks' => 'label.notification_category_tasks',
                                                    'comments' => 'label.notification_category_comments',
                                                    'goals' => 'label.notification_category_goals',
                                                    'ideas' => 'label.notification_category_ideas',
                                                    'projects' => 'label.notification_category_projects',
                                                    'boards' => 'label.notification_category_boards',
                                                ];
                                            @endphp
                                            @foreach ($notificationCategories as $categoryKey => $config)
                                                <div class="form-group">
                                                    <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; padding:4px 0;">
                                                        <input type="checkbox"
                                                               name="defaultNotificationEventTypes[]"
                                                               value="{{ $categoryKey }}"
                                                               style="margin-top:3px;"
                                                               @if (in_array($categoryKey, $defaultNotificationTypes)) checked="checked" @endif
                                                        />
                                                        <span>
                                                            <strong>{!! __($categoryLabels[$categoryKey] ?? $categoryKey) !!}</strong><br />
                                                            <small style="color:#888;">{!! __($config['description'] ?? '') !!}</small>
                                                        </span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <br />
                                    <x-global::elements.section-header icon="fa fa-sliders">{!! __('label.default_notification_relevance') !!}</x-global::elements.section-header>
                                    <p>{!! __('label.default_notification_relevance_description') !!}</p>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <x-global::forms.select name="defaultNotificationRelevance" class="form-control">
                                                    @foreach ($relevanceLevels as $level => $labelKey)
                                                        <option value="{{ $level }}" @if ($defaultRelevance === $level) selected @endif>
                                                            {!! __($labelKey) !!}
                                                        </option>
                                                    @endforeach
                                                </x-global::forms.select>
                                            </div>
                                        </div>
                                    </div>
                                    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" id="saveBtn" />
                                </form>
                            </div>
                            <div class="col-md-4">

                                <form class="" method="post" id="" action="{{ BASE_URL }}/setting/editCompanySettings" >
                                    <input type="hidden" value="1" name="saveLogo" />
                                    <x-global::elements.section-header as="h5">{!! __('headlines.logo') !!}</x-global::elements.section-header>
                                    <br />

                                    <div class="row">

                                        <div class="col-md-12">
                                            @if ($companySettings['logo'] != '')
                                            <img src='{{ $companySettings['logo'] }}'  class='logoImg' alt='{{ __('label.logo') }}' id="previousImage" width="260"/>
                                            @else
                                                {!! __('text.no_logo') !!}
                                            @endif
                                            <div id="logoImg" style="height:auto;">
                                            </div>
                                            <br />
                                            <div class="par">

                                                <x-global::forms.label>{!! __('label.upload_new_logo') !!}</x-global::forms.label>

                                                <div class='fileupload fileupload-new' data-provides='fileupload'>
                                                    <input type="hidden"/>
                                                    <div class="input-append">
                                                        <div class="uneditable-input span3">
                                                            <i class="fa-file fileupload-exists"></i>
                                                            <span class="fileupload-preview"></span>
                                                        </div>
                                                        <span class="btn btn-default btn-file">
                                                            <span class="fileupload-new">{!! __('buttons.select_file') !!}</span>
                                                            <span class='fileupload-exists'>{!! __('buttons.change') !!}</span>
                                                            <input type='file' name='file' onchange="leantime.settingController.readURL(this)" />
                                                        </span>

                                                        <a href='#' style="margin-left:5px;" class='btn btn-default fileupload-exists' data-dismiss='fileupload' onclick="leantime.usersController.clearCroppie()">{!! __('buttons.remove') !!}</a>
                                                    </div>
                                                    <p class='stdformbutton'>
                                                        <span id="save-logo" class="btn btn-primary fileupload-exists ld-ext-right">
                                                            <span onclick="leantime.settingController.saveCroppie()">{!! __('buttons.save') !!}</span>
                                                            <span class="ld ld-ring ld-spin"> </span>
                                                        </span>

                                                        <input id="picSubmit" type="submit" name="savePic" class="hidden" value="{{ __('buttons.upload') }}" />
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <hr />
                                {!! __('text.logo_reset') !!}<br /><br />
                                <x-global::forms.button tag="a" link="{{ BASE_URL }}/setting/editCompanySettings?resetLogo=1" contentRole="default">{!! __('buttons.reset_logo') !!}</x-global::forms.button>
                            </div>
                        </div>
                </div>

                    <div id="apiKeys">
                        <x-global::forms.button tag="a" link="#/api/newApiKey" contentRole="primary">{{ __('buttons.generate_api_key') }}</x-global::forms.button>
                        <br /> <br />
                        <ul class="sortableTicketList">


                        @foreach ($apiKeys as $apiKey)
                            <li>
                                <x-global::elements.card compact class="apiKeyCard" style="margin-bottom:10px;">
                                      <x-global::actions.dropdown>
                                          <li><a href="#/api/apiKey/{{ $apiKey['id'] }}"><i class="fa fa-edit"></i> {{ __('links.edit_api_key') }}</a></li>
                                          <li><a href="{{ BASE_URL }}/api/delAPIKey/{{ $apiKey['id'] }}" class="delete"><i class="fa fa-trash"></i> {{ __('links.delete_api_key') }}</a></li>

                                      </x-global::actions.dropdown>
                                    <a href="#/api/apiKey/{{ $apiKey['id'] }}"><strong>{{ $apiKey['firstname'] }}</strong></a><br />
                                    lt_{{ $apiKey['username'] }}***
                                    | {!! __('labels.created_on') !!}: {{ format($apiKey['createdOn'])->date() }} | {!! __('labels.last_used') !!}: {{ format($apiKey['lastlogin'])->date() }}

                                </x-global::elements.card>
                            </li>
                        @endforeach
                        </ul>

                    </div>

                    @dispatchEvent('tabsContent')

            </div>
        </div>
    </div>
    </x-global::layout.content-box>




</div>

@once
@push('scripts')
<script>
    jQuery(document).ready(function() {
        jQuery(".companyTabs").tabs({
            activate: function (event, ui) {

                window.location.hash = ui.newPanel.selector;
            }
        });
    });
</script>
@endpush
@endonce

@endsection
