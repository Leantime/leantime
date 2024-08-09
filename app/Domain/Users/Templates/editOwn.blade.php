@extends($layout)

@section('content')

<x-global::content.pageheader :icon="'fa fa-user'">
    <h5>{{ __('label.overview') }}</h5>
    <h1>{!! __('headlines.accountSettings') !!}</h1>

</x-global::content.pageheader>

<div class="maincontent">

    {!! $tpl->displayNotification() !!}

    <div class="row">
        <div class="col-md-8">
            <div class="maincontentinner">
                <div class="tabbedwidget tab-primary accountTabs">

                    <ul>
                        <li><a href="#myProfile">{!! __('tabs.myProfile') !!}</a></li>
                        <li><a href="#security">{!! __('tabs.security') !!}</a></li>
                        <li><a href="#settings">{!! __('tabs.settings') !!}</a></li>
                        <li><a href="#theme">{!! __('tabs.theme') !!}</a></li>
                        <li><a href="#notifications">{!! __('tabs.notifications') !!}</a></li>
                    </ul>

                    <div id="myProfile">
                        <form action="" method="post">

                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="firstname" >{{ __('label.firstname') }}</label>
                                    <span>
                                        <input type="text" class="input" name="firstname" id="firstname" {{ session("userdata.isLdap") ? "disabled='disabled'" : '' }}
                                               value="{{ $values['firstname'] }}"/><br/>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="lastname" >{{ __('label.lastname') }}</label>
                                    <span>
                                        <input type="text" name="lastname" class="input" id="lastname" {{ session("userdata.isLdap") ? "disabled='disabled'" : '' }}
                                               value="{{ $values['lastname']  }}"/><br/>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="user" >{{ __('label.email') }}</label>
                                    <span>
                                        <input type="text" name="user" class="input" id="user" {{ session("userdata.isLdap") ? "disabled='disabled'" : '' }}
                                               value="{{ $values['user']  }}"/><br/>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="phone" >{{ __('label.phone') }}</label>
                                    <span>
                                        <input type="text" name="phone" class="input" id="phone" {{ session("userdata.isLdap") ? "disabled='disabled'" : '' }}
                                               value="{{ $values['phone']  }}"/><br/>
                                    </span>
                                </div>

                            </div>
                            <p class='stdformbutton'>
                                <input type="hidden" name="profileInfo" value="1" />

                                <input type="submit" name="save" id="save" value="{{ __('buttons.save') }}" class="button"/>
                            </p>

                        </form>
                    </div>

                    <div id="security">
                        <h4 class="widgettitle title-light">
                            {!! __('headlines.change_password') !!}
                        </h4>
                        @if (session("userdata.isLdap") );
                            <strong> {{  __("text.account_managed_ldap") }}</strong><br /><br />
                        @endif
                        <form method="post">
                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="currentPassword" >{{ __('label.old_password') }}</label>
                                    <span>
                                        <input type='password' value="" name="currentPassword" class="input" {{ session("userdata.isLdap") ? "disabled='disabled'" : '' }}
                                               id="currentPassword"/><br/>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="newPassword" >{{ __('label.new_password') }}</label>
                                    <span>
                                        <input type='password' value="" name="newPassword" class="input" {{ session("userdata.isLdap") ? "disabled='disabled'" : '' }}
                                               id="newPassword"/>
                                        <span id="pwStrength"></span>

                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="confirmPassword" >{{ __('label.password_repeat') }}</label>
                                    <span>
                                        <input type="password" value="" name="confirmPassword" class="input" {{ session("userdata.isLdap") ? "disabled='disabled'" : '' }}
                                               id="confirmPassword"/><br/>
                                        @if (!session("userdata.isLdap") );
                                        <small>{{ __('label.passwordRequirements') }}</small>
                                       @endif
                                    </span>

                                </div>
                            </div>
                            @if (!session("userdata.isLdap") );
                                <input type="hidden" name="savepw" value="1" />
                                <input type="submit" name="save" id="savePw" value="{{ __('buttons.save') }}" class="button"/>
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
                                        <select name="language" id="language" style="width: 220px">
                                            @foreach ($languageList as $languagKey => $languageValue )
                                                <option value="{{ $languagKey }}"
                                                        @if ($userLang == $languagKey )
                                                            selected='selected'
                                                         @endif >{{ $languageValue }}</option>
                                             @endforeach
                                        </select>
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="date_format" >{{ __('label.date_format') }}</label>
                                    <span>
                                        <select name="date_format" id="date_format" style="width: 220px">
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
                                        </select>
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="time_format" >{{ __('label.time_format') }}</label>
                                    <span>
                                        <select name="time_format" id="time_format" style="width: 220px">
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
                                        </select>
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="timezone" >{{ __('label.timezone') }}</label>
                                    <span>
                                        <select name="timezone" id="timezone" style="width: 220px">

                                            @foreach ($timezoneOptions as $tz)
                                                <option value="{{ $tz }}"
                                                        @if ($timezone === $tz )
                                                            selected='selected'
                                                        @endif
                                                        >{{ $tz }}</option>
                                            @endforeach
                                        </select>
                                    </span>
                                </div>
                            </div>
                            <input type="hidden" name="saveSettings" value="1" />
                            <input type="submit" name="save" id="saveSettings" value="{{ __('buttons.save') }}" class="button"/>
                        </form>
                    </div>

                    <div id="theme">
                        <form action="" method="post">
                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="themeSelect" >{{ __('label.theme') }}</label>
                                    <span class='field'>
                                        <select name="theme" id="themeSelect" style="width: 220px">

                                            @foreach ($availableThemes as $key => $theme)
                                                <option value="{{  $key  }}"
                                                    @if ($userTheme == $key)
                                                     selected='selected'
                                                   @endif >{{ __($theme['name']) }}</option>
                                            @endforeach
                                        </select>

                                    </span>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">

                                        <hr />
                                        <label for="colormode" >{{ __('label.colormode') }}</label>

                                        <x-global::forms.select-button :selected="($userColorMode == 'light') ? 'true' : ''" :id="'light'" :name="'colormode'" :value="'light'" :label="'Light'" onclick="leantime.snippets.toggleTheme('light')">
                                            <label for="colormode-light" class="tw-w-[100px]">
                                                <i class="fa-solid fa-sun tw-font-xxl"></i>
                                            </label>
                                        </x-global::forms.select-button>

                                        <x-global::forms.select-button :selected="($userColorMode == 'dark') ? 'true' : ''" :id="'dark'" :name="'colormode'" :value="'dark'" :label="'Dark'" onclick="leantime.snippets.toggleTheme('dark')">
                                            <label for="colormode-light" class="tw-w-[100px]">
                                                <i class="fa-solid fa-moon tw-font-xxl"></i>
                                            </label>
                                        </x-global::forms.select-button>

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <hr />
                                        <label>Font</label>
                                        @foreach($availableFonts as $key => $font)

                                            <x-global::forms.select-button  :selected="($themeFont == $font) ? 'true' : ''" :id="$key" :name="'themeFont'" :value="$font" :label="$font" onclick="leantime.snippets.toggleFont('{{ $font }}')">
                                                <label for="selectable-{{ $key }}" class="font tw-w-[200px]"
                                                       style="font-family:'{{ $font }}'; font-size:16px;">
                                                    The quick brown fox jumps over the lazy dog
                                                </label>
                                            </x-global::forms.select-button>

                                        @endforeach

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <hr />
                                        <label>Color Scheme</label>
                                        @foreach($availableColorSchemes as $key => $scheme )
                                            <x-global::forms.select-button class="circle" :selected="($userColorScheme == $key) ? 'true' : ''" :id="$key" :name="'colorscheme'" :value="$key" :label="__($scheme['name'])"  onclick="leantime.snippets.toggleColors('{{ $scheme['primaryColor'] }}','{{ $scheme['secondaryColor'] }}');">
                                                <label for="color-{{ $key }}" class="colorCircle"
                                                       style="background:linear-gradient(135deg, {{ $scheme["primaryColor"] }} 20%, {{ $scheme["secondaryColor"] }} 100%);">
                                                </label>
                                            </x-global::forms.select-button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <br /><br />
                            <input type="hidden" name="saveTheme" value="1" />
                            <input type="submit" name="save" id="saveTheme" value="{{ __('buttons.save') }}" class="button"/>
                        </form>
                    </div>

                    <div id="notifications">
                        <form action="" method="post">
                            <input type="hidden" name="{{ session("formTokenName") }}" value="{{ session("formTokenValue") }}" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="notifications" >{{ __('label.receive_notifications') }}</label>
                                    <span>
                                        <input type="checkbox" value="" name="notifications" class="input"
                                               id="notifications"
                                               @if ($values['notifications'] == "1" )
                                                   checked='checked'
                                               @endif/> <br/>
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="messagesfrequency" >{{ __('label.messages_frequency') }}</label>
                                    <span>
                                        <select name="messagesfrequency" class="input" id="messagesfrequency" style="width: 220px">
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
                                        </select> <br/>
                                    </span>
                                </div>
                            </div>
                            <input type="hidden" name="savenotifications" value="1" />
                            <input type="submit" name="save" value="{{ __('buttons.save') }}" class="button"/>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="maincontentinner center">
                <img src='{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}?v={{ format($user['modified'])->timestamp() }}'  class='profileImg tw-rounded-full' alt='Profile Picture' id="previousImage"/>
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


<script type="text/javascript">

    jQuery(document).ready(function(){

        leantime.usersController.checkPWStrength('newPassword');

        jQuery('.accountTabs').tabs();

        jQuery("#messagesfrequency").chosen();
        jQuery("#language").chosen();
        jQuery("#themeSelect").chosen();

    });
</script>

@endsection
