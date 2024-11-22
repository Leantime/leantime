@extends($layout)

@section('content')
    <?php
    $companySettings = $tpl->get('companySettings');
    ?>

    <div class="pageheader">

        <div class="pageicon"><span class="fa fa-cogs"></span></div>
        <div class="pagetitle">
            <h5><?= $tpl->__('label.administration') ?></h5>
            <h1><?= $tpl->__('headlines.company_settings') ?></h1>
        </div>
    </div>

<div class="maincontent">
    @displayNotification()
    <div class="maincontentinner">
        <div class="row">
            <div class="col-md-12">

                    <div class="tabbedwidget tab-primary companyTabs">

                    <ul>
                        <li><a href="#details"><span class="fa fa-building"></span> {{ __("tabs.details") }}</a></li>
                        <li><a href="#apiKeys"><i class="fa-solid fa-key"></i> {{ __("tabs.apiKeys") }}</a></li>
                    </ul>


                        <div id="details">


                        <div class="row">
                            <div class="col-md-8">
                                <form class="" method="post" id="" action="{{ BASE_URL }}/setting/editCompanySettings#details" >

                                        <h5 class="subtitle"><?= $tpl->__('headlines.company_settings') ?></h5>
                                        <p><?= $tpl->__('text.these_are_system_wide_settings') ?></p>
                                        <br />
                                        <input type="hidden" value="1" name="saveSettings" />

                            <h4 class="widgettitle title-light"><span
                                    class="fa fa-building"></span>{{ __("subtitles.companydetails") }}
                            </h4>
                            <div class="row">
                                <div class="col-md-2">
                                    <label><?=$tpl->__("label.language")?></label>
                                </div>
                                <div class="col-md-8">
                                    <x-global::forms.select name="language" id="language">
                                        @foreach ($tpl->get('languageList') as $languageKey => $languageValue)
                                            <x-global::forms.select.select-option :value="$languageKey" :selected="$companySettings['language'] == $languageKey">
                                                {!! $languageValue !!}
                                            </x-global::forms.select.select-option>
                                        @endforeach
                                    </x-global::forms.select>
                                    


                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-2">
                                                <label><?= $tpl->__('label.company_name') ?></label>
                                            </div>
                                            <div class="col-md-8">
                                                <x-global::forms.text-input 
                                                    type="text" 
                                                    name="name" 
                                                    id="companyName" 
                                                    value="{!! $companySettings['name'] !!}" 
                                                    class="pull-left" 
                                                />
                                            
                                                <small><?= $tpl->__('text.company_name_helper') ?></small>
                                            </div>
                                        </div>
                                        <br />

                                        <?php $tpl->dispatchTplEvent('beforeTelemetrySettings'); ?>

                                        <div class="row" id="telemetryContainer">
                                            <div class="col-md-2">
                                                <label><?= $tpl->__('label.send_telemetry') ?></label>
                                            </div>
                                            <div class="col-md-8">
                                                <x-global::forms.checkbox
                                                    name="telemetryActive"
                                                    id="telemetryActive"
                                                    :checked="isset($companySettings['telemetryActive']) ? $companySettings['telemetryActive'] : false"
                                                    class="toggle"
                                                />

                                    <i class="fa fa-question-circle" style="vertical-align: bottom;" data-tippy-content="<?=strip_tags($tpl->__("label.telemetry_background")) ?>"></i>
                                    <div class="clearall"></div><br />
                                </div>
                            </div>
                                    <br />
                            <h4 class="widgettitle title-light"><span
                                    class="fa fa-cog"></span>{{ __("subtitles.defaults") }}
                            </h4>
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="messageFrequency">{{ __("label.messages_frequency") }}</label>
                                </div>
                                <div class="col-md-8">
                                    <span class='field'>
                                        <x-global::forms.select name="messageFrequency" id="messageFrequency" :labelText="__('label.choose_option')">
                                            <x-global::forms.select.select-option value="">
                                                --{!! __('label.choose_option') !!}--
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="300" :selected="$companySettings['messageFrequency'] == '300'">
                                                {!! __('label.5min') !!}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="900" :selected="$companySettings['messageFrequency'] == '900'">
                                                {!! __('label.15min') !!}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="1800" :selected="$companySettings['messageFrequency'] == '1800'">
                                                {!! __('label.30min') !!}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="3600" :selected="$companySettings['messageFrequency'] == '3600'">
                                                {!! __('label.1h') !!}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="10800" :selected="$companySettings['messageFrequency'] == '10800'">
                                                {!! __('label.3h') !!}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="36000" :selected="$companySettings['messageFrequency'] == '36000'">
                                                {!! __('label.6h') !!}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="43200" :selected="$companySettings['messageFrequency'] == '43200'">
                                                {!! __('label.12h') !!}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="86400" :selected="$companySettings['messageFrequency'] == '86400'">
                                                {!! __('label.24h') !!}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="172800" :selected="$companySettings['messageFrequency'] == '172800'">
                                                {!! __('label.48h') !!}
                                            </x-global::forms.select.select-option>
                                        
                                            <x-global::forms.select.select-option value="604800" :selected="$companySettings['messageFrequency'] == '604800'">
                                                {!! __('label.1w') !!}
                                            </x-global::forms.select.select-option>
                                        </x-global::forms.select>
                                        
                                        <br/>
                                    </span>
                                </div>
                            </div>
                            <x-global::forms.button type="submit" id="saveBtn">
                                {{ __('buttons.save') }}
                            </x-global::forms.button>
                            </form>
                            </div>
                            <div class="col-md-4">

                                <form class="" method="post" id="" action="{{ BASE_URL }}/setting/editCompanySettings" >
                                    <input type="hidden" value="1" name="saveLogo" />
                                    <h5 class="subtitle"><?=$tpl->__("headlines.logo")?></h5>
                                    <br />

                                        <div class="row">

                                            <div class="col-md-12">
                                                <?php if ($companySettings['logo'] != "") { ?>
                                                <img src='<?php echo $companySettings['logo']; ?>' class='logoImg' alt='Logo'
                                                    id="previousImage" width="260" />
                                                <?php } else { ?>
                                                <?= $tpl->__('text.no_logo') ?>
                                                <?php } ?>
                                                <div id="logoImg" style="height:auto;">
                                                </div>
                                                <br />
                                                <div class="par">

                                                    <label><?= $tpl->__('label.upload_new_logo') ?></label>

                                                    <div class='fileupload fileupload-new' data-provides='fileupload'>
                                                        <input type="hidden" />
                                                        <div class="input-append">
                                                            <div class="uneditable-input span3">
                                                                <i class="fa-file fileupload-exists"></i>
                                                                <span class="fileupload-preview"></span>
                                                            </div>
                                                            <span class="btn btn-default btn-file">
                                                                <span
                                                                    class="fileupload-new"><?= $tpl->__('buttons.select_file') ?></span>
                                                                <span
                                                                    class='fileupload-exists'><?= $tpl->__('buttons.change') ?></span>
                                                                <input type='file' name='file'
                                                                    onchange="leantime.settingController.readURL(this)" />
                                                            </span>

                                                        <a href='#' style="margin-left:5px;" class='btn btn-default fileupload-exists' data-dismiss='fileupload' onclick="leantime.usersController.clearCroppie()"><?=$tpl->__("buttons.remove")?></a>
                                                    </div>
                                                    <p class='stdformbutton'>
                                                        <x-global::forms.button tag="button" id="saveBtn" onclick="leantime.settingController.saveCroppie()">
                                                            {{ __('buttons.save') }}
                                                        </x-global::forms.button>
                                                        <input id="picSubmit" type="submit" name="savePic" class="hidden" value="<?=$tpl->__("buttons.upload")?>" />
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <hr />
                                <?=$tpl->__("text.logo_reset")?><br /><br />
                                {{-- <a href="{{ BASE_URL }}/setting/editCompanySettings?resetLogo=1" class="btn btn-default"><?=$tpl->__("buttons.reset_logo")?></a> --}}
                                <x-global::forms.button tag="a" contentRole="ghost" href="{{ BASE_URL }}/setting/editCompanySettings?resetLogo=1" scale="sm">
                                    {{ __("buttons.reset_logo") }}
                                </x-global::forms.button>
                            </div>
                        </div>
                </div>

                        <div id="apiKeys">
                            <a href="#/api/newApiKey" class="btn btn-primary">Generate API Key</a>
                            <br /> <br />
                            <ul class="sortableTicketList">


                                <?php foreach ($tpl->get('apiKeys') as $apiKey) { ?>
                                <li>
                                    <div class="ticketBox">
                                        <x-global::content.context-menu
                                            label-text="<i class='fa fa-ellipsis-v' aria-hidden='true'></i>"
                                            contentRole="link" position="bottom" align="start" class="ticketDropDown">

                                            <!-- Menu Items -->
                                            <x-global::actions.dropdown.item href="#/api/apiKey/{{ $apiKey['id'] }}">
                                                <i class="fa fa-edit"></i> Edit Key
                                            </x-global::actions.dropdown.item>
                                            <x-global::actions.dropdown.item
                                                href="{{ BASE_URL }}/api/delAPIKey/{{ $apiKey['id'] }}"
                                                class="delete">
                                                <i class="fa fa-trash"></i> Delete Key
                                            </x-global::actions.dropdown.item>
                                        </x-global::content.context-menu>

                                        <a href="#/api/apiKey/<?=$apiKey["id"] ?>"><strong><?=$apiKey["firstname"] ?></strong></a><br />
                                    lt_<?=$apiKey["username"] ?>***
                                    | <?=$tpl->__("labels.created_on")?>: <?=format($apiKey["createdOn"])->date() ?> | <?=$tpl->__("labels.last_used")?>: <?= format($apiKey["lastlogin"])->date() ?>

                                    </div>
                                </li>
                                <?php } ?>
                            </ul>

                        </div>

                    </div>
                </div>
            </div>
        </div>




    </div>


    <script>
        jQuery(document).ready(function() {
            jQuery(".companyTabs").tabs({
                activate: function(event, ui) {

                    window.location.hash = ui.newPanel.selector;
                }
            });
        });
    </script>
@endsection
