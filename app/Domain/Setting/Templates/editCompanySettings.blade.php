@php
    $companySettings = $tpl->get('companySettings');
@endphp

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-cogs"></span></div>
    <div class="pagetitle">
        <h5>{{ __('label.administration') }}</h5>
        <h1>{{ __('headlines.company_settings') }}</h1>
    </div>
</div>

<div class="maincontent">
    {!! $tpl->displayNotification() !!}
    <div class="maincontentinner">
                <div class="tabbedwidget tab-primary companyTabs">

                    <ul>
                        <li><a href="#details"><span class="fa fa-building"></span> {{ __('tabs.details') }}</a></li>
                        <li><a href="#apiKeys"><i class="fa-solid fa-key"></i> {{ __('tabs.apiKeys') }}</a></li>
                        @dispatchEvent('tabs')
                    </ul>

                    <div id="details">

                        <div class="tw:grid tw:grid-cols-[2fr_1fr] tw:gap-6">
                            <div>
                                <form class="" method="post" id="" action="{{ BASE_URL }}/setting/editCompanySettings#details">
                                    <p>{{ __('text.these_are_system_wide_settings') }}</p>
                                    <br />
                                    <input type="hidden" value="1" name="saveSettings" />

                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-building"></span>{{ __('subtitles.companydetails') }}
                                    </h4>
                                    <div class="tw:grid tw:grid-cols-[1fr_4fr] tw:gap-6">
                                        <div>
                                            <label>{{ __('label.language') }}</label>
                                        </div>
                                        <div>
                                            <x-global::forms.select name="language" id="language">
                                                @foreach($tpl->get('languageList') as $languagKey => $languageValue)
                                                    <option value="{{ $languagKey }}"
                                                        {{ $companySettings['language'] == $languagKey ? "selected='selected'" : '' }}>{{ $languageValue }}</option>
                                                @endforeach
                                            </x-global::forms.select>
                                        </div>
                                    </div>

                                    <div class="tw:grid tw:grid-cols-[1fr_4fr] tw:gap-6">
                                        <div>
                                            <label>{{ __('label.company_name') }}</label>
                                        </div>
                                        <div>
                                            <x-global::forms.input name="name" id="companyName" value="{{ $companySettings['name'] }}" class="tw:float-left" />
                                            <small>{{ __('text.company_name_helper') }}</small>
                                        </div>
                                    </div>
                                    <br />
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-cog"></span>{{ __('subtitles.defaults') }}
                                    </h4>
                                    <div class="tw:grid tw:grid-cols-[1fr_4fr] tw:gap-6">
                                        <div>
                                            <label for="messageFrequency">{{ __('label.messages_frequency') }}</label>
                                        </div>
                                        <div>
                                            <span class='field'>
                                                <x-global::forms.select name="messageFrequency" id="messageFrequency" style="width: 220px">
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
                                                </x-global::forms.select> <br/>
                                            </span>
                                        </div>
                                    </div>
                                    <x-global::button submit type="primary" id="saveBtn">{{ __('buttons.save') }}</x-global::button>
                                </form>
                            </div>
                            <div>

                                <form class="" method="post" id="" action="{{ BASE_URL }}/setting/editCompanySettings">
                                    <input type="hidden" value="1" name="saveLogo" />
                                    <h5 class="widgettitle title-light">{{ __('headlines.logo') }}</h5>
                                    <br />

                                    <div>
                                            @if($companySettings['logo'] != '')
                                                <img src='{{ $companySettings['logo'] }}' class='logoImg' alt='Logo' id="previousImage" width="260"/>
                                            @else
                                                {{ __('text.no_logo') }}
                                            @endif
                                            <div id="logoImg" style="height:auto;">
                                            </div>
                                            <br />
                                            <div class="par">

                                                <label>{{ __('label.upload_new_logo') }}</label>

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
                                                            <x-global::forms.file :bare="true" name="file" onchange="leantime.settingController.readURL(this)" />
                                                        </span>

                                                        <x-global::button link="#" type="secondary" class="fileupload-exists" style="margin-left:5px;" data-dismiss="fileupload" onclick="leantime.usersController.clearCroppie()">{{ __('buttons.remove') }}</x-global::button>
                                                    </div>
                                                    <p class='stdformbutton'>
                                                        <x-global::button tag="button" type="primary" id="save-logo" class="fileupload-exists ld-ext-right" onclick="leantime.settingController.saveCroppie()">{{ __('buttons.save') }}<span class="ld ld-ring ld-spin"> </span></x-global::button>

                                                        <input id="picSubmit" type="submit" name="savePic" class="hidden" value="{{ __('buttons.upload') }}" />
                                                    </p>
                                                </div>
                                            </div>
                                    </div>
                                </form>
                                <hr />
                                {{ __('text.logo_reset') }}<br /><br />
                                <x-global::button link="{{ BASE_URL }}/setting/editCompanySettings?resetLogo=1" type="secondary">{{ __('buttons.reset_logo') }}</x-global::button>
                            </div>
                        </div>
                    </div>

                    <div id="apiKeys">
                        <x-global::button link="#/api/newApiKey" type="primary">Generate API Key</x-global::button>
                        <br /> <br />
                        <ul class="sortableTicketList">
                            @foreach($tpl->get('apiKeys') as $apiKey)
                                <li>
                                    <div class="ticketBox">
                                        <div class="inlineDropDownContainer">
                                            <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <li><a href="#/api/apiKey/{{ $apiKey['id'] }}"><i class="fa fa-edit"></i> Edit Key</a></li>
                                                <li><a href="{{ BASE_URL }}/api/delAPIKey/{{ $apiKey['id'] }}" class="delete"><i class="fa fa-trash"></i> Delete Key</a></li>
                                            </ul>
                                        </div>
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

<script>
    jQuery(document).ready(function() {
        jQuery(".companyTabs").tabs({
            activate: function (event, ui) {
                window.location.hash = ui.newPanel.selector;
            }
        });
    });
</script>
