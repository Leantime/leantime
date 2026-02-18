@php
    $project = $tpl->get('project');
    $state = $tpl->get('state');
@endphp

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-suitcase"></span></div>
    <div class="pagetitle">
        <h5>{{ __('label.administration') }}</h5>
        <h1>{{ sprintf(__('headline.project'), e($project['name'])) }}</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="inlineDropDownContainer" style="float:right; z-index:9; padding-top:2px;">
            <x-global::button link="{{ BASE_URL }}/projects/duplicateProject/{{ $project['id'] }}" type="secondary" class="duplicateProjectModal" data-tippy-content="{{ __('link.duplicate_project') }}" icon="fa-regular fa-copy">Copy</x-global::button>
            <x-global::button link="{{ BASE_URL }}/projects/delProject/{{ $project['id'] }}" type="danger" class="delete" data-tippy-content="{{ __('link.delete_project') }}" outline icon="fa fa-trash">Delete</x-global::button>
        </div>
        <div class="tabbedwidget tab-primary projectTabs">

            <ul>
                <li><a href="#projectdetails"><span class="fa fa-leaf"></span> {{ __('tabs.projectdetails') }}</a></li>
                <li><a href="#team"><span class="fa fa-group"></span> {{ __('tabs.team') }}</a></li>
                <li><a href="#integrations"> <span class="fa fa-asterisk"></span> {{ __('tabs.Integrations') }}</a></li>
                <li><a href="#todosettings"><span class="fa fa-list-ul"></span> {{ __('tabs.todosettings') }}</a></li>
                @dispatchEvent('projectTabsList')
            </ul>

            <div id="projectdetails">
                @php $tpl->displaySubmodule('projects-projectDetails') @endphp
            </div>

            <div id="team">
                <form method="post" action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#team">
                    <input type="hidden" name="saveUsers" value="1" />

                    <div>
                    <div>

                         <div class="form-group">
                             <br />{{ __('text.choose_access_for_users') }}<br />
                             <br />

                            <div>
                                <h4 class="widgettitle title-light">
                                    <span class="fa fa-users"></span>{{ __('headlines.team_member') }}
                                </h4>
                            </div>

                             <div class="tw:grid tw:md:grid-cols-3 tw:gap-6">
                                @foreach($project['assignedUsers'] as $userId => $assignedUser)
                                    <div>
                                        <div class="userBox">
                                            <x-global::forms.checkbox name="editorId[]" id="user-{{ $assignedUser['id'] }}" value="{{ $assignedUser['id'] }}"
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
                                                <x-global::forms.input name="role-{{ $assignedUser['id'] }}" readonly disabled value="{{ __('label.roles.' . $roles::getRoles()[$assignedUser['role']]) }}" />
                                            @else
                                                <x-global::forms.select name="userProjectRole-{{ $assignedUser['id'] }}">
                                                    <option value="inherit">Inherit</option>
                                                    <option value="{{ array_search($roles::$readonly, $roles::getRoles()) }}"
                                                        {{ $assignedUser['projectRole'] == array_search($roles::$readonly, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$readonly) }}</option>
                                                    <option value="{{ array_search($roles::$commenter, $roles::getRoles()) }}"
                                                        {{ $assignedUser['projectRole'] == array_search($roles::$commenter, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$commenter) }}</option>
                                                    <option value="{{ array_search($roles::$editor, $roles::getRoles()) }}"
                                                        {{ $assignedUser['projectRole'] == array_search($roles::$editor, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$editor) }}</option>
                                                    <option value="{{ array_search($roles::$manager, $roles::getRoles()) }}"
                                                        {{ $assignedUser['projectRole'] == array_search($roles::$manager, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$manager) }}</option>
                                                </x-global::forms.select>
                                            @endif
                                            <div class="clearall"></div>
                                        </div>
                                    </div>
                                @endforeach
                             </div>

                            <div>
                                <h4 class="widgettitle title-light">
                                    <span class="fa fa-user-friends "></span>{{ __('headlines.assign_users_to_project') }}
                                </h4>
                            </div>

                             <div class="tw:grid tw:md:grid-cols-3 tw:gap-6">
                                @foreach($tpl->get('availableUsers') as $row)
                                    @if(collect($project['assignedUsers'])->where('id', $row['id'])->isEmpty())
                                        <div>
                                            <div class="userBox">
                                                <x-global::forms.checkbox name="editorId[]" id="user-{{ $row['id'] }}" value="{{ $row['id'] }}" />

                                                <div class="commentImage">
                                                    <img src="{{ BASE_URL }}/api/users?profileImage={{ $row['id'] }}&v={{ format($row['modified'])->timestamp() }}"/>
                                                </div>
                                                <label for="user-{{ $row['id'] }}">{{ sprintf(__('text.full_name'), e($row['firstname']), e($row['lastname'])) }}</label>
                                                @if($roles::getRoles()[$row['role']] == $roles::$admin || $roles::getRoles()[$row['role']] == $roles::$owner)
                                                    <x-global::forms.input name="role-{{ $row['id'] }}" readonly disabled value="{{ __('label.roles.' . $roles::getRoles()[$row['role']]) }}" />
                                                @else
                                                    @php $assignedUserMatch = collect($project['assignedUsers'])->where('id', $row['id'])->first(); @endphp
                                                    <x-global::forms.select name="userProjectRole-{{ $row['id'] }}">
                                                        <option value="inherit">Inherit</option>
                                                        <option value="{{ array_search($roles::$readonly, $roles::getRoles()) }}"
                                                            {{ $assignedUserMatch && $assignedUserMatch['projectRole'] == array_search($roles::$readonly, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$readonly) }}</option>
                                                        <option value="{{ array_search($roles::$commenter, $roles::getRoles()) }}"
                                                            {{ $assignedUserMatch && $assignedUserMatch['projectRole'] == array_search($roles::$commenter, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$commenter) }}</option>
                                                        <option value="{{ array_search($roles::$editor, $roles::getRoles()) }}"
                                                            {{ $assignedUserMatch && $assignedUserMatch['projectRole'] == array_search($roles::$editor, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$editor) }}</option>
                                                        <option value="{{ array_search($roles::$manager, $roles::getRoles()) }}"
                                                            {{ $assignedUserMatch && $assignedUserMatch['projectRole'] == array_search($roles::$manager, $roles::getRoles()) ? "selected='selected'" : '' }}>{{ __('label.roles.' . $roles::$manager) }}</option>
                                                    </x-global::forms.select>
                                                @endif
                                                <div class="clearall"></div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                                @if($login::userIsAtLeast($roles::$manager))
                                    <div>
                                        <div class="userBox">
                                            <a class="userEditModal" href="{{ BASE_URL }}/users/newUser?preSelectProjectId={{ $project['id'] }}" style="font-size:var(--font-size-l); line-height:61px"><span class="fa fa-user-plus"></span> {{ __('links.create_user') }}</a>
                                            <div class="clearall"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                             <div>
                             </div>
                        </div>
                    </div>
                </div>
                    <br/>
                    <x-global::button submit type="primary" name="saveUsers" id="save">{{ __('buttons.save') }}</x-global::button>

                </form>

            </div>

            <div id="integrations">

                <h4 class="widgettitle title-light"><span class="fa fa-leaf"></span>Mattermost</h4>
                <div class="tw:grid tw:grid-cols-[3fr_5fr_4fr] tw:gap-6">
                    <div>
                        <img src="{{ BASE_URL }}/dist/images/mattermost-logoHorizontal.png" width="200" />
                    </div>
                    <div>
                        {{ __('text.mattermost_instructions') }}
                    </div>
                    <div>
                        <strong>{{ __('label.webhook_url') }}</strong><br />
                        <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#integrations" method="post">
                            <x-global::forms.input name="mattermostWebhookURL" id="mattermostWebhookURL" value="{{ e($tpl->get('mattermostWebhookURL')) }}" />
                            <br />
                            <x-global::button submit type="primary" name="mattermostSave">{{ __('buttons.save') }}</x-global::button>
                        </form>
                    </div>
                </div>
                <br />
                <h4 class="widgettitle title-light"><span class="fa fa-leaf"></span>Slack</h4>
                <div class="tw:grid tw:grid-cols-[3fr_5fr_4fr] tw:gap-6">
                    <div>
                        <img src="https://cdn.cdnlogo.com/logos/s/52/slack.svg" width="200"/>
                    </div>

                    <div>
                        {{ __('text.slack_instructions') }}
                    </div>
                    <div>
                        <strong>{{ __('label.webhook_url') }}</strong><br />
                        <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#integrations" method="post">
                            <x-global::forms.input name="slackWebhookURL" id="slackWebhookURL" value="{{ e($tpl->get('slackWebhookURL')) }}" />
                            <br />
                            <x-global::button submit type="primary" name="slackSave">{{ __('buttons.save') }}</x-global::button>
                        </form>
                    </div>
                </div>

                <h4 class="widgettitle title-light"><span class="fa fa-leaf"></span>Zulip</h4>
                <div class="tw:grid tw:grid-cols-[3fr_5fr_4fr] tw:gap-6">
                    <div>
                        <img src="{{ BASE_URL }}/dist/images/zulip-org-logo.png" width="200"/>
                    </div>

                    <div>
                        {{ __('text.zulip_instructions') }}
                    </div>
                    <div>
                        <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#integrations" method="post">
                            <strong>{{ __('label.base_url') }}</strong><br />
                            <x-global::forms.input name="zulipURL" id="zulipURL" placeholder="{{ __('input.placeholders.zulip_url') }}" value="{{ $tpl->get('zulipHook')['zulipURL'] }}" />
                            <br />
                            <strong>{{ __('label.bot_email') }}</strong><br />
                            <x-global::forms.input name="zulipEmail" id="zulipEmail" value="{{ e($tpl->get('zulipHook')['zulipEmail']) }}" />
                            <br />
                            <strong>{{ __('label.botkey') }}</strong><br />
                            <x-global::forms.input name="zulipBotKey" id="zulipBotKey" value="{{ e($tpl->get('zulipHook')['zulipBotKey']) }}" />
                            <br />
                            <strong>{{ __('label.stream') }}</strong><br />
                            <x-global::forms.input name="zulipStream" id="zulipStream" value="{{ e($tpl->get('zulipHook')['zulipStream']) }}" />
                            <br />
                            <strong>{{ __('label.topic') }}</strong><br />
                            <x-global::forms.input name="zulipTopic" id="zulipTopic" value="{{ e($tpl->get('zulipHook')['zulipTopic']) }}" />
                            <br />
                            <x-global::button submit type="primary" name="zulipSave">{{ __('buttons.save') }}</x-global::button>
                        </form>
                    </div>
                </div>

                <h4 class='widgettitle title-light'><span class='fa fa-leaf'></span>Discord</h4>
                <div class='tw:grid tw:grid-cols-[3fr_5fr_4fr] tw:gap-6'>
                    <div>
                        <img src='{{ BASE_URL }}/dist/images/discord-logo.png' width='200'/>
                    </div>

                    <div>
                      {{ __('text.discord_instructions') }}
                    </div>
                    <div>
                        <strong>{{ __('label.webhook_url') }}</strong><br/>
                        <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#integrations" method="post">
                            @for($i = 1; $i <= 3; $i++)
                            <x-global::forms.input name="discordWebhookURL{{ $i }}" id="discordWebhookURL{{ $i }}" placeholder="{{ __('input.placeholders.discord_url') }}" value="{{ e($tpl->get('discordWebhookURL' . $i)) }}" /><br/>
                            @endfor
                            <x-global::button submit type="primary" name="discordSave">{{ __('buttons.save') }}</x-global::button>
                        </form>
                    </div>
                </div>

            </div>

            <div id="todosettings">
                <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#todosettings" method="post">
                    <ul class="sortableTicketList" id="todoStatusList">
                        @foreach($tpl->get('todoStatus') as $key => $ticketStatus)
                            <li>
                                <div class="ticketBox">

                                    <div class="tw:grid tw:grid-cols-[auto_1fr_2fr_2fr_2fr_2fr_auto] tw:gap-4 tw:items-start statusList" id="todostatus-{{ $key }}">

                                        <input type="hidden" name="labelKeys[]" id="labelKey-{{ $key }}" class='labelKey' value="{{ $key }}"/>
                                        <div class="sortHandle">
                                            <br />
                                            <span class="fa fa-sort"></span>
                                        </div>
                                        <div>
                                            <label>{{ __('label.sortindex') }}</label>
                                            <input type="text" name="labelSort-{{ $key }}" id="labelSort-{{ $key }}" value="{{ e($ticketStatus['sortKey']) }}" class="sorter" style="width:50px;" />
                                        </div>
                                        <div>
                                            <label>{{ __('label.label') }}</label>
                                            <x-global::forms.input name="label-{{ $key }}" id="label-{{ $key }}" value="{{ e($ticketStatus['name']) }}" :readonly="$key == -1" />
                                        </div>
                                        <div>
                                            <label>{{ __('label.color') }}</label>
                                            <select name="labelClass-{{ $key }}" id="labelClass-{{ $key }}" class="colorChosen">
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
                                            </select>
                                        </div>
                                        <div>
                                            <label>{{ __('label.reportType') }}</label>
                                            <x-global::forms.select name="labelType-{{ $key }}" id="labelType-{{ $key }}">
                                                <option value="NEW" {{ ($ticketStatus['statusType'] == 'NEW') ? 'selected="selected"' : '' }}>{{ __('status.new') }}</option>
                                                <option value="INPROGRESS" {{ ($ticketStatus['statusType'] == 'INPROGRESS') ? 'selected="selected"' : '' }}>{{ __('status.in_progress') }}</option>
                                                <option value="DONE" {{ ($ticketStatus['statusType'] == 'DONE') ? 'selected="selected"' : '' }}>{{ __('status.done') }}</option>
                                                <option value="NONE" {{ ($ticketStatus['statusType'] == 'NONE') ? 'selected="selected"' : '' }}>{{ __('status.dont_report') }}</option>
                                            </x-global::forms.select>
                                        </div>
                                        <div>
                                            <label for="">{{ __('label.showInKanban') }}</label>
                                            <x-global::forms.checkbox name="labelKanbanCol-{{ $key }}" id="labelKanbanCol-{{ $key }}" :checked="(bool) $ticketStatus['kanbanCol']" />
                                        </div>
                                        <div class="remove">
                                            <br />
                                            @if($key != -1)
                                                <a href="javascript:void(0);" onclick="leantime.projectsController.removeStatus({{ $key }})" class="delete"><span class="fa fa-trash"></span></a>
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

                    <a href="javascript:void(0);" onclick="leantime.projectsController.addToDoStatus();" class="quickAddLink" style="text-align:left;">{{ __('links.add_status') }}</a>
                    <br />
                    <x-global::button submit type="primary" name="submitSettings">{{ __('buttons.save') }}</x-global::button>
                </form>
            </div>

            @dispatchEvent('projectTabsContent')
        </div>
    </div>
</div>


<!-- New Status Template -->
<div class="newStatusTpl" style="display:none;">
    <div class="ticketBox">
    <div class="tw:grid tw:grid-cols-[auto_1fr_2fr_2fr_2fr_2fr_auto] tw:gap-4 tw:items-start statusList" id="todostatus-XXNEWKEYXX">
        <input type="hidden" name="labelKeys[]" id="labelKey-XXNEWKEYXX" class='labelKey' value="XXNEWKEYXX"/>
        <div class="sortHandle">
            <br />
            <span class="fa fa-sort"></span>
        </div>
        <div>
            <label>{{ __('label.sortindex') }}</label>
            <input type="text" name="labelSort-XXNEWKEYXX" id="labelSort-XXNEWKEYXX" value="" class="sorter" style="width:50px;" />
        </div>
        <div>
            <label>{{ __('label.label') }}</label>
            <x-global::forms.input name="label-XXNEWKEYXX" id="label-XXNEWKEYXX" value="" />
        </div>
        <div>
            <label>{{ __('label.color') }}</label>
            <select name="labelClass-XXNEWKEYXX" id="labelClass-XXNEWKEYXX" class="colorChosen">
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
            </select>
        </div>
        <div>
            <label>{{ __('label.reportType') }}</label>
            <x-global::forms.select name="labelType-XXNEWKEYXX" id="labelType-XXNEWKEYXX">
                <option value="NEW">{{ __('status.new') }}</option>
                <option value="INPROGRESS">{{ __('status.in_progress') }}</option>
                <option value="DONE">{{ __('status.done') }}</option>
                <option value="NONE">{{ __('status.dont_report') }}</option>
            </x-global::forms.select>
        </div>
        <div>
            <label for="">{{ __('label.showInKanban') }}</label>
            <x-global::forms.checkbox name="labelKanbanCol-XXNEWKEYXX" id="labelKanbanCol-XXNEWKEYXX" />
        </div>
        <div class="remove">
            <br />
            <a href="javascript:void(0);" onclick="leantime.projectsController.removeStatus('XXNEWKEYXX')" class="delete"><span class="fa fa-trash"></span></a>
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

        leantime.projectsController.initProjectTabs();
        leantime.projectsController.initDuplicateProjectModal();
        leantime.projectsController.initTodoStatusSortable("#todoStatusList");
        leantime.projectsController.initSelectFields();
        leantime.usersController.initUserEditModal();

        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initComplexEditor();
        }

    });

</script>
