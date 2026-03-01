@php
    $project = $tpl->get('project');
    $state = $tpl->get('state');
@endphp

<div class="pageheader">
    <div class="pageicon"><x-global::elements.icon name="luggage" /></div>
    <div class="pagetitle">
        <h5>{{ __('label.administration') }}</h5>
        <h1>{{ sprintf(__('headline.project'), e($project['name'])) }}</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="inlineDropDownContainer" style="float:right; z-index:9; padding-top:2px;">
            <x-globals::forms.button link="{{ BASE_URL }}/projects/duplicateProject/{{ $project['id'] }}" type="secondary" class="duplicateProjectModal" data-tippy-content="{{ __('link.duplicate_project') }}" icon="content_copy">Copy</x-globals::forms.button>
            <x-globals::forms.button link="{{ BASE_URL }}/projects/delProject/{{ $project['id'] }}" type="danger" class="delete" data-tippy-content="{{ __('link.delete_project') }}" outline icon="delete">Delete</x-globals::forms.button>
        </div>
        <div class="lt-tabs tabbedwidget projectTabs" data-tabs>

            <ul role="tablist">
                <li><a href="#projectdetails"><x-global::elements.icon name="eco" /> {{ __('tabs.projectdetails') }}</a></li>
                <li><a href="#team"><x-global::elements.icon name="group" /> {{ __('tabs.team') }}</a></li>
                <li><a href="#integrations"> <x-global::elements.icon name="emergency" /> {{ __('tabs.Integrations') }}</a></li>
                <li><a href="#todosettings"><x-global::elements.icon name="format_list_bulleted" /> {{ __('tabs.todosettings') }}</a></li>
                @dispatchEvent('projectTabsList')
            </ul>

            <div id="projectdetails">
                @php $tpl->displaySubmodule('projects-projectDetails') @endphp
            </div>

            <div id="team">
                <form method="post" action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#team">
                    <input type="hidden" name="saveUsers" value="1" />

                    <div class="row-fluid">
                    <div class="span12">

                         <div class="form-group">
                             <br />{!! __('text.choose_access_for_users') !!}<br />
                             <br />

                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="widgettitle title-light">
                                        <x-global::elements.icon name="group" />{{ __('headlines.team_member') }}
                                    </h4>
                                </div>
                            </div>

                             <div class="row">
                                @foreach($project['assignedUsers'] as $userId => $assignedUser)
                                    <div class="col-md-4">
                                        <div class="userBox">
                                            <x-globals::forms.checkbox name="editorId[]" id="user-{{ $assignedUser['id'] }}" value="{{ $assignedUser['id'] }}"
                                                :checked="true" />
                                            <div class="commentImage">
                                                <img src="{{ BASE_URL }}/api/users?profileImage={{ $assignedUser['id'] }}&v={{ format($assignedUser['modified'])->timestamp() }}"/>
                                            </div>
                                            <label for="user-{{ $assignedUser['id'] }}">{{ sprintf(__('text.full_name'), e($assignedUser['firstname']), e($assignedUser['lastname'])) }}
                                                @if($assignedUser['jobTitle'] != '')
                                                    <small>
                                                        {{ e($assignedUser['jobTitle']) }}
                                                    </small>
                                                    <br/>
                                                @endif
                                                @if($assignedUser['source'] == 'api')
                                                    <small>
                                                        API Access
                                                    </small>
                                                    <br/>
                                                @endif
                                                @if($assignedUser['status'] == 'i')
                                                    <small>{{ __('label.invited') }}</small>
                                                @endif
                                            </label>
                                            @if($roles::getRoles()[$assignedUser['role']] == $roles::$admin || $roles::getRoles()[$assignedUser['role']] == $roles::$owner)
                                                <x-globals::forms.input :bare="true" name="role-{{ $assignedUser['id'] }}" readonly disabled value="{{ __('label.roles.' . $roles::getRoles()[$assignedUser['role']]) }}" />
                                            @else
                                                <x-globals::forms.select name="userProjectRole-{{ $assignedUser['id'] }}">
                                                    <option value="inherit">Inherit</option>
                                                    <option value="{{ array_search($roles::$readonly, $roles::getRoles()) }}"
                                                        {{ $assignedUser['projectRole'] == array_search($roles::$readonly, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$readonly) }}</option>
                                                    <option value="{{ array_search($roles::$commenter, $roles::getRoles()) }}"
                                                        {{ $assignedUser['projectRole'] == array_search($roles::$commenter, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$commenter) }}</option>
                                                    <option value="{{ array_search($roles::$editor, $roles::getRoles()) }}"
                                                        {{ $assignedUser['projectRole'] == array_search($roles::$editor, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$editor) }}</option>
                                                    <option value="{{ array_search($roles::$manager, $roles::getRoles()) }}"
                                                        {{ $assignedUser['projectRole'] == array_search($roles::$manager, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$manager) }}</option>
                                                </x-globals::forms.select>
                                            @endif
                                            <div class="clearall"></div>
                                        </div>
                                    </div>
                                @endforeach
                             </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="widgettitle title-light">
                                        <x-global::elements.icon name="group" />{{ __('headlines.assign_users_to_project') }}
                                    </h4>
                                </div>
                            </div>

                             <div class="row">
                                @foreach($tpl->get('availableUsers') as $row)
                                    @if(collect($project['assignedUsers'])->where('id', $row['id'])->isEmpty())
                                        <div class="col-md-4">
                                            <div class="userBox">
                                                <x-globals::forms.checkbox name="editorId[]" id="user-{{ $row['id'] }}" value="{{ $row['id'] }}" />

                                                <div class="commentImage">
                                                    <img src="{{ BASE_URL }}/api/users?profileImage={{ $row['id'] }}&v={{ format($row['modified'])->timestamp() }}"/>
                                                </div>
                                                <label for="user-{{ $row['id'] }}">{{ sprintf(__('text.full_name'), e($row['firstname']), e($row['lastname'])) }}</label>
                                                @if($roles::getRoles()[$row['role']] == $roles::$admin || $roles::getRoles()[$row['role']] == $roles::$owner)
                                                    <x-globals::forms.input :bare="true" name="role-{{ $row['id'] }}" readonly disabled value="{{ __('label.roles.' . $roles::getRoles()[$row['role']]) }}" />
                                                @else
                                                    @php $assignedUserMatch = collect($project['assignedUsers'])->where('id', $row['id'])->first(); @endphp
                                                    <x-globals::forms.select name="userProjectRole-{{ $row['id'] }}">
                                                        <option value="inherit">Inherit</option>
                                                        <option value="{{ array_search($roles::$readonly, $roles::getRoles()) }}"
                                                            {{ $assignedUserMatch && $assignedUserMatch['projectRole'] == array_search($roles::$readonly, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$readonly) }}</option>
                                                        <option value="{{ array_search($roles::$commenter, $roles::getRoles()) }}"
                                                            {{ $assignedUserMatch && $assignedUserMatch['projectRole'] == array_search($roles::$commenter, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$commenter) }}</option>
                                                        <option value="{{ array_search($roles::$editor, $roles::getRoles()) }}"
                                                            {{ $assignedUserMatch && $assignedUserMatch['projectRole'] == array_search($roles::$editor, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$editor) }}</option>
                                                        <option value="{{ array_search($roles::$manager, $roles::getRoles()) }}"
                                                            {{ $assignedUserMatch && $assignedUserMatch['projectRole'] == array_search($roles::$manager, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$manager) }}</option>
                                                    </x-globals::forms.select>
                                                @endif
                                                <div class="clearall"></div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                                @if($login::userIsAtLeast($roles::$manager))
                                    <div class="col-md-4">
                                        <div class="userBox">
                                            <a class="userEditModal" href="{{ BASE_URL }}/users/newUser?preSelectProjectId={{ $project['id'] }}" style="font-size:var(--font-size-l); line-height:61px"><x-global::elements.icon name="person_add" /> {{ __('links.create_user') }}</a>
                                            <div class="clearall"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                             <div class="row">
                                 <div class="col-md-12">

                                 </div>
                             </div>
                        </div>
                    </div>
                </div>
                    <br/>
                    <x-globals::forms.button submit type="primary" name="saveUsers" id="save">{{ __('buttons.save') }}</x-globals::forms.button>

                </form>

            </div>

            <div id="integrations">

                <h4 class="widgettitle title-light"><x-global::elements.icon name="eco" />Mattermost</h4>
                <div class="row">
                    <div class="col-md-3">
                        <img src="{{ BASE_URL }}/dist/images/mattermost-logoHorizontal.png" width="200" />
                    </div>
                    <div class="col-md-5">
                        {{ __('text.mattermost_instructions') }}
                    </div>
                    <div class="col-md-4">
                        <strong>{{ __('label.webhook_url') }}</strong><br />
                        <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#integrations" method="post">
                            <x-globals::forms.input name="mattermostWebhookURL" id="mattermostWebhookURL" value="{{ e($tpl->get('mattermostWebhookURL')) }}" />
                            <br />
                            <x-globals::forms.button submit type="primary" name="mattermostSave">{{ __('buttons.save') }}</x-globals::forms.button>
                        </form>
                    </div>
                </div>
                <br />
                <h4 class="widgettitle title-light"><x-global::elements.icon name="eco" />Slack</h4>
                <div class="row">
                    <div class="col-md-3">
                        <img src="https://cdn.cdnlogo.com/logos/s/52/slack.svg" width="200"/>
                    </div>

                    <div class="col-md-5">
                        {{ __('text.slack_instructions') }}
                    </div>
                    <div class="col-md-4">
                        <strong>{{ __('label.webhook_url') }}</strong><br />
                        <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#integrations" method="post">
                            <x-globals::forms.input name="slackWebhookURL" id="slackWebhookURL" value="{{ e($tpl->get('slackWebhookURL')) }}" />
                            <br />
                            <x-globals::forms.button submit type="primary" name="slackSave">{{ __('buttons.save') }}</x-globals::forms.button>
                        </form>
                    </div>
                </div>

                <h4 class="widgettitle title-light"><x-global::elements.icon name="eco" />Zulip</h4>
                <div class="row">
                    <div class="col-md-3">
                        <img src="{{ BASE_URL }}/dist/images/zulip-org-logo.png" width="200"/>
                    </div>

                    <div class="col-md-5">
                        {{ __('text.zulip_instructions') }}
                    </div>
                    <div class="col-md-4">
                        <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#integrations" method="post">
                            <strong>{{ __('label.base_url') }}</strong><br />
                            <x-globals::forms.input name="zulipURL" id="zulipURL" placeholder="{{ __('input.placeholders.zulip_url') }}" value="{{ $tpl->get('zulipHook')['zulipURL'] }}" />
                            <br />
                            <strong>{{ __('label.bot_email') }}</strong><br />
                            <x-globals::forms.input name="zulipEmail" id="zulipEmail" value="{{ e($tpl->get('zulipHook')['zulipEmail']) }}" />
                            <br />
                            <strong>{{ __('label.botkey') }}</strong><br />
                            <x-globals::forms.input name="zulipBotKey" id="zulipBotKey" value="{{ e($tpl->get('zulipHook')['zulipBotKey']) }}" />
                            <br />
                            <strong>{{ __('label.stream') }}</strong><br />
                            <x-globals::forms.input name="zulipStream" id="zulipStream" value="{{ e($tpl->get('zulipHook')['zulipStream']) }}" />
                            <br />
                            <strong>{{ __('label.topic') }}</strong><br />
                            <x-globals::forms.input name="zulipTopic" id="zulipTopic" value="{{ e($tpl->get('zulipHook')['zulipTopic']) }}" />
                            <br />
                            <x-globals::forms.button submit type="primary" name="zulipSave">{{ __('buttons.save') }}</x-globals::forms.button>
                        </form>
                    </div>
                </div>

                <h4 class='widgettitle title-light'><x-global::elements.icon name="eco" />Discord</h4>
                <div class='row'>
                    <div class='col-md-3'>
                        <img src='{{ BASE_URL }}/dist/images/discord-logo.png' width='200'/>
                    </div>

                    <div class='col-md-5'>
                      {{ __('text.discord_instructions') }}
                    </div>
                    <div class="col-md-4">
                        <strong>{{ __('label.webhook_url') }}</strong><br/>
                        <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#integrations" method="post">
                            @for($i = 1; $i <= 3; $i++)
                            <x-globals::forms.input name="discordWebhookURL{{ $i }}" id="discordWebhookURL{{ $i }}" placeholder="{{ __('input.placeholders.discord_url') }}" value="{{ e($tpl->get('discordWebhookURL' . $i)) }}" /><br/>
                            @endfor
                            <x-globals::forms.button submit type="primary" name="discordSave">{{ __('buttons.save') }}</x-globals::forms.button>
                        </form>
                    </div>
                </div>

            </div>

            <div id="todosettings">
<form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#todosettings" method="post">
                    <div class="row statusListHeader">
                        <div class="statusListHeader-handle"></div>
                        <div class="col-md-1">{{ __('label.sortindex') }}</div>
                        <div class="col-md-3">{{ __('label.label') }}</div>
                        <div class="col-md-2">{{ __('label.color') }}</div>
                        <div class="col-md-2">{{ __('label.reportType') }}</div>
                        <div class="col-md-2">{{ __('label.showInKanban') }}</div>
                        <div class="statusListHeader-remove"></div>
                    </div>
                    <ul class="sortableTicketList" id="todoStatusList">
                        @foreach($tpl->get('todoStatus') as $key => $ticketStatus)
                            <li>
                                <div class="ticketBox">

                                    <div class="row statusList" id="todostatus-{{ $key }}">

                                        <input type="hidden" name="labelKeys[]" id="labelKey-{{ $key }}" class='labelKey' value="{{ $key }}"/>
                                        <div class="sortHandle">
                                            <x-global::elements.icon name="sort" />
                                        </div>
                                        <div class="col-md-1">
                                            <x-globals::forms.input :bare="true" type="text" name="labelSort-{{ $key }}" id="labelSort-{{ $key }}" value="{{ e($ticketStatus['sortKey']) }}" class="sorter" style="width:50px;" />
                                        </div>
                                        <div class="col-md-3">
                                            <x-globals::forms.input :bare="true" name="label-{{ $key }}" id="label-{{ $key }}" value="{{ e($ticketStatus['name']) }}" :readonly="$key == -1" />
                                        </div>
                                        <div class="col-md-2">
                                            <x-globals::forms.select :bare="true" name="labelClass-{{ $key }}" id="labelClass-{{ $key }}" class="colorChosen">
                                                <option value="label-purple" class="label-purple" {{ $ticketStatus['class'] == 'label-purple' ? 'selected="selected"' : '' }}><span class="label-purple">{{ __('label.purple') }}</span></option>
                                                <option value="label-pink" class="label-pink" {{ $ticketStatus['class'] == 'label-pink' ? 'selected="selected"' : '' }}><span class="label-pink">{{ __('label.pink') }}</span></option>
                                                <option value="label-darker-blue" class="label-darker-blue" {{ $ticketStatus['class'] == 'label-darker-blue' ? 'selected="selected"' : '' }}><span class="label-darker-blue">{{ __('label.darker-blue') }}</span></option>
                                                <option value="label-info" class="label-info" {{ $ticketStatus['class'] == 'label-info' ? 'selected="selected"' : '' }}><span class="label-info">{{ __('label.dark-blue') }}</span></option>
                                                <option value="label-blue" class="label-blue" {{ $ticketStatus['class'] == 'label-blue' ? 'selected="selected"' : '' }}><span class="label-blue">{{ __('label.blue') }}</span></option>
                                                <option value="label-dark-green" class="label-dark-green" {{ $ticketStatus['class'] == 'label-dark-green' ? 'selected="selected"' : '' }}><span class="label-dark-green">{{ __('label.dark-green') }}</span></option>
                                                <option value="label-success" class="label-success" {{ $ticketStatus['class'] == 'label-success' ? 'selected="selected"' : '' }}><span class="label-success">{{ __('label.green') }}</span></option>
                                                <option value="label-warning" class="label-warning" {{ $ticketStatus['class'] == 'label-warning' ? 'selected="selected"' : '' }}><span class="label-warning">{{ __('label.yellow') }}</span></option>
                                                <option value="label-brown" class="label-brown" {{ $ticketStatus['class'] == 'label-brown' ? 'selected="selected"' : '' }}><span class="label-brown">{{ __('label.brown') }}</span></option>
                                                <option value="label-danger" class="label-danger" {{ $ticketStatus['class'] == 'label-danger' ? 'selected="selected"' : '' }}><span class="label-danger">{{ __('label.dark-red') }}</span></option>
                                                <option value="label-important" class="label-important" {{ $ticketStatus['class'] == 'label-important' ? 'selected="selected"' : '' }}><span class="label-important">{{ __('label.red') }}</span></option>
                                                <option value="label-default" class="label-default" {{ $ticketStatus['class'] == 'label-default' ? 'selected="selected"' : '' }}><span class="label-default">{{ __('label.grey') }}</span></option>
                                            </x-globals::forms.select>
                                        </div>
                                        <div class="col-md-2">
                                            <x-globals::forms.select name="labelType-{{ $key }}" id="labelType-{{ $key }}">
                                                <option value="NEW" {{ ($ticketStatus['statusType'] == 'NEW') ? 'selected="selected"' : '' }}>{{ __('status.new') }}</option>
                                                <option value="INPROGRESS" {{ ($ticketStatus['statusType'] == 'INPROGRESS') ? 'selected="selected"' : '' }}>{{ __('status.in_progress') }}</option>
                                                <option value="DONE" {{ ($ticketStatus['statusType'] == 'DONE') ? 'selected="selected"' : '' }}>{{ __('status.done') }}</option>
                                                <option value="NONE" {{ ($ticketStatus['statusType'] == 'NONE') ? 'selected="selected"' : '' }}>{{ __('status.dont_report') }}</option>
                                            </x-globals::forms.select>
                                        </div>
                                        <div class="col-md-2">
                                            <x-globals::forms.checkbox name="labelKanbanCol-{{ $key }}" id="labelKanbanCol-{{ $key }}" :checked="(bool) $ticketStatus['kanbanCol']" />
                                        </div>
                                        <div class="remove">
                                            @if($key != -1)
                                                <a href="javascript:void(0);" onclick="leantime.projectsController.removeStatus({{ $key }})" class="delete" aria-label="{{ __('label.remove') }}"><x-global::elements.icon name="delete" /></a>
                                            @endif
                                        </div>
                                    </div>

                                    @if($key == -1)
                                        <em>* the archive status is protected cannot be renamed or removed.</em>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <a href="javascript:void(0);" onclick="leantime.projectsController.addToDoStatus();" class="quickAddLink" style="text-align:left;">{!! __('links.add_status') !!}</a>
                    <br />
                    <x-globals::forms.button submit type="primary" name="submitSettings">{{ __('buttons.save') }}</x-globals::forms.button>
                </form>
            </div>

            @dispatchEvent('projectTabsContent')
        </div>
    </div>
</div>


<!-- New Status Template -->
<div class="newStatusTpl" style="display:none;">
    <div class="ticketBox">
    <div class="row statusList" id="todostatus-XXNEWKEYXX">
        <input type="hidden" name="labelKeys[]" id="labelKey-XXNEWKEYXX" class='labelKey' value="XXNEWKEYXX"/>
        <div class="sortHandle">
            <x-global::elements.icon name="sort" />
        </div>
        <div class="col-md-1">
            <x-globals::forms.input :bare="true" type="text" name="labelSort-XXNEWKEYXX" id="labelSort-XXNEWKEYXX" value="" class="sorter" style="width:50px;" />
        </div>
        <div class="col-md-3">
            <x-globals::forms.input :bare="true" name="label-XXNEWKEYXX" id="label-XXNEWKEYXX" value="" />
        </div>
        <div class="col-md-2">
            <x-globals::forms.select :bare="true" name="labelClass-XXNEWKEYXX" id="labelClass-XXNEWKEYXX" class="colorChosen">
                <option value="label-blue" class="label-blue"><span class="label-blue">{{ __('label.blue') }}</span></option>
                <option value="label-info" class="label-info"><span class="label-info">{{ __('label.dark-blue') }}</span></option>
                <option value="label-darker-blue" class="label-darker-blue"><span class="label-darker-blue">{{ __('label.darker-blue') }}</span></option>
                <option value="label-warning" class="label-warning"><span class="label-warning">{{ __('label.yellow') }}</span></option>
                <option value="label-success" class="label-success"><span class="label-success">{{ __('label.green') }}</span></option>
                <option value="label-dark-green" class="label-dark-green"><span class="label-dark-green">{{ __('label.dark-green') }}</span></option>
                <option value="label-important" class="label-important"><span class="label-important">{{ __('label.red') }}</span></option>
                <option value="label-danger" class="label-danger"><span class="label-danger">{{ __('label.dark-red') }}</span></option>
                <option value="label-pink" class="label-pink"><span class="label-pink">{{ __('label.pink') }}</span></option>
                <option value="label-purple" class="label-purple"><span class="label-purple">{{ __('label.purple') }}</span></option>
                <option value="label-brown" class="label-brown"><span class="label-brown">{{ __('label.brown') }}</span></option>
                <option value="label-default" class="label-default"><span class="label-default">{{ __('label.grey') }}</span></option>
            </x-globals::forms.select>
        </div>
        <div class="col-md-2">
            <x-globals::forms.select name="labelType-XXNEWKEYXX" id="labelType-XXNEWKEYXX">
                <option value="NEW">{{ __('status.new') }}</option>
                <option value="INPROGRESS">{{ __('status.in_progress') }}</option>
                <option value="DONE">{{ __('status.done') }}</option>
                <option value="NONE">{{ __('status.dont_report') }}</option>
            </x-globals::forms.select>
        </div>
        <div class="col-md-2">
            <x-globals::forms.checkbox name="labelKanbanCol-XXNEWKEYXX" id="labelKanbanCol-XXNEWKEYXX" />
        </div>
        <div class="remove">
            <a href="javascript:void(0);" onclick="leantime.projectsController.removeStatus('XXNEWKEYXX')" class="delete" aria-label="{{ __('label.remove') }}"><x-global::elements.icon name="delete" /></a>
        </div>
    </div>
</div>
</div>

<script type='text/javascript'>

    jQuery(document).ready(function() {
        jQuery("#projectdetails select").chosen();

        @if(isset($_GET['integrationSuccess']))
            window.history.pushState({},document.title, '{{ BASE_URL }}/projects/showProject/{{ (int) $project['id'] }}');
        @endif

        jQuery(".dates").datepicker(
            {
                dateFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
                dayNames: leantime.i18n.__("language.dayNames").split(","),
                dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                monthNames: leantime.i18n.__("language.monthNames").split(","),
                currentText: leantime.i18n.__("language.currentText"),
                closeText: leantime.i18n.__("language.closeText"),
                buttonText: leantime.i18n.__("language.buttonText"),
                isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
                nextText: leantime.i18n.__("language.nextText"),
                prevText: leantime.i18n.__("language.prevText"),
                weekHeader: leantime.i18n.__("language.weekHeader"),
                firstDay: leantime.i18n.__("language.firstDayOfWeek"),
            }
        );

        // Domain JS is lazy-loaded via Vite dynamic import() and may not
        // be available when jQuery.ready fires. Retry until it arrives.
        (function initDomainJs(attempts) {
            if (leantime.projectsController) {
                leantime.projectsController.initDuplicateProjectModal();
                leantime.projectsController.initTodoStatusSortable("#todoStatusList");
                leantime.projectsController.initSelectFields();
                leantime.usersController.initUserEditModal();

                if (window.leantime && window.leantime.tiptapController) {
                    leantime.tiptapController.initComplexEditor();
                }
            } else if (attempts < 40) {
                setTimeout(function() { initDomainJs(attempts + 1); }, 50);
            }
        })(0);

    });

</script>
