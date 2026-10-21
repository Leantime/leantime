@extends($layout)

@section('content')

<x-global::layout.page-header icon="fa fa-user" :eyebrow="__('label.overview')">
    {!! __('headlines.accountSettings') !!}
</x-global::layout.page-header>

<div class="maincontent">

    {!! $tpl->displayNotification() !!}

    <div class="row">
        <div class="col-md-12">
            <x-global::layout.content-box>
                <div class="tabbedwidget tab-primary accountTabs">

                    <ul>
                        <li><a href="#myProfile">{!! __('tabs.myProfile') !!}</a></li>
                        <li><a href="#security">{!! __('tabs.security') !!}</a></li>
                        <li><a href="#settings">{!! __('tabs.settings') !!}</a></li>
                        <li><a href="#notifications">{!! __('tabs.notifications') !!}</a></li>
                        <li><a href="#webhooks">{!! __('tabs.webhooks') !!}</a></li>
                        <li><a href="#theme">{!! __('tabs.theme') !!}</a></li>
                        <li><a href="#background">{!! __('tabs.background') !!}</a></li>
                        @dispatchEvent('tabs')
                        <li><a href="#personalTokens"><i class="fa-solid fa-key"></i> {{ __('tabs.personal_access_tokens') }}</a></li>
                    </ul>

                    <div id="myProfile">
                        <div class="row">
                            <div class="col-md-8">
                                <form action="" method="post">
                                    <x-global::elements.section-header><?php echo $tpl->__('label.profile_information'); ?></x-global::elements.section-header>
                                    <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                                    <div class="row-fluid">
                                        <div class="form-group">
                                            <x-global::forms.label for="firstname" >{{ __('label.firstname') }}</x-global::forms.label>
                                            <span>
                                                <input type="text" class="input" name="firstname" id="firstname" {{ session("userdata.isExternalAuth") ? "disabled='disabled'" : '' }}
                                                value="{{ $values['firstname'] }}"/><br/>
                                            </span>
                                        </div>

                                        <div class="form-group">
                                            <x-global::forms.label for="lastname" >{{ __('label.lastname') }}</x-global::forms.label>
                                            <span>
                                                <input type="text" name="lastname" class="input" id="lastname" {{ session("userdata.isExternalAuth") ? "disabled='disabled'" : '' }}
                                                value="{{ $values['lastname']  }}"/><br/>
                                            </span>
                                        </div>

                                        <div class="form-group">
                                            <x-global::forms.label for="user" >{{ __('label.email') }}</x-global::forms.label>
                                            <span>
                                                <input type="text" name="user" class="input" id="user" {{ session("userdata.isExternalAuth") ? "disabled='disabled'" : '' }}
                                                value="{{ $values['user']  }}"/><br/>
                                            </span>
                                        </div>

                                        <div class="form-group">
                                            <x-global::forms.label for="phone" >{{ __('label.phone') }}</x-global::forms.label>
                                            <span>
                                                <input type="text" name="phone" class="input" id="phone" {{ session("userdata.isExternalAuth") ? "disabled='disabled'" : '' }}
                                                value="{{ $values['phone']  }}"/><br/>
                                            </span>
                                        </div>
                                        <p class='stdformbutton'>
                                            <input type="hidden" name="profileInfo" value="1" />

                                            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" id="save" />
                                        </p>
                                        <br />
                                        <x-global::elements.section-header>{{ __('label.employee_information') }}</x-global::elements.section-header>
                                        <em>{{ __('text.only_admins_can_change_user_info') }}</em><br /><br />
                                        <div class="form-group">
                                            <x-global::forms.label for="phone" >{{ __('label.jobTitle') }}</x-global::forms.label>
                                            <span>
                                                <input type="text" name="jobTitle" readonly class="input" id="jobTitle"}}
                                                value="{{ $values['jobTitle']  }}"/><br/>
                                            </span>
                                        </div>

                                        <div class="form-group">
                                            <x-global::forms.label for="phone" >{{ __('label.jobLevel') }}</x-global::forms.label>
                                            <span>
                                                <input type="text" name="jobLevel" readonly class="input" id="jobLevel"}}
                                                       value="{{ $values['jobLevel']  }}"/><br/>
                                            </span>
                                        </div>

                                        <div class="form-group">
                                            <x-global::forms.label for="phone" >{{ __('label.department') }}</x-global::forms.label>
                                            <span>
                                                <input type="text" name="department" readonly class="input" id="department"}}
                                                       value="{{ $values['department']  }}"/><br/>
                                            </span>
                                        </div>

                                    </div>


                                </form>
                            </div>
                            <div class="col-md-4">
                                <div class="center">
                                    <img src='{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}&v={{ format($user['modified'])->timestamp() }}'  class='profileImg tw-rounded-full' alt='{{ __('headlines.profile_picture') }}' id="previousImage"/>
                                    <div id="profileImg">
                                    </div>

                                    <div class="par">

                                        <x-global::forms.label>{{ __('label.upload') }}</x-global::forms.label>

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
                                        <input type='file' name='file' onchange="leantime.usersController.readURL(this)" accept=".jpg,.png,.gif,.webp"/>
                                    </span>

                                                <a href='#' class='btn fileupload-exists' data-dismiss='fileupload' onclick="leantime.usersController.clearCroppie()">{{ __('buttons.remove') }}</a>
                                            </div>
                                            <p class='stdformbutton'>
                                    <span id="save-picture" class="btn btn-primary fileupload-exists ld-ext-right">
                                        <span onclick="leantime.usersController.saveCroppie()">{{ __('buttons.save') }}</span>
                                        <span class="ld ld-ring ld-spin"></span>
                                    </span>
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
                        <x-global::elements.section-header>{!! __('headlines.change_password') !!}</x-global::elements.section-header>
                        @if (session("userdata.isExternalAuth") )
                            <strong> {{  __("text.account_managed_external_auth") }}</strong><br /><br />
                        @endif
                        <form method="post">
                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <x-global::forms.label for="currentPassword" >{{ __('label.old_password') }}</x-global::forms.label>
                                    <span>
                                        <input type='password' value="" name="currentPassword" class="input" {{ session("userdata.isExternalAuth") ? "disabled='disabled'" : '' }}
                                               id="currentPassword"/><br/>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <x-global::forms.label for="newPassword" >{{ __('label.new_password') }}</x-global::forms.label>
                                    <span>
                                        <input type='password' value="" name="newPassword" class="input" {{ session("userdata.isExternalAuth") ? "disabled='disabled'" : '' }}
                                               id="newPassword"/>
                                        <span id="pwStrength"></span>

                                    </span>
                                </div>

                                <div class="form-group">
                                    <x-global::forms.label for="confirmPassword" >{{ __('label.password_repeat') }}</x-global::forms.label>
                                    <span>
                                        <input type="password" value="" name="confirmPassword" class="input" {{ session("userdata.isExternalAuth") ? "disabled='disabled'" : '' }}
                                               id="confirmPassword"/><br/>
                                        @if (!session("userdata.isExternalAuth") )
                                        <small>{{ __('label.passwordRequirements') }}</small>
                                       @endif
                                    </span>

                                </div>
                            </div>
                            @if (!session("userdata.isExternalAuth") )
                                <input type="hidden" name="savepw" value="1" />
                                <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" id="savePw" />
                            @endif
                        </form>
                        <br /><br />
                        <x-global::elements.section-header icon="fa-solid fa-shield-halved">{{ __('headlines.twoFA') }}</x-global::elements.section-header>
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
                                    <x-global::forms.label for="language" >{{ __('label.language') }}</x-global::forms.label>
                                    <span class='field'>
                                        <x-global::forms.select enhanced name="language" id="language">
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
                                    <x-global::forms.label for="date_format" >{{ __('label.date_format') }}</x-global::forms.label>
                                    <span>
                                        <x-global::forms.select name="date_format" id="date_format">
                                           @php
                                            $dateFormats = $dateTimeValues['dates'];
                                            $dateTimeNow = dtHelper()->userNow();
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
                                    <x-global::forms.label for="time_format" >{{ __('label.time_format') }}</x-global::forms.label>
                                    <span>
                                        <x-global::forms.select name="time_format" id="time_format">
                                            @php
                                                $timeFormats = $dateTimeValues['times'];
                                                $dateTimeNow = dtHelper()->userNow();
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
                                    <x-global::forms.label for="timezone" >{{ __('label.timezone') }}</x-global::forms.label>
                                    <span>
                                        <x-global::forms.select name="timezone" id="timezone">

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
                            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" id="saveSettings" />
                        </form>
                    </div>

                    <div id="theme">
                        <form action="" method="post">
                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <x-global::forms.label for="themeSelect">{{ __('label.optimal_stimulation') }}</x-global::forms.label>
                                    <span class='field tw-flex tw-w-80'>

                                         <?php
                                         foreach ($availableThemes as $key => $theme) { ?>
                                             <x-global::selectable selected="{{ ($userTheme == $key ? 'true' : 'false') }}" :id="''" :name="'theme'" :value="$key" :label="''" class="tw-w-1/2" onclick="leantime.snippets.toggleBg('{{ $key }}')">
                                                <img src="{{ BASE_URL }}/dist/images/background-{{$key}}.png" style="margin:0; border-radius:10px;" />
                                                     <br /><?= $tpl->__($theme['name']) ?>
                                             </x-global::selectable>

                                        <?php } ?>
                                    </span>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">

                                        <hr />
                                        <x-global::forms.label for="colormode" >{{ __('label.colormode') }}</x-global::forms.label>

                                        <x-global::selectable :selected="($userColorMode == 'light') ? 'true' : ''" :id="'light'" :name="'colormode'" :value="'light'" :label="'Light'" onclick="leantime.snippets.toggleTheme('light')">
                                            <label for="colormode-light" class="tw-w-[100px]">
                                                <i class="fa-solid fa-sun tw-font-xxl"></i>
                                            </label>
                                        </x-global::selectable>

                                        <x-global::selectable :selected="($userColorMode == 'dark') ? 'true' : ''" :id="'dark'" :name="'colormode'" :value="'dark'" :label="'Dark'" onclick="leantime.snippets.toggleTheme('dark')">
                                            <label for="colormode-light" class="tw-w-[100px]">
                                                <i class="fa-solid fa-moon tw-font-xxl"></i>
                                            </label>
                                        </x-global::selectable>

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <hr />
                                        <x-global::forms.label>{{ __('label.font') }}</x-global::forms.label>
                                        @foreach($availableFonts as $key => $font)

                                            <x-global::selectable  :selected="($themeFont == $font) ? 'true' : ''" :id="$key" :name="'themeFont'" :value="$font" :label="$font" onclick="leantime.snippets.toggleFont('{{ $font }}')">
                                                <x-global::forms.label for="selectable-{{ $key }}" class="font tw-w-[200px]"
                                                       style="font-family:'{{ $font }}'; font-size:16px;">
                                                    {{ __('text.font_preview_sentence') }}
                                                </x-global::forms.label>
                                            </x-global::selectable>

                                        @endforeach

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <hr />
                                        <x-global::forms.label>{{ __('label.color_scheme') }}</x-global::forms.label>
                                        @foreach($availableColorSchemes as $key => $scheme )
                                            <x-global::selectable class="circle" :selected="($userColorScheme == $key) ? 'true' : ''" :id="$key" :name="'colorscheme'" :value="$key" :label="__($scheme['name'])"  onclick="leantime.snippets.toggleColors('{{ $scheme['primaryColor'] }}','{{ $scheme['secondaryColor'] }}');">
                                                <label for="color-{{ $key }}" class="colorCircle"
                                                       style="background:linear-gradient(135deg, {{ $scheme["primaryColor"] }} 20%, {{ $scheme["secondaryColor"] }} 100%);">
                                                </label>
                                            </x-global::selectable>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <br /><br />
                            <input type="hidden" name="saveTheme" value="1" />
                            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" id="saveTheme" />
                        </form>

                        @dispatchEvent('themecontent')
                    </div>

                    <div id="background">
                        {{-- Loads when the tab is shown, so Unsplash is only queried by users who open it. --}}
                        <x-global::hx endpoint="users/background" trigger="intersect once" wrapperId="backgroundSettings" />
                    </div>

                    <div id="notifications">
                        <form action="" method="post">
                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <x-global::forms.checkbox name="notifications" value="on" id="notifications" :checked="$values['notifications'] == '1'">{{ __('label.receive_notifications') }}</x-global::forms.checkbox>
                                </div>
                                <div class="form-group">
                                    <x-global::forms.label for="messagesfrequency" >{{ __('label.messages_frequency') }}</x-global::forms.label>
                                    <span>
                                        <x-global::forms.select enhanced name="messagesfrequency" class="input" id="messagesfrequency">
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

                            <x-global::elements.section-header>{{ __('label.notification_event_types') }}</x-global::elements.section-header>
                            <p><small>{{ __('label.notification_event_types_description') }}</small></p>
                            <div class="tw-mb-4">
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
                                    <x-global::forms.checkbox name="enabledEventTypes[]" :value="$categoryKey" wrapperClass="tw-flex"
                                        :checked="in_array($categoryKey, $enabledEventTypes)"
                                        :description="__($config['description'] ?? '')">{{ __($categoryLabels[$categoryKey] ?? $categoryKey) }}</x-global::forms.checkbox>
                                @endforeach
                            </div>

                            <hr />

                            <x-global::elements.section-header>{{ __('label.project_notifications') }}</x-global::elements.section-header>
                            <p><small>{{ __('label.project_notifications_description') }}</small></p>
                            <div class="tw-max-w-lg tw-max-h-[350px] tw-overflow-y-auto tw-mb-5">
                                @if (count($userProjects) > 0)
                                    @foreach ($userProjects as $project)
                                        @php
                                            $currentLevel = $projectNotificationLevels[$project['id']] ?? $companyDefaultRelevance;
                                        @endphp
                                        <div class="tw-flex tw-items-center tw-justify-between tw-py-1.5 tw-border-b tw-border-gray-100">
                                            <span class="tw-truncate tw-mr-3">
                                                {{ $project['name'] }}
                                                @if (!empty($project['clientName']))
                                                    <span class="tw-text-gray-400 tw-text-xs">({{ $project['clientName'] }})</span>
                                                @endif
                                            </span>
                                            <x-global::forms.select name="projectNotificationLevel[{{ $project['id'] }}]"
                                                    class="tw-text-sm tw-border tw-border-gray-300 tw-rounded tw-px-2 tw-py-1 tw-min-w-[140px]">
                                                @foreach ($relevanceLevels as $level => $labelKey)
                                                    <option value="{{ $level }}"
                                                            @if ($currentLevel === $level) selected @endif
                                                    >{{ __($labelKey) }}</option>
                                                @endforeach
                                            </x-global::forms.select>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="tw-text-gray-400 tw-p-2">{{ __('label.no_projects') }}</p>
                                @endif
                            </div>

                            <input type="hidden" name="savenotifications" value="1" />
                            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" />
                        </form>
                    </div>

                    <div id="webhooks">
                        <form action="" method="post">
                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <p>{{ __('label.webhook_notifications_description') }}</p>
                            <br />
                            <div class="form-group">
                                <x-global::forms.checkbox name="webhookEnabled" id="webhookEnabled" :checked="! empty($webhookEnabled)">{{ __('label.enable_webhook_notifications') }}</x-global::forms.checkbox>
                            </div>
                            <div class="form-group">
                                <x-global::forms.label for="webhookUrl">{{ __('label.webhook_url') }}</x-global::forms.label>
                                <span>
                                    <x-global::forms.text-input type="url" name="webhookUrl" id="webhookUrl" class="input"
                                                                placeholder="https://example.com/webhook"
                                                                value="{{ $webhookUrl ?? '' }}" />
                                    <br/>
                                    <small class="tw-text-gray-500">{{ __('label.webhook_url_hint') }}</small>
                                </span>
                            </div>

                            <input type="hidden" name="savewebhooks" value="1" />
                            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" />
                        </form>
                    </div>

                    @dispatchEvent('tabsContent')

                    <div id="personalTokens">
                        <x-global::hx endpoint="auth/personalTokens" trigger="load" />
                    </div>
                </div>
            </x-global::layout.content-box>
        </div>
    </div>
</div>


<script type="text/javascript">

    jQuery(document).ready(function(){

        leantime.usersController.checkPWStrength('newPassword');

        jQuery('.accountTabs').tabs();


    });
</script>

@endsection
