@extends($layout)

@section('content')

<x-global::content.pageheader :icon="'fa fa-user'">
    <h5>{{ __('label.overview') }}</h5>
    <h1>{!! __('headlines.accountSettings') !!}</h1>

</x-global::content.pageheader>

<div class="maincontent">

    @displayNotification()

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
                            <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
                        
                            <div class="row-fluid">
                                <div class="form-group">
                                    <x-global::forms.text-input
                                        name="firstname"
                                        id="firstname"
                                        class="input"
                                        :value="$values['firstname']"
                                        caption="{{ __('label.firstname') }}"
                                        :disabled="session('userdata.isLdap')"
                                    />
                                </div>
                            
                                <div class="form-group">
                                    <x-global::forms.text-input
                                        name="lastname"
                                        id="lastname"
                                        class="input"
                                        :value="$values['lastname']"
                                        caption="{{ __('label.lastname') }}"
                                        :disabled="session('userdata.isLdap')"
                                    />
                                </div>
                            
                                <div class="form-group">
                                    <x-global::forms.text-input
                                        name="user"
                                        id="user"
                                        class="input"
                                        :value="$values['user']"
                                        caption="{{ __('label.email') }}"
                                        :disabled="session('userdata.isLdap')"
                                    />
                                </div>
                            
                                <div class="form-group">
                                    <x-global::forms.text-input
                                        name="phone"
                                        id="phone"
                                        class="input"
                                        :value="$values['phone']"
                                        caption="{{ __('label.phone') }}"
                                        :disabled="session('userdata.isLdap')"
                                    />
                                </div>
                            </div>
                            
                            
                            
                        
                            <p class="stdformbutton">
                                <input type="hidden" name="profileInfo" value="1" />
                                
                                <x-global::forms.button 
                                    type="submit" 
                                    name="save" 
                                    id="save"
                                    class="button"
                                >
                                    {{ __('buttons.save') }}
                                </x-global::forms.button>
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
                                    <x-global::forms.select name="language" id="language" :labelText="__('label.language')">
                                        @foreach ($languageList as $languagKey => $languageValue)
                                            <x-global::forms.select.select-option :value="$languagKey" :selected="$userLang == $languagKey">
                                                {{ $languageValue }}
                                            </x-global::forms.select.select-option>
                                        @endforeach
                                    </x-global::forms.select>
                                </div>
                                
                                <div class="form-group">
                                    <x-global::forms.select name="date_format" id="date_format" :labelText="__('label.date_format')">
                                        @php
                                            $dateFormats = $dateTimeValues['dates'];
                                            $dateTimeNow = date_create();
                                        @endphp
                                
                                        @foreach ($dateFormats as $format)
                                            <x-global::forms.select.select-option :value="$format" :selected="$dateFormat == $format">
                                                {{ date_format($dateTimeNow, $format) }}
                                            </x-global::forms.select.select-option>
                                        @endforeach
                                    </x-global::forms.select>
                                </div>
                                
                                <div class="form-group">
                                    <x-global::forms.select name="time_format" id="time_format" :labelText="__('label.time_format')">
                                        @php
                                            $timeFormats = $dateTimeValues['times'];
                                            $dateTimeNow = date_create();
                                        @endphp
                                
                                        @foreach ($timeFormats as $format)
                                            <x-global::forms.select.select-option :value="$format" :selected="$timeFormat == $format">
                                                {{ date_format($dateTimeNow, $format) }}
                                            </x-global::forms.select.select-option>
                                        @endforeach
                                    </x-global::forms.select>
                                </div>
                                
                                <div class="form-group">
                                    <x-global::forms.select name="timezone" id="timezone" :labelText="__('label.timezone')">
                                        @foreach ($timezoneOptions as $tz)
                                            <x-global::forms.select.select-option :value="$tz" :selected="$timezone === $tz">
                                                {{ $tz }}
                                            </x-global::forms.select.select-option>
                                        @endforeach
                                    </x-global::forms.select>
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
                                    <x-global::forms.select name="theme" id="themeSelect" :labelText="__('label.theme')">
                                        @foreach ($availableThemes as $key => $theme)
                                            <x-global::forms.select.select-option :value="$key" :selected="$userTheme == $key">
                                                {!! __($theme['name']) !!}
                                            </x-global::forms.select.select-option>
                                        @endforeach
                                    </x-global::forms.select>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">

                                        <hr />
                                        <label for="colormode" >{{ __('label.colormode') }}</label>

                                        <x-global::forms.select-button :selected="($userColorMode == 'light') ? 'true' : ''" :id="'light'" :name="'colormode'" :value="'light'" :label="'Light'" onclick="leantime.snippets.toggleTheme('light')">
                                            <label for="colormode-light" class="w-[100px]">
                                                <i class="fa-solid fa-sun font-xxl"></i>
                                            </label>
                                        </x-global::forms.select-button>

                                        <x-global::forms.select-button :selected="($userColorMode == 'dark') ? 'true' : ''" :id="'dark'" :name="'colormode'" :value="'dark'" :label="'Dark'" onclick="leantime.snippets.toggleTheme('dark')">
                                            <label for="colormode-light" class="w-[100px]">
                                                <i class="fa-solid fa-moon font-xxl"></i>
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
                                                <label for="selectable-{{ $key }}" class="font w-[200px]"
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
                                    <x-global::forms.checkbox
                                        name="notifications"
                                        id="notifications"
                                        :checked="$values['notifications'] == '1'"
                                        class="input"
                                        labelText="{{ __('label.receive_notifications') }}"
                                        labelPosition="left"
                                    />    
                                    <br/>
                                </div>
                                <div class="form-group">
                                    <label for="messagesfrequency" >{{ __('label.messages_frequency') }}</label>
                                    <span>
                                        <x-global::forms.select name="messagesfrequency" class="input" id="messagesfrequency" :labelText="__('label.choose_option')">
                                            <x-global::forms.select.select-option value="">
                                                --{{ __('label.choose_option') }}--
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="60" :selected="$values['messagesfrequency'] == '60'">
                                                {{ __('label.1min') }}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="300" :selected="$values['messagesfrequency'] == '300'">
                                                {{ __('label.5min') }}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="900" :selected="$values['messagesfrequency'] == '900'">
                                                {{ __('label.15min') }}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="1800" :selected="$values['messagesfrequency'] == '1800'">
                                                {{ __('label.30min') }}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="3600" :selected="$values['messagesfrequency'] == '3600'">
                                                {{ __('label.1h') }}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="10800" :selected="$values['messagesfrequency'] == '10800'">
                                                {{ __('label.3h') }}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="36000" :selected="$values['messagesfrequency'] == '36000'">
                                                {{ __('label.6h') }}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="43200" :selected="$values['messagesfrequency'] == '43200'">
                                                {{ __('label.12h') }}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="86400" :selected="$values['messagesfrequency'] == '86400'">
                                                {{ __('label.24h') }}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="172800" :selected="$values['messagesfrequency'] == '172800'">
                                                {{ __('label.48h') }}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="604800" :selected="$values['messagesfrequency'] == '604800'">
                                                {{ __('label.1w') }}
                                            </x-global::forms.select.select-option>
                                        </x-global::forms.select>
                                        <br/>
                                        
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
                <img src='{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}?v={{ format($user['modified'])->timestamp() }}'  class='profileImg rounded-full' alt='Profile Picture' id="previousImage"/>
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
