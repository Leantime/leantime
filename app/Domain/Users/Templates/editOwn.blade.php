@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-user'">
    <h5>{{ __('label.overview') }}</h5>
    <h1>{!! __('headlines.accountSettings') !!}</h1>

</x-global::pageheader>

<div class="maincontent">

    {!! $tpl->displayNotification() !!}

    <div class="maincontentinner">
        <div class="lt-tabs tabbedwidget accountTabs" data-tabs data-tabs-persist="hash">

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
                                    <h4 class="widgettitle title-light"><?php echo $tpl->__('label.profile_information'); ?></h4>
                                    <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                                    <div class="row-fluid">
                                        <div class="form-group">
                                            <label for="firstname" >{{ __('label.firstname') }}</label>
                                            <span>
                                                <x-global::forms.input name="firstname" id="firstname" :disabled="session('userdata.isExternalAuth')" value="{{ $values['firstname'] }}" /><br/>
                                            </span>
                                        </div>

                                        <div class="form-group">
                                            <label for="lastname" >{{ __('label.lastname') }}</label>
                                            <span>
                                                <x-global::forms.input name="lastname" id="lastname" :disabled="session('userdata.isExternalAuth')" value="{{ $values['lastname'] }}" /><br/>
                                            </span>
                                        </div>

                                        <div class="form-group">
                                            <label for="user" >{{ __('label.email') }}</label>
                                            <span>
                                                <x-global::forms.input name="user" id="user" :disabled="session('userdata.isExternalAuth')" value="{{ $values['user'] }}" /><br/>
                                            </span>
                                        </div>

                                        <div class="form-group">
                                            <label for="phone" >{{ __('label.phone') }}</label>
                                            <span>
                                                <x-global::forms.input name="phone" id="phone" :disabled="session('userdata.isExternalAuth')" value="{{ $values['phone'] }}" /><br/>
                                            </span>
                                        </div>
                                        <p class='stdformbutton'>
                                            <input type="hidden" name="profileInfo" value="1" />

                                            <x-global::button submit type="primary" name="save" id="save">{{ __('buttons.save') }}</x-global::button>
                                        </p>
                                        <br />
                                        <h4 class="widgettitle title-light">{{ __('label.employee_information') }}</h4>
                                        <em>{{ __('text.only_admins_can_change_user_info') }}</em><br /><br />
                                        <div class="form-group">
                                            <label for="phone" >{{ __('label.jobTitle') }}</label>
                                            <span>
                                                <x-global::forms.input name="jobTitle" id="jobTitle" :readonly="true" value="{{ $values['jobTitle'] }}" /><br/>
                                            </span>
                                        </div>

                                        <div class="form-group">
                                            <label for="phone" >{{ __('label.jobLevel') }}</label>
                                            <span>
                                                <x-global::forms.input name="jobLevel" id="jobLevel" :readonly="true" value="{{ $values['jobLevel'] }}" /><br/>
                                            </span>
                                        </div>

                                        <div class="form-group">
                                            <label for="phone" >{{ __('label.department') }}</label>
                                            <span>
                                                <x-global::forms.input name="department" id="department" :readonly="true" value="{{ $values['department'] }}" /><br/>
                                            </span>
                                        </div>

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
                                        <x-global::forms.file :bare="true" name="file" accept=".jpg,.png,.gif,.webp" onchange="leantime.usersController.readURL(this)" />
                                    </span>

                                                <x-global::button link="#" type="secondary" class="fileupload-exists" data-dismiss="fileupload" onclick="leantime.usersController.clearCroppie()">{{ __('buttons.remove') }}</x-global::button>
                                            </div>
                                            <p class='stdformbutton'>
                                    <x-global::button tag="button" type="primary" id="save-picture" class="fileupload-exists" onclick="leantime.usersController.saveCroppie()">{{ __('buttons.save') }}</x-global::button>
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
                        <h4 class="widgettitle title-light">
                            {!! __('headlines.change_password') !!}
                        </h4>
                        @if (session("userdata.isExternalAuth") )
                            <strong> {{  __("text.account_managed_external_auth") }}</strong><br /><br />
                        @endif
                        <form method="post">
                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="currentPassword" >{{ __('label.old_password') }}</label>
                                    <span>
                                        <x-global::forms.input type="password" value="" name="currentPassword" :disabled="session('userdata.isExternalAuth')" id="currentPassword" /><br/>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="newPassword" >{{ __('label.new_password') }}</label>
                                    <span>
                                        <x-global::forms.input type="password" value="" name="newPassword" :disabled="session('userdata.isExternalAuth')" id="newPassword" />
                                        <span id="pwStrength"></span>

                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="confirmPassword" >{{ __('label.password_repeat') }}</label>
                                    <span>
                                        <x-global::forms.input type="password" value="" name="confirmPassword" :disabled="session('userdata.isExternalAuth')" id="confirmPassword" /><br/>
                                        @if (!session("userdata.isExternalAuth") )
                                        <small>{{ __('label.passwordRequirements') }}</small>
                                       @endif
                                    </span>

                                </div>
                            </div>
                            @if (!session("userdata.isExternalAuth") )
                                <input type="hidden" name="savepw" value="1" />
                                <x-global::button submit type="primary" name="save" id="savePw">{{ __('buttons.save') }}</x-global::button>
                            @endif
                        </form>
                        <br /><br />
                        <h4 class="widgettitle title-light">
                            <i class="fa-solid fa-shield-halved"></i> {{ __('headlines.twoFA') }}
                        </h4>
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
                                <div class="form-group">
                                    <label for="language" >{{ __('label.language') }}</label>
                                    <span class='field'>
                                        <x-global::forms.select name="language" id="language" style="width: 220px">
                                            @foreach ($languageList as $languagKey => $languageValue )
                                                <option value="{{ $languagKey }}"
                                                        @if ($userLang == $languagKey )
                                                            selected='selected'
                                                         @endif >{{ $languageValue }}</option>
                                             @endforeach
                                        </x-global::forms.select>
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="date_format" >{{ __('label.date_format') }}</label>
                                    <span>
                                        <x-global::forms.select name="date_format" id="date_format" style="width: 220px">
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
                                        </x-global::forms.select>
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="time_format" >{{ __('label.time_format') }}</label>
                                    <span>
                                        <x-global::forms.select name="time_format" id="time_format" style="width: 220px">
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
                                        </x-global::forms.select>
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="timezone" >{{ __('label.timezone') }}</label>
                                    <span>
                                        <x-global::forms.select name="timezone" id="timezone" style="width: 220px">

                                            @foreach ($timezoneOptions as $tz)
                                                <option value="{{ $tz }}"
                                                        @if ($timezone === $tz )
                                                            selected='selected'
                                                        @endif
                                                        >{{ $tz }}</option>
                                            @endforeach
                                        </x-global::forms.select>
                                    </span>
                                </div>
                            </div>
                            <input type="hidden" name="saveSettings" value="1" />
                            <x-global::button submit type="primary" name="save" id="saveSettings">{{ __('buttons.save') }}</x-global::button>
                        </form>
                    </div>

                    <div id="theme">
                        <form action="" method="post">
                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="themeSelect">Optimal Stimulation</label>
                                    <span class='field tw:flex' style="gap:10px;">

                                         <?php
                                         foreach ($availableThemes as $key => $theme) { ?>
                                             <x-global::selectable selected="{{ ($userTheme == $key ? 'true' : 'false') }}" :id="''" :name="'theme'" :value="$key" :label="''" onclick="leantime.snippets.toggleBg('{{ $key }}')">
                                                <img src="{{ BASE_URL }}/dist/images/background-{{$key}}.png" style="margin:0; border-radius:10px; width:180px;" />
                                                     <br /><?= $tpl->__($theme['name']) ?>
                                             </x-global::selectable>

                                        <?php } ?>
                                    </span>
                                </div>

                                <div>

                                        <hr />
                                        <label for="colormode" >{{ __('label.colormode') }}</label>

                                        <x-global::selectable :selected="($userColorMode == 'light') ? 'true' : ''" :id="'light'" :name="'colormode'" :value="'light'" :label="'Light'" onclick="leantime.snippets.toggleTheme('light')">
                                            <div style="width:80px; height:60px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg, #fffbe6 0%, #ffe8a0 100%); border-radius:var(--element-radius);">
                                                <span style="font-size:30px; color:#f5a623; line-height:1;">&#9679;</span>
                                            </div>
                                        </x-global::selectable>

                                        <x-global::selectable :selected="($userColorMode == 'dark') ? 'true' : ''" :id="'dark'" :name="'colormode'" :value="'dark'" :label="'Dark'" onclick="leantime.snippets.toggleTheme('dark')">
                                            <div style="width:80px; height:60px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg, #1a2332 0%, #2c3e50 100%); border-radius:var(--element-radius);">
                                                <i class="fa-solid fa-moon" style="font-size:26px; color:#c4cfe0; line-height:1;"></i>
                                            </div>
                                        </x-global::selectable>

                                </div>
                                <div>
                                        <hr />
                                        <label>Font</label>
                                        @foreach($availableFonts as $key => $font)

                                            <x-global::selectable  :selected="($themeFont == $font) ? 'true' : ''" :id="$key" :name="'themeFont'" :value="$font" :label="$font" onclick="leantime.snippets.toggleFont('{{ $font }}')">
                                                <label for="selectable-{{ $key }}" class="font tw:w-[200px]"
                                                       style="font-family:'{{ $font }}'; font-size:16px;">
                                                    The quick brown fox jumps over the lazy dog
                                                </label>
                                            </x-global::selectable>

                                        @endforeach

                                </div>
                                <div>
                                        <hr />
                                        <label>Color Scheme</label>
                                        @foreach($availableColorSchemes as $key => $scheme )
                                            <x-global::selectable class="circle" :selected="($userColorScheme == $key) ? 'true' : ''" :id="$key" :name="'colorscheme'" :value="$key" :label="__($scheme['name'])"  onclick="leantime.snippets.toggleColors('{{ $scheme['primaryColor'] }}','{{ $scheme['secondaryColor'] }}');">
                                                <label for="color-{{ $key }}" class="colorCircle"
                                                       style="background:linear-gradient(135deg, {{ $scheme["primaryColor"] }} 20%, {{ $scheme["secondaryColor"] }} 100%);">
                                                </label>
                                            </x-global::selectable>
                                        @endforeach
                                </div>
                            </div>
                            <br /><br />
                            <input type="hidden" name="saveTheme" value="1" />
                            <x-global::button submit type="primary" name="save" id="saveTheme">{{ __('buttons.save') }}</x-global::button>
                        </form>

                        @dispatchEvent('themecontent')
                    </div>

                    <div id="notifications">
                        <form action="" method="post">
                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <x-global::forms.checkbox name="notifications" id="notifications" value="on"
                                        :checked="$values['notifications'] == '1'"
                                        label="{{ __('label.receive_notifications') }}" />
                                </div>
                                <div class="form-group">
                                    <label for="messagesfrequency" >{{ __('label.messages_frequency') }}</label>
                                    <span>
                                        <x-global::forms.select name="messagesfrequency" id="messagesfrequency" style="width: 220px">
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
                                        </x-global::forms.select> <br/>
                                    </span>
                                </div>
                            </div>

                            <hr />

                            <h4 class="widgettitle title-light">{{ __('label.notification_event_types') }}</h4>
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
                                    <x-global::forms.checkbox name="enabledEventTypes[]" value="{{ $categoryKey }}"
                                        :checked="in_array($categoryKey, $enabledEventTypes)">
                                        <strong>{{ __($categoryLabels[$categoryKey] ?? $categoryKey) }}</strong><br />
                                        <small class="tw:text-gray-500">{{ __($config['description'] ?? '') }}</small>
                                    </x-global::forms.checkbox>
                                @endforeach
                            </div>

                            <hr />

                            <h4 class="widgettitle title-light">{{ __('label.project_notifications') }}</h4>
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
                                            <x-global::forms.select name="projectNotificationLevel[{{ $project['id'] }}]"
                                                    class="tw:text-sm tw:border tw:border-gray-300 tw:rounded tw:px-2 tw:py-1 tw:min-w-[140px]">
                                                @foreach ($relevanceLevels as $level => $labelKey)
                                                    <option value="{{ $level }}"
                                                            @if ($currentLevel === $level) selected @endif
                                                    >{{ __($labelKey) }}</option>
                                                @endforeach
                                            </x-global::forms.select>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="tw:text-gray-400 tw:p-2">{{ __('label.no_projects') }}</p>
                                @endif
                            </div>

                            <input type="hidden" name="savenotifications" value="1" />
                            <x-global::button submit type="primary" name="save">{{ __('buttons.save') }}</x-global::button>
                        </form>
                    </div>

                    @dispatchEvent('tabsContent')
                </div>
    </div>
</div>


<script type="text/javascript">

    function initAccountSettings() {
        leantime.usersController.checkPWStrength('newPassword');

        jQuery("#messagesfrequency").chosen();
        jQuery("#language").chosen();
        jQuery("#themeSelect").chosen();
    }

    jQuery(document).ready(function(){
        initAccountSettings();
    });
</script>

@endsection
