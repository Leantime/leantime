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
        <div class="row">
            <div class="col-md-12">

                <div class="tabbedwidget tab-primary companyTabs">

                    <ul>
                        <li><a href="#details"><span class="fa fa-building"></span> {{ __('tabs.details') }}</a></li>
                        <li><a href="#apiKeys"><i class="fa-solid fa-key"></i> {{ __('tabs.apiKeys') }}</a></li>
                        @dispatchEvent('tabs')
                    </ul>

                    <div id="details">

                        <div class="row">
                            <div class="col-md-8">
                                <form class="" method="post" id="" action="{{ BASE_URL }}/setting/editCompanySettings#details">
                                    <p>{{ __('text.these_are_system_wide_settings') }}</p>
                                    <br />
                                    <input type="hidden" value="1" name="saveSettings" />

                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-building"></span>{{ __('subtitles.companydetails') }}
                                    </h4>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>{{ __('label.language') }}</label>
                                        </div>
                                        <div class="col-md-8">
                                            <select name="language" id="language">
                                                @foreach($tpl->get('languageList') as $languagKey => $languageValue)
                                                    <option value="{{ $languagKey }}"
                                                        {{ $companySettings['language'] == $languagKey ? "selected='selected'" : '' }}>{{ $languageValue }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>{{ __('label.company_name') }}</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" name="name" id="companyName" value="{{ $companySettings['name'] }}" class="pull-left"/>
                                            <small>{{ __('text.company_name_helper') }}</small>
                                        </div>
                                    </div>
                                    <br />
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-cog"></span>{{ __('subtitles.defaults') }}
                                    </h4>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label for="messageFrequency">{{ __('label.messages_frequency') }}</label>
                                        </div>
                                        <div class="col-md-8">
                                            <span class='field'>
                                                <select name="messageFrequency" class="input" id="messageFrequency" style="width: 220px">
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
                                                </select> <br/>
                                            </span>
                                        </div>
                                    </div>
                                    <input type="submit" value="{{ __('buttons.save') }}" id="saveBtn"/>
                                </form>
                            </div>
                            <div class="col-md-4">

                                <form class="" method="post" id="" action="{{ BASE_URL }}/setting/editCompanySettings">
                                    <input type="hidden" value="1" name="saveLogo" />
                                    <h5 class="widgettitle title-light">{{ __('headlines.logo') }}</h5>
                                    <br />

                                    <div class="row">
                                        <div class="col-md-12">
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
                                                            <input type='file' name='file' onchange="leantime.settingController.readURL(this)" />
                                                        </span>

                                                        <a href='#' style="margin-left:5px;" class='btn btn-default fileupload-exists' data-dismiss='fileupload' onclick="leantime.usersController.clearCroppie()">{{ __('buttons.remove') }}</a>
                                                    </div>
                                                    <p class='stdformbutton'>
                                                        <span id="save-logo" class="btn btn-primary fileupload-exists ld-ext-right">
                                                            <span onclick="leantime.settingController.saveCroppie()">{{ __('buttons.save') }}</span>
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
                                {{ __('text.logo_reset') }}<br /><br />
                                <a href="{{ BASE_URL }}/setting/editCompanySettings?resetLogo=1" class="btn btn-default">{{ __('buttons.reset_logo') }}</a>
                            </div>
                        </div>
                    </div>

                    <div id="apiKeys">
                        <a href="#/api/newApiKey" class="btn btn-primary">Generate API Key</a>
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
