@php
    $companySettings = $tpl->get('companySettings');
@endphp

<x-globals::layout.page-header icon="settings" headline="{{ __('headlines.company_settings') }}" subtitle="{{ __('label.administration') }}" />

<div class="maincontent">
    {!! $tpl->displayNotification() !!}
    <div class="maincontentinner">
                <div class="row"><div class="col-md-12">
                <div class="tabbedwidget tab-primary companyTabs" data-tabs>

                    <ul role="tablist">
                        <li><a href="#details"><x-globals::elements.icon name="apartment" /> {{ __('tabs.details') }}</a></li>
                        <li><a href="#apiKeys"><x-globals::elements.icon name="key" /> {{ __('tabs.apiKeys') }}</a></li>
                        @dispatchEvent('tabs')
                    </ul>

                    <div id="details">

                        <div class="row">
                            <div class="col-md-8">
                                <form class="" method="post" id="" action="{{ BASE_URL }}/setting/editCompanySettings#details">
                                    <p>{{ __('text.these_are_system_wide_settings') }}</p>
                                    <br />
                                    <input type="hidden" value="1" name="saveSettings" />

                                    <x-globals::elements.section-title icon="apartment">{{ __('subtitles.companydetails') }}</x-globals::elements.section-title>
                                    <x-globals::forms.form-field label-text="{{ __('label.language') }}" name="language" label-position="left" label-width="tw:w-[150px]">
                                        <x-globals::forms.select :bare="true" name="language" id="language">
                                            @foreach($tpl->get('languageList') as $languagKey => $languageValue)
                                                <option value="{{ $languagKey }}"
                                                    {{ $companySettings['language'] == $languagKey ? "selected='selected'" : '' }}>{{ $languageValue }}</option>
                                            @endforeach
                                        </x-globals::forms.select>
                                    </x-globals::forms.form-field>

                                    <x-globals::forms.form-field label-text="{{ __('label.company_name') }}" name="companyName" label-position="left" label-width="tw:w-[150px]" caption="{{ __('text.company_name_helper') }}">
                                        <x-globals::forms.text-input :bare="true" name="name" id="companyName" value="{{ $companySettings['name'] }}" />
                                    </x-globals::forms.form-field>

                                    <x-globals::elements.section-title icon="settings">{{ __('subtitles.defaults') }}</x-globals::elements.section-title>

                                    <x-globals::forms.form-field label-text="{{ __('label.messages_frequency') }}" name="messageFrequency" label-position="left" label-width="tw:w-[150px]">
                                        <x-globals::forms.select :bare="true" name="messageFrequency" id="messageFrequency" class="tw:w-56">
                                            <option value="">--{{ __('label.choose_option') }}--</option>
                                            <option value="300" {{ $companySettings['messageFrequency'] == '300' ? ' selected ' : '' }}>{{ __('label.5min') }}</option>
                                            <option value="900" {{ $companySettings['messageFrequency'] == '900' ? ' selected ' : '' }}>{{ __('label.15min') }}</option>
                                            <option value="1800" {{ $companySettings['messageFrequency'] == '1800' ? ' selected ' : '' }}>{{ __('label.30min') }}</option>
                                            <option value="3600" {{ $companySettings['messageFrequency'] == '3600' ? ' selected ' : '' }}>{{ __('label.1h') }}</option>
                                            <option value="10800" {{ $companySettings['messageFrequency'] == '10800' ? ' selected ' : '' }}>{{ __('label.3h') }}</option>
                                            <option value="36000" {{ $companySettings['messageFrequency'] == '36000' ? ' selected ' : '' }}>{{ __('label.6h') }}</option>
                                            <option value="43200" {{ $companySettings['messageFrequency'] == '43200' ? ' selected ' : '' }}>{{ __('label.12h') }}</option>
                                            <option value="86400" {{ $companySettings['messageFrequency'] == '86400' ? ' selected ' : '' }}>{{ __('label.24h') }}</option>
                                            <option value="172800" {{ $companySettings['messageFrequency'] == '172800' ? ' selected ' : '' }}>{{ __('label.48h') }}</option>
                                            <option value="604800" {{ $companySettings['messageFrequency'] == '604800' ? ' selected ' : '' }}>{{ __('label.1w') }}</option>
                                        </x-globals::forms.select>
                                    </x-globals::forms.form-field>
                                    <x-globals::forms.button :submit="true" contentRole="primary" id="saveBtn">{{ __('buttons.save') }}</x-globals::forms.button>
                                </form>
                            </div>
                            <div class="col-md-4">

                                <form class="" method="post" id="" action="{{ BASE_URL }}/setting/editCompanySettings">
                                    <input type="hidden" value="1" name="saveLogo" />
                                    <x-globals::elements.section-title tag="h5">{{ __('headlines.logo') }}</x-globals::elements.section-title>
                                    <br />

                                    <div class="row"><div class="col-md-12">
                                            @if($companySettings['logo'] != '')
                                                <img src='{{ $companySettings['logo'] }}' class='logoImg' alt='Logo' id="previousImage" width="260"/>
                                            @else
                                                {{ __('text.no_logo') }}
                                            @endif
                                            <div id="logoImg">
                                            </div>
                                            <br />
                                            <div class="par">

                                                <label for="logo-file-input">{{ __('label.upload_new_logo') }}</label>

                                                <div class='fileupload fileupload-new' data-provides='fileupload'>
                                                    <input type="hidden"/>
                                                    <div class="input-append">
                                                        <div class="uneditable-input span3">
                                                            <i class="fa-file fileupload-exists"></i>
                                                            <span class="fileupload-preview"></span>
                                                        </div>
                                                        <span class="btn btn-default btn-file">
                                                            <span class="fileupload-new">{{ __('buttons.select_file') }}</span>
                                                            <span class='fileupload-exists'>{{ __('buttons.change') }}</span>
                                                            <x-globals::forms.file :bare="true" id="logo-file-input" name="file" onchange="leantime.settingController.readURL(this)" />
                                                        </span>

                                                        <x-globals::forms.button element="a" href="#" contentRole="secondary" class="fileupload-exists tw:ml-1" data-dismiss="fileupload" onclick="leantime.usersController.clearCroppie()">{{ __('buttons.remove') }}</x-globals::forms.button>
                                                    </div>
                                                    <p>
                                                        <x-globals::forms.button contentRole="primary" id="save-logo" class="fileupload-exists" onclick="leantime.settingController.saveCroppie()">{{ __('buttons.save') }}</x-globals::forms.button>

                                                        <input id="picSubmit" type="submit" name="savePic" class="tw:hidden" value="{{ __('buttons.upload') }}" />
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <hr />
                                {{ __('text.logo_reset') }}<br /><br />
                                <x-globals::forms.button element="a" href="{{ BASE_URL }}/setting/editCompanySettings?resetLogo=1" contentRole="secondary">{{ __('buttons.reset_logo') }}</x-globals::forms.button>
                            </div>
                        </div>
                    </div>

                    <div id="apiKeys">
                        <x-globals::forms.button element="a" href="#/api/newApiKey" contentRole="primary">Generate API Key</x-globals::forms.button>
                        <br /> <br />
                        <ul class="sortableTicketList">
                            @foreach($tpl->get('apiKeys') as $apiKey)
                                <li>
                                    <div class="ticketBox">
                                        <x-globals::actions.dropdown-menu>
                                            <x-globals::actions.dropdown-item href="#/api/apiKey/{{ $apiKey['id'] }}" leadingVisual="edit">Edit Key</x-globals::actions.dropdown-item>
                                            <x-globals::actions.dropdown-item href="{{ BASE_URL }}/api/delAPIKey/{{ $apiKey['id'] }}" leadingVisual="delete" state="danger">Delete Key</x-globals::actions.dropdown-item>
                                        </x-globals::actions.dropdown-menu>
                                        <a href="#/api/apiKey/{{ $apiKey['id'] }}"><strong>{{ e($apiKey['firstname']) }}</strong></a><br />
                                        lt_{{ $apiKey['username'] }}***
                                        | {{ __('labels.created_on') }}: {{ format($apiKey['createdOn'])->date() }} | {{ __('labels.last_used') }}: {{ format($apiKey['lastlogin'])->date() }}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    @dispatchEvent('tabsContent')

                </div>
            </div>
        </div>
    </div>
</div>

