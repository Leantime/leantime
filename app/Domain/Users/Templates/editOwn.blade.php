@extends($layout)

@section('content')

<x-globals::layout.page-header icon="person" headline="{{ __('headlines.accountSettings') }}" subtitle="{{ __('label.overview') }}" />

<div class="maincontent">

    {!! $tpl->displayNotification() !!}

    <div class="maincontentinner">
        <div class="lt-tabs tabbedwidget accountTabs" data-tabs data-tabs-persist="hash" hx-boost="false">

                    <ul role="tablist">
                        <li><a href="#myProfile">{!! __('tabs.myProfile') !!}</a></li>
                        <li><a href="#security">{!! __('tabs.security') !!}</a></li>
                        <li><a href="#settings">{!! __('tabs.settings') !!}</a></li>
                        <li><a href="#notifications">{!! __('tabs.notifications') !!}</a></li>
                        <li><a href="#theme">{!! __('tabs.theme') !!}</a></li>
                        @dispatchEvent('tabs')
                    </ul>

                    <div id="myProfile">
                        <div class="row">
                            <div class="col-md-8">
                                <form action="" method="post">
                                    <x-globals::elements.section-title><?php echo $tpl->__('label.profile_information'); ?></x-globals::elements.section-title>
                                    <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                                    <div class="row-fluid">
                                        <x-globals::forms.form-field label-text="{{ __('label.firstname') }}" name="firstname">
                                            <x-globals::forms.text-input :bare="true" name="firstname" id="firstname" :disabled="session('userdata.isExternalAuth')" value="{{ $values['firstname'] }}" />
                                        </x-globals::forms.form-field>

                                        <x-globals::forms.form-field label-text="{{ __('label.lastname') }}" name="lastname">
                                            <x-globals::forms.text-input :bare="true" name="lastname" id="lastname" :disabled="session('userdata.isExternalAuth')" value="{{ $values['lastname'] }}" />
                                        </x-globals::forms.form-field>

                                        <x-globals::forms.form-field label-text="{{ __('label.email') }}" name="user">
                                            <x-globals::forms.text-input :bare="true" name="user" id="user" :disabled="session('userdata.isExternalAuth')" value="{{ $values['user'] }}" />
                                        </x-globals::forms.form-field>

                                        <x-globals::forms.form-field label-text="{{ __('label.phone') }}" name="phone">
                                            <x-globals::forms.text-input :bare="true" name="phone" id="phone" :disabled="session('userdata.isExternalAuth')" value="{{ $values['phone'] }}" />
                                        </x-globals::forms.form-field>
                                        <p class='stdformbutton'>
                                            <input type="hidden" name="profileInfo" value="1" />

                                            <x-globals::forms.button submit type="primary" name="save" id="save">{{ __('buttons.save') }}</x-globals::forms.button>
                                        </p>
                                        <br />
                                        <x-globals::elements.section-title>{{ __('label.employee_information') }}</x-globals::elements.section-title>
                                        <em>{{ __('text.only_admins_can_change_user_info') }}</em><br /><br />
                                        <x-globals::forms.form-field label-text="{{ __('label.jobTitle') }}" name="jobTitle">
                                            <x-globals::forms.text-input :bare="true" name="jobTitle" id="jobTitle" :readonly="true" value="{{ $values['jobTitle'] }}" />
                                        </x-globals::forms.form-field>

                                        <x-globals::forms.form-field label-text="{{ __('label.jobLevel') }}" name="jobLevel">
                                            <x-globals::forms.text-input :bare="true" name="jobLevel" id="jobLevel" :readonly="true" value="{{ $values['jobLevel'] }}" />
                                        </x-globals::forms.form-field>

                                        <x-globals::forms.form-field label-text="{{ __('label.department') }}" name="department">
                                            <x-globals::forms.text-input :bare="true" name="department" id="department" :readonly="true" value="{{ $values['department'] }}" />
                                        </x-globals::forms.form-field>

                                    </div>


                                </form>
                            </div>
                            <div class="col-md-4">
                                <div class="center">
                                    <img src='{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}?v={{ format($user['modified'])->timestamp() }}'  class='profileImg tw:rounded-full' alt='Profile Picture' id="previousImage"/>
                                    <div id="profileImg">
                                    </div>

                                    <div class="par">

                                        <label>{{ __('label.upload') }}</label>

                                        <div class='fileupload fileupload-new' data-provides='fileupload'>
                                            <input type="hidden"/>
                                            <div class="input-append">
                                                <div class="uneditable-input span3">
                                                    <i class="fa-file fileupload-exists"></i>
                                                    <span class="fileupload-preview"></span>
                                                </div>
                                                <span class="btn btn-file">
                                        <span class="fileupload-new">{{ __('buttons.select_file') }}</span>
                                        <span class='fileupload-exists'>{{ __('buttons.change') }}</span>
                                        <x-globals::forms.file :bare="true" name="file" accept=".jpg,.png,.gif,.webp" onchange="leantime.usersController.readURL(this)" />
                                    </span>

                                                <x-globals::forms.button link="#" type="secondary" class="fileupload-exists" data-dismiss="fileupload" onclick="leantime.usersController.clearCroppie()">{{ __('buttons.remove') }}</x-globals::forms.button>
                                            </div>
                                            <p class='stdformbutton'>
                                    <x-globals::forms.button tag="button" type="primary" id="save-picture" class="fileupload-exists" onclick="leantime.usersController.saveCroppie()">{{ __('buttons.save') }}</x-globals::forms.button>
                                                <input type="hidden" name="profileImage" value="1" />
                                                <input id="picSubmit" type="submit" name="savePic" class="hidden"
                                                       value="{{ __('buttons.upload') }}"/>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div id="security">
                        <x-globals::elements.section-title>{!! __('headlines.change_password') !!}</x-globals::elements.section-title>
                        @if (session("userdata.isExternalAuth") )
                            <strong> {{  __("text.account_managed_external_auth") }}</strong><br /><br />
                        @endif
                        <form method="post">
                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <div class="row-fluid">
                                <x-globals::forms.form-field label-text="{{ __('label.old_password') }}" name="currentPassword">
                                    <x-globals::forms.text-input :bare="true" type="password" value="" name="currentPassword" :disabled="session('userdata.isExternalAuth')" id="currentPassword" />
                                </x-globals::forms.form-field>

                                <x-globals::forms.form-field label-text="{{ __('label.new_password') }}" name="newPassword">
                                    <x-globals::forms.text-input :bare="true" type="password" value="" name="newPassword" :disabled="session('userdata.isExternalAuth')" id="newPassword" />
                                    <span id="pwStrength"></span>
                                </x-globals::forms.form-field>

                                <x-globals::forms.form-field label-text="{{ __('label.password_repeat') }}" name="confirmPassword" caption="{{ !session('userdata.isExternalAuth') ? __('label.passwordRequirements') : '' }}">
                                    <x-globals::forms.text-input :bare="true" type="password" value="" name="confirmPassword" :disabled="session('userdata.isExternalAuth')" id="confirmPassword" />
                                </x-globals::forms.form-field>
                            </div>
                            @if (!session("userdata.isExternalAuth") )
                                <input type="hidden" name="savepw" value="1" />
                                <x-globals::forms.button submit type="primary" name="save" id="savePw">{{ __('buttons.save') }}</x-globals::forms.button>
                            @endif
                        </form>
                        <br /><br />
                        <x-globals::elements.section-title icon="shield">{{ __('headlines.twoFA') }}</x-globals::elements.section-title>
                        @if ($values['twoFAEnabled'] )
                            <p>{!!   __('text.twoFA_enabled') !!}</p>
                        @else
                            <p>{!! __('text.twoFA_disabled')  !!}</p>
                        @endif
                        <p><a href="{{ BASE_URL }}/twoFA/edit">{!! __('text.twoFA_manage') !!}</a></p>
                    </div>

                    <div id="settings">
                        <form action="" method="post">
                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <div class="row-fluid">
                                <x-globals::forms.form-field label-text="{{ __('label.language') }}" name="language">
                                    <x-globals::forms.select :bare="true" name="language" id="language" class="tw:w-56">
                                        @foreach ($languageList as $languagKey => $languageValue )
                                            <option value="{{ $languagKey }}"
                                                    @if ($userLang == $languagKey )
                                                        selected='selected'
                                                     @endif >{{ $languageValue }}</option>
                                         @endforeach
                                    </x-globals::forms.select>
                                </x-globals::forms.form-field>

                                <x-globals::forms.form-field label-text="{{ __('label.date_format') }}" name="date_format">
                                    <x-globals::forms.select :bare="true" name="date_format" id="date_format" class="tw:w-56">
                                       @php
                                        $dateFormats = $dateTimeValues['dates'];
                                        $dateTimeNow = date_create();
                                       @endphp
                                        @foreach ($dateFormats as $format)
                                            <option value="{{ $format }}"
                                                    @if ($dateFormat == $format)
                                                        selected='selected'
                                                    @endif >{{ date_format($dateTimeNow, $format) }}</option>
                                        @endforeach
                                    </x-globals::forms.select>
                                </x-globals::forms.form-field>

                                <x-globals::forms.form-field label-text="{{ __('label.time_format') }}" name="time_format">
                                    <x-globals::forms.select :bare="true" name="time_format" id="time_format" class="tw:w-56">
                                        @php
                                            $timeFormats = $dateTimeValues['times'];
                                            $dateTimeNow = date_create();
                                        @endphp
                                        @foreach ($timeFormats as $format)
                                            <option value="{{ $format }}"
                                                    @if ($timeFormat == $format)
                                                        selected='selected'
                                                    @endif>{{ date_format($dateTimeNow, $format) }}</option>
                                        @endforeach
                                    </x-globals::forms.select>
                                </x-globals::forms.form-field>

                                <x-globals::forms.form-field label-text="{{ __('label.timezone') }}" name="timezone">
                                    <x-globals::forms.select :bare="true" name="timezone" id="timezone" class="tw:w-56">
                                        @foreach ($timezoneOptions as $tz)
                                            <option value="{{ $tz }}"
                                                    @if ($timezone === $tz )
                                                        selected='selected'
                                                    @endif
                                                    >{{ $tz }}</option>
                                        @endforeach
                                    </x-globals::forms.select>
                                </x-globals::forms.form-field>
                            </div>
                            <input type="hidden" name="saveSettings" value="1" />
                            <x-globals::forms.button submit type="primary" name="save" id="saveSettings">{{ __('buttons.save') }}</x-globals::forms.button>
                        </form>
                    </div>

                    <div id="theme">
                        <form action="" method="post">
                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <div class="row-fluid">
                                <x-globals::forms.form-field label-text="Optimal Stimulation" name="themeSelect">
                                    <div class="tw:flex tw:gap-2">
                                        <?php
                                        foreach ($availableThemes as $key => $theme) { ?>
                                            <x-globals::selectable selected="{{ ($userTheme == $key ? 'true' : 'false') }}" :id="''" :name="'theme'" :value="$key" :label="''" onclick="leantime.snippets.toggleBg('{{ $key }}')">
                                               <img src="{{ BASE_URL }}/dist/images/background-{{$key}}.png" class="tw:m-0 tw:rounded-lg tw:w-[180px]" />
                                                    <br /><?= $tpl->__($theme['name']) ?>
                                            </x-globals::selectable>
                                        <?php } ?>
                                    </div>
                                </x-globals::forms.form-field>

                                <hr />

                                <x-globals::forms.form-field label-text="{{ __('label.colormode') }}" name="colormode">
                                    <x-globals::selectable :selected="($userColorMode == 'light') ? 'true' : ''" :id="'light'" :name="'colormode'" :value="'light'" :label="'Light'" onclick="leantime.snippets.toggleTheme('light')">
                                        <div class="tw:w-[80px] tw:h-[60px] tw:flex tw:items-center tw:justify-center" style="background:linear-gradient(135deg, #fffbe6 0%, #ffe8a0 100%); border-radius:var(--element-radius);">
                                            <span style="font-size:30px; color:var(--yellow); line-height:1;">&#9679;</span>
                                        </div>
                                    </x-globals::selectable>

                                    <x-globals::selectable :selected="($userColorMode == 'dark') ? 'true' : ''" :id="'dark'" :name="'colormode'" :value="'dark'" :label="'Dark'" onclick="leantime.snippets.toggleTheme('dark')">
                                        <div class="tw:w-[80px] tw:h-[60px] tw:flex tw:items-center tw:justify-center" style="background:linear-gradient(135deg, #1a2332 0%, #2c3e50 100%); border-radius:var(--element-radius);">
                                            <x-globals::elements.icon name="dark_mode" style="font-size:26px; color:var(--secondary-font-color); line-height:1;" />
                                        </div>
                                    </x-globals::selectable>
                                </x-globals::forms.form-field>

                                <hr />

                                <x-globals::forms.form-field label-text="Font" name="themeFont">
                                    @foreach($availableFonts as $key => $font)
                                        <x-globals::selectable :selected="($themeFont == $font) ? 'true' : ''" :id="$key" :name="'themeFont'" :value="$font" :label="$font" onclick="leantime.snippets.toggleFont('{{ $font }}')">
                                            <label for="selectable-{{ $key }}" class="font tw:w-[200px]"
                                                   style="font-family:'{{ $font }}'; font-size:16px;">
                                                The quick brown fox jumps over the lazy dog
                                            </label>
                                        </x-globals::selectable>
                                    @endforeach
                                </x-globals::forms.form-field>

                                <hr />

                                <x-globals::forms.form-field label-text="Color Scheme" name="colorscheme">
                                    <div class="tw:flex tw:flex-wrap tw:gap-2">
                                        @foreach($availableColorSchemes as $key => $scheme )
                                            <x-globals::selectable class="circle" :selected="($userColorScheme == $key) ? 'true' : ''" :id="$key" :name="'colorscheme'" :value="$key" :label="__($scheme['name'])"  onclick="leantime.snippets.toggleColors('{{ $scheme['primaryColor'] }}','{{ $scheme['secondaryColor'] }}');">
                                                <label for="color-{{ $key }}" class="colorCircle"
                                                       style="background:linear-gradient(135deg, {{ $scheme["primaryColor"] }} 20%, {{ $scheme["secondaryColor"] }} 100%);">
                                                </label>
                                            </x-globals::selectable>
                                        @endforeach
                                    </div>
                                </x-globals::forms.form-field>
                            </div>
                            <br /><br />
                            <input type="hidden" name="saveTheme" value="1" />
                            <x-globals::forms.button submit type="primary" name="save" id="saveTheme">{{ __('buttons.save') }}</x-globals::forms.button>
                        </form>

                        @dispatchEvent('themecontent')
                    </div>

                    <div id="notifications">
                        <form action="" method="post">
                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <x-globals::forms.checkbox name="notifications" id="notifications" value="on"
                                        :checked="$values['notifications'] == '1'"
                                        label="{{ __('label.receive_notifications') }}" />
                                </div>
                                <x-globals::forms.form-field label-text="{{ __('label.messages_frequency') }}" name="messagesfrequency">
                                    <x-globals::forms.select :bare="true" name="messagesfrequency" id="messagesfrequency" class="tw:w-56">
                                        <option value="">--{{ __('label.choose_option') }}--</option>
                                         <option value="60"
                                                 @if ($values['messagesfrequency'] == "60" )
                                             selected="selected"
                                         @endif>{{ __('label.1min') }}</option>
                                        <option value="300" @if ($values['messagesfrequency'] == "300" )
                                            selected="selected"
                                                            @endif>{{ __('label.5min') }}</option>
                                        <option value="900" @if ($values['messagesfrequency'] == "900" )
                                            selected="selected"
                                                            @endif>{{ __('label.15min') }}</option>
                                        <option value="1800" @if ($values['messagesfrequency'] == "1800" )
                                            selected="selected"
                                                             @endif>{{ __('label.30min') }}</option>
                                        <option value="3600" @if ($values['messagesfrequency'] == "3600" )
                                            selected="selected"
                                                             @endif>{{ __('label.1h') }}</option>
                                        <option value="10800" @if ($values['messagesfrequency'] == "10800" )
                                            selected="selected"
                                                              @endif>{{ __('label.3h') }}</option>
                                        <option value="36000" @if ($values['messagesfrequency'] == "36000" )
                                            selected="selected"
                                                              @endif>{{ __('label.6h') }}</option>
                                        <option value="43200" @if ($values['messagesfrequency'] == "43200" )
                                            selected="selected"
                                                              @endif>{{ __('label.12h') }}</option>
                                        <option value="86400" @if ($values['messagesfrequency'] == "86400" )
                                            selected="selected"
                                                              @endif>{{ __('label.24h') }}</option>
                                        <option value="172800" @if ($values['messagesfrequency'] == "172800" )
                                            selected="selected"
                                                               @endif>{{ __('label.48h') }}</option>
                                        <option value="604800" @if ($values['messagesfrequency'] == "604800" )
                                            selected="selected"
                                                               @endif>{{ __('label.1w') }}</option>
                                    </x-globals::forms.select>
                                </x-globals::forms.form-field>
                            </div>

                            <hr />

                            <x-globals::elements.section-title>{{ __('label.notification_event_types') }}</x-globals::elements.section-title>
                            <p><small>{{ __('label.notification_event_types_description') }}</small></p>
                            <div class="tw:mb-4">
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
                                    <x-globals::forms.checkbox name="enabledEventTypes[]" value="{{ $categoryKey }}"
                                        :checked="in_array($categoryKey, $enabledEventTypes)">
                                        <strong>{{ __($categoryLabels[$categoryKey] ?? $categoryKey) }}</strong><br />
                                        <small class="tw:text-gray-500">{{ __($config['description'] ?? '') }}</small>
                                    </x-globals::forms.checkbox>
                                @endforeach
                            </div>

                            <hr />

                            <x-globals::elements.section-title>{{ __('label.project_notifications') }}</x-globals::elements.section-title>
                            <p><small>{{ __('label.project_notifications_description') }}</small></p>
                            <div class="tw:max-w-lg tw:max-h-[350px] tw:overflow-y-auto tw:mb-5">
                                @if (count($userProjects) > 0)
                                    @foreach ($userProjects as $project)
                                        @php
                                            $currentLevel = $projectNotificationLevels[$project['id']] ?? $companyDefaultRelevance;
                                        @endphp
                                        <div class="tw:flex tw:items-center tw:justify-between tw:py-1.5 tw:border-b tw:border-gray-100">
                                            <span class="tw:truncate tw:mr-3">
                                                {{ $project['name'] }}
                                                @if (!empty($project['clientName']))
                                                    <span class="tw:text-gray-400 tw:text-xs">({{ $project['clientName'] }})</span>
                                                @endif
                                            </span>
                                            <x-globals::forms.select name="projectNotificationLevel[{{ $project['id'] }}]"
                                                    class="tw:text-sm tw:border tw:border-gray-300 tw:rounded tw:px-2 tw:py-1 tw:min-w-[140px]">
                                                @foreach ($relevanceLevels as $level => $labelKey)
                                                    <option value="{{ $level }}"
                                                            @if ($currentLevel === $level) selected @endif
                                                    >{{ __($labelKey) }}</option>
                                                @endforeach
                                            </x-globals::forms.select>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="tw:text-gray-400 tw:p-2">{{ __('label.no_projects') }}</p>
                                @endif
                            </div>

                            <input type="hidden" name="savenotifications" value="1" />
                            <x-globals::forms.button submit type="primary" name="save">{{ __('buttons.save') }}</x-globals::forms.button>
                        </form>
                    </div>

                    @dispatchEvent('tabsContent')
                </div>
    </div>
</div>


<script type="text/javascript">

    function initAccountSettings() {
        leantime.usersController.checkPWStrength('newPassword');
    }

    jQuery(document).ready(function(){
        initAccountSettings();
    });
</script>

@endsection
