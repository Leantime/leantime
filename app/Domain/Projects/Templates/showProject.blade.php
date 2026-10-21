@extends($layout)

@section('content')

@php
    $state = $state ?? null;
@endphp

<x-global::layout.page-header icon="fa fa-suitcase" :eyebrow="__('label.administration')">
    {!! sprintf(__('headline.project'), $tpl->escape($project['name'])) !!}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

        <div class="inlineDropDownContainer" style="float:right; z-index:9; padding-top:2px;">

            <x-global::forms.button tag="a" link="{{ BASE_URL }}/projects/duplicateProject/{{ $project['id'] }}" contentRole="default" class="duplicateProjectModal" data-tippy-content="{{ __('link.duplicate_project') }}"><i class="fa-regular fa-copy"></i> {{ __('buttons.clone') }}</x-global::forms.button>
            <x-global::forms.button tag="a" state="danger" variant="outline" class="delete" link="{{ BASE_URL }}/projects/delProject/{{ $project['id'] }}" data-tippy-content="{{ __('label.deleteProject') }}"><i class="fa fa-trash"></i> {{ __('buttons.delete') }}</x-global::forms.button>


        </div>
        <div class="tabbedwidget tab-primary projectTabs">

            <ul>
                <li><a href="#projectdetails"><span class="fa fa-leaf"></span> {!! __('tabs.projectdetails') !!}</a></li>
                <li><a href="#team"><span class="fa fa-group"></span> {!! __('tabs.team') !!}</a></li>
                <li><a href="#modules"><span class="fa fa-puzzle-piece"></span> {{ __('tabs.modules') }}</a></li>

                <li><a href="#integrations"> <span class="fa fa-asterisk"></span> {!! __('tabs.Integrations') !!}</a></li>
                <li><a href="#todosettings"><span class="fa fa-list-ul"></span> {!! __('tabs.todosettings') !!}</a></li>
                @dispatchEvent('projectTabsList')
            </ul>

            <div id="projectdetails">
                @include('projects::submodules.projectDetails')
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
                                    <x-global::elements.section-header icon="fa fa-users">{!! __('headlines.team_member') !!}</x-global::elements.section-header>
                                </div>
                            </div>

                             <div class="row">
                                @foreach ($project['assignedUsers'] as $userId => $assignedUser)
                                    <div class="col-md-4">
                                        <x-global::elements.card compact class="userBox">
                                            <input type='checkbox' name='editorId[]' id="user-{{ $assignedUser['id'] }}" value='{{ $assignedUser['id'] }}'
                                                checked="checked"
                                                />
                                            <div class="commentImage">
                                                <img src="{{ BASE_URL }}/api/users?profileImage={{ $assignedUser['id'] }}&v={{ format($assignedUser['modified'])->timestamp() }}"/>
                                            </div>
                                            <x-global::forms.label for="user-{{ $assignedUser['id'] }}" >{!! sprintf(__('text.full_name'), $tpl->escape($assignedUser['firstname']), $tpl->escape($assignedUser['lastname'])) !!}
                                                @if ($assignedUser['jobTitle'] != '')
                                                    <small>
                                                        {{ $assignedUser['jobTitle'] }}
                                                    </small>
                                                    <br/>
                                                @endif
                                                @if ($assignedUser['source'] == 'api')
                                                    <small>
                                                        {{ __('label.api_access') }}
                                                    </small>
                                                    <br/>
                                                @endif
                                                @if ($assignedUser['status'] == 'i')
                                                    <small>{!! __('label.invited') !!}</small>
                                                @endif
                                            </x-global::forms.label>
                                            @if ($roles::isAtLeast($assignedUser['role'], $roles::$admin))
                                                <x-global::forms.text-input readonly disabled value="{{ $roles::label($assignedUser['role']) }}" />
                                            @else
                                                <x-global::forms.select name="userProjectRole-{{ $assignedUser['id'] }}">
                                                    <option value="inherit">{{ __('label.inherit') }}</option>
                                                    @foreach ($roles::getProjectAssignableRoles() as $projectRoleKey => $projectRoleName)
                                                        <option value="{{ $projectRoleKey }}"
                                                            @if ($assignedUser['projectRole'] == $projectRoleKey) selected='selected' @endif
                                                        >{{ $roles::label($projectRoleKey) }}</option>
                                                    @endforeach
                                                </x-global::forms.select>
                                            @endif
                                            <div class="clearall"></div>
                                        </x-global::elements.card>
                                    </div>
                                @endforeach

                             </div>


                            <div class="row">
                                <div class="col-md-12">
                                    <x-global::elements.section-header icon="fa fa-user-friends">{!! __('headlines.assign_users_to_project') !!}</x-global::elements.section-header>
                                </div>
                            </div>

                             <div class="row">
                                @foreach ($availableUsers as $row)
                                    @if (collect($project['assignedUsers'])->where('id', $row['id'])->isEmpty())
                                        <div class="col-md-4">
                                            <x-global::elements.card compact class="userBox">
                                                <input type='checkbox' name='editorId[]' id="user-{{ $row['id'] }}" value='{{ $row['id'] }}' />

                                                <div class="commentImage">
                                                    <img src="{{ BASE_URL }}/api/users?profileImage={{ $row['id'] }}&v={{ format($row['modified'])->timestamp() }}"/>
                                                </div>
                                                <x-global::forms.label for="user-{{ $row['id'] }}" >{!! sprintf(__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])) !!}</x-global::forms.label>
                                                @if ($roles::isAtLeast($row['role'], $roles::$admin))
                                                    <x-global::forms.text-input readonly disabled value="{{ $roles::label($row['role']) }}" />
                                                @else
                                                    @php $assignedUserMatch = collect($project['assignedUsers'])->where('id', $row['id'])->first(); @endphp
                                                    <x-global::forms.select name="userProjectRole-{{ $row['id'] }}">
                                                        <option value="inherit">{{ __('label.inherit') }}</option>
                                                        @foreach ($roles::getProjectAssignableRoles() as $projectRoleKey => $projectRoleName)
                                                            <option value="{{ $projectRoleKey }}"
                                                                @if ($assignedUserMatch && $assignedUserMatch['projectRole'] == $projectRoleKey) selected='selected' @endif
                                                            >{{ $roles::label($projectRoleKey) }}</option>
                                                        @endforeach
                                                    </x-global::forms.select>
                                                @endif
                                                <div class="clearall"></div>
                                            </x-global::elements.card>
                                        </div>




                                    @endif
                                @endforeach
                                @if ($login::userIsAtLeast($roles::$manager))
                                    <div class="col-md-4">

                                        <x-global::elements.card compact class="userBox">
                                            <a class="userEditModal" href="{{ BASE_URL }}/users/newUser?preSelectProjectId={{ $project['id'] }}" style="font-size:var(--font-size-l); line-height:61px"><span class="fa fa-user-plus"></span> {!! __('links.create_user') !!}</a>
                                            <div class="clearall"></div>
                                        </x-global::elements.card>
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
                    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="saveUsers" id="save" />

                </form>

            </div>

            <div id="modules">
                <x-global::hx endpoint="modulemanager/projectModules/get/{{ $project['id'] }}" trigger="load" loaderCount="4" />
            </div>

            <div id="integrations">

                @if ($projectMuteCount > 0)
                    <x-global::feedback.alert icon="fa fa-bell-slash">{!! sprintf(__('label.project_mute_count'), $projectMuteCount) !!}</x-global::feedback.alert>
                @endif

                <x-global::elements.section-header icon="fa fa-leaf">Mattermost</x-global::elements.section-header>
                <div class="row">
                    <div class="col-md-3">
                        <img src="{{ BASE_URL }}/dist/images/mattermost-logoHorizontal.png" width="200" />
                    </div>
                    <div class="col-md-5">
                        {!! __('text.mattermost_instructions') !!}
                    </div>
                    <div class="col-md-4">
                        <strong>{!! __('label.webhook_url') !!}</strong><br />
                        <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#integrations" method="post">
                            <x-global::forms.text-input name="mattermostWebhookURL" id="mattermostWebhookURL" value="{{ e($mattermostWebhookURL) }}" />
                            <br />
                            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="mattermostSave" />
                        </form>
                    </div>
                </div>
                <br />
                <x-global::elements.section-header icon="fa fa-leaf">Slack</x-global::elements.section-header>
                <div class="row">
                    <div class="col-md-3">
                        <img src="https://cdn.cdnlogo.com/logos/s/52/slack.svg" width="200"/>
                    </div>

                    <div class="col-md-5">
                        {!! __('text.slack_instructions') !!}
                    </div>
                    <div class="col-md-4">
                        <strong>{!! __('label.webhook_url') !!}</strong><br />
                        <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#integrations" method="post">
                            <x-global::forms.text-input name="slackWebhookURL" id="slackWebhookURL" value="{{ e($slackWebhookURL) }}" />
                            <br />
                            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="slackSave" />
                        </form>
                    </div>
                </div>

                <x-global::elements.section-header icon="fa fa-leaf">Zulip</x-global::elements.section-header>
                <div class="row">
                    <div class="col-md-3">
                        <img src="{{ BASE_URL }}/dist/images/zulip-org-logo.png" width="200"/>
                    </div>

                    <div class="col-md-5">
                        {!! __('text.zulip_instructions') !!}
                    </div>
                    <div class="col-md-4">

                        <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#integrations" method="post">
                            <strong>{!! __('label.base_url') !!}</strong><br />
                            <x-global::forms.text-input name="zulipURL" id="zulipURL" placeholder="{{ __('input.placeholders.zulip_url') }}" value="{{ $zulipHook['zulipURL'] }}" />
                            <br />
                            <strong>{!! __('label.bot_email') !!}</strong><br />
                            <x-global::forms.text-input name="zulipEmail" id="zulipEmail" placeholder="" value="{{ $tpl->escape($zulipHook['zulipEmail']) }}" />
                            <br />
                            <strong>{!! __('label.botkey') !!}</strong><br />
                            <x-global::forms.text-input name="zulipBotKey" id="zulipBotKey" placeholder="" value="{{ $tpl->escape($zulipHook['zulipBotKey']) }}" />
                            <br />
                            <strong>{!! __('label.stream') !!}</strong><br />
                            <x-global::forms.text-input name="zulipStream" id="zulipStream" placeholder="" value="{{ $tpl->escape($zulipHook['zulipStream']) }}" />
                            <br />
                            <strong>{!! __('label.topic') !!}</strong><br />
                            <x-global::forms.text-input name="zulipTopic" id="zulipTopic" placeholder="" value="{{ $tpl->escape($zulipHook['zulipTopic']) }}" />
                            <br />
                            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="zulipSave" />
                        </form>
                    </div>
                </div>

                <x-global::elements.section-header icon="fa fa-leaf">Telegram</x-global::elements.section-header>
                <div class="row">
                    <div class="col-md-3">
                        <img src="{{ BASE_URL }}/dist/images/telegram-logo.svg" width="130" alt="Telegram" />
                    </div>

                    <div class="col-md-5">
                        {!! __('text.telegram_instructions') !!}
                    </div>
                    <div class="col-md-4">
                        <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#integrations" method="post">
                            <strong>{!! __('label.botkey') !!}</strong><br />
                            <x-global::forms.text-input name="telegramBotToken" id="telegramBotToken" placeholder="" value="{{ $tpl->escape($telegramHook['telegramBotToken']) }}" />
                            <br />
                            <strong>{!! __('label.telegram_chat_id') !!}</strong><br />
                            <x-global::forms.text-input name="telegramChatId" id="telegramChatId" placeholder="{{ __('input.placeholders.telegram_chat_id') }}" value="{{ $tpl->escape($telegramHook['telegramChatId']) }}" />
                            <br />
                            <strong>{!! __('label.telegram_topic_id') !!}</strong><br />
                            <x-global::forms.text-input name="telegramTopicId" id="telegramTopicId" placeholder="{{ __('input.placeholders.telegram_topic_id') }}" value="{{ $tpl->escape($telegramHook['telegramTopicId']) }}" />
                            <br />
                            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="telegramSave" />
                        </form>
                    </div>
                </div>

                {{-- Slack webhook --}}
                <x-global::elements.section-header icon="fa fa-leaf">Discord</x-global::elements.section-header>
                <div class='row'>
                    <div class='col-md-3'>
                        <img src='{{ BASE_URL }}/dist/images/discord-logo.png' width='200'/>
                    </div>

                    <div class='col-md-5'>
                      {!! __('text.discord_instructions') !!}
                    </div>
                    <div class="col-md-4">
                        <strong>{!! __('label.webhook_url') !!}</strong><br/>
                        <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#integrations" method="post">
                            @for ($i = 1; $i <= 3; $i++)
                            @php $discordVarName = 'discordWebhookURL'.$i; $discordVarVal = $$discordVarName ?? ''; @endphp
                            <input type="text" name="discordWebhookURL{{ $i }}" id="discordWebhookURL{{ $i }}" placeholder="{{ __('input.placeholders.discord_url') }}" value="{{ e($discordVarVal) }}"/><br/>
                            @endfor
                            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="discordSave" />
                        </form>
                    </div>
                </div>

            </div>

            <div id="todosettings">
                <form action="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}#todosettings" method="post">
                    <ul class="sortableTicketList" id="todoStatusList">
                        @foreach ($todoStatus as $key => $ticketStatus)
                            <li>
                                <x-global::elements.card compact class="ticketBox">

                                    <div class="row statusList" id="todostatus-{{ $key }}">

                                        <input type="hidden" name="labelKeys[]" id="labelKey-{{ $key }}" class='labelKey' value="{{ $key }}"/>
                                        <div class="sortHandle">
                                            <br />
                                            <span class="fa fa-sort"></span>
                                        </div>
                                        <div class="col-md-1">
                                            <x-global::forms.label>{!! __('label.sortindex') !!}</x-global::forms.label>
                                            <input type="text" name="labelSort-{{ $key }}" class="sorter" id="labelSort-{{ $key }}" value="{{ $ticketStatus['sortKey'] }}" style="width:50px;"/>
                                        </div>
                                        <div class="col-md-2">

                                            <x-global::forms.label>{!! __('label.label') !!}</x-global::forms.label>
                                            <input type="text" name="label-{{ $key }}" {{ $key == -1 ? 'readonly' : '' }} id="label-{{ $key }}" value="{{ $ticketStatus['name'] }}" />

                                        </div>
                                        <div class="col-md-2">
                                            <x-global::forms.label>{!! __('label.color') !!}</x-global::forms.label>
                                            <x-global::forms.select name="labelClass-{{ $key }}" id="labelClass-{{ $key }}" class="colorChosen" enhanced :search="false">
                                                <x-global::forms.select.option value="label-purple" color-class="label-purple" :selected="$ticketStatus['class'] == 'label-purple'">{!! __('label.purple') !!}</x-global::forms.select.option>
                                                <x-global::forms.select.option value="label-pink" color-class="label-pink" :selected="$ticketStatus['class'] == 'label-pink'">{!! __('label.pink') !!}</x-global::forms.select.option>
                                                <x-global::forms.select.option value="label-darker-blue" color-class="label-darker-blue" :selected="$ticketStatus['class'] == 'label-darker-blue'">{!! __('label.darker-blue') !!}</x-global::forms.select.option>
                                                <x-global::forms.select.option value="label-info" color-class="label-info" :selected="$ticketStatus['class'] == 'label-info'">{!! __('label.dark-blue') !!}</x-global::forms.select.option>
                                                <x-global::forms.select.option value="label-blue" color-class="label-blue" :selected="$ticketStatus['class'] == 'label-blue'">{!! __('label.blue') !!}</x-global::forms.select.option>
                                                <x-global::forms.select.option value="label-dark-green" color-class="label-dark-green" :selected="$ticketStatus['class'] == 'label-dark-green'">{!! __('label.dark-green') !!}</x-global::forms.select.option>
                                                <x-global::forms.select.option value="label-success" color-class="label-success" :selected="$ticketStatus['class'] == 'label-success'">{!! __('label.green') !!}</x-global::forms.select.option>
                                                <x-global::forms.select.option value="label-warning" color-class="label-warning" :selected="$ticketStatus['class'] == 'label-warning'">{!! __('label.yellow') !!}</x-global::forms.select.option>
                                                <x-global::forms.select.option value="label-brown" color-class="label-brown" :selected="$ticketStatus['class'] == 'label-brown'">{!! __('label.brown') !!}</x-global::forms.select.option>
                                                <x-global::forms.select.option value="label-danger" color-class="label-danger" :selected="$ticketStatus['class'] == 'label-danger'">{!! __('label.dark-red') !!}</x-global::forms.select.option>
                                                <x-global::forms.select.option value="label-important" color-class="label-important" :selected="$ticketStatus['class'] == 'label-important'">{!! __('label.red') !!}</x-global::forms.select.option>
                                                <x-global::forms.select.option value="label-default" color-class="label-default" :selected="$ticketStatus['class'] == 'label-default'">{!! __('label.grey') !!}</x-global::forms.select.option>



                                            </x-global::forms.select>
                                        </div>
                                        <div class="col-md-2">
                                            <x-global::forms.label>{!! __('label.reportType') !!}</x-global::forms.label>
                                            <x-global::forms.select name="labelType-{{ $key }}" id="labelType-{{ $key }}">
                                                <option value="NEW" {{ ($ticketStatus['statusType'] == 'NEW') ? 'selected="selected"' : '' }}>{!! __('status.new') !!}</option>
                                                <option value="INPROGRESS" {{ ($ticketStatus['statusType'] == 'INPROGRESS') ? 'selected="selected"' : '' }}>{!! __('status.in_progress') !!}</option>
                                                <option value="DONE" {{ ($ticketStatus['statusType'] == 'DONE') ? 'selected="selected"' : '' }}>{!! __('status.done') !!}</option>
                                                <option value="NONE" {{ ($ticketStatus['statusType'] == 'NONE') ? 'selected="selected"' : '' }}>{!! __('status.dont_report') !!}</option>
                                            </x-global::forms.select>
                                        </div>
                                        <div class="col-md-2">
                                            <x-global::forms.label for="">{!! __('label.showInKanban') !!}</x-global::forms.label>
                                            <input type="checkbox" name="labelKanbanCol-{{ $key }}" id="labelKanbanCol-{{ $key }}" {{ $ticketStatus['kanbanCol'] ? 'checked="checked"' : '' }}/>
                                        </div>
                                        <div class="remove">
                                            <br />
                                            @if ($key != -1)
                                                <a href="javascript:void(0);" onclick="leantime.projectsController.removeStatus({{ $key }})" class="delete"><span class="fa fa-trash"></span></a>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($key == -1)
                                        <em>{{ __('text.archive_status_protected') }}</em>
                                    @endif
                                </x-global::elements.card>
                            </li>

                        @endforeach
                    </ul>

                    <a href="javascript:void(0);" onclick="leantime.projectsController.addToDoStatus();" class="quickAddLink" style="text-align:left;">{!! __('links.add_status') !!}</a>
                    <br />
                    <x-global::forms.button tag="input" inputType="submit" :labelText="__('buttons.save')" name="submitSettings" contentRole="primary"/>
                </form>
            </div>

            @dispatchEvent('projectTabsContent')
        </div>
    </x-global::layout.content-box>
</div>


<!-- New Status Template -->
<template class="newStatusTpl">
    <x-global::elements.card compact class="ticketBox">
    <div class="row statusList" id="todostatus-XXNEWKEYXX">
        <input type="hidden" name="labelKeys[]" id="labelKey-XXNEWKEYXX" class='labelKey' value="XXNEWKEYXX"/>
        <div class="sortHandle">
            <br />
            <span class="fa fa-sort"></span>
        </div>
        <div class="col-md-1">
            <x-global::forms.label>{!! __('label.sortindex') !!}</x-global::forms.label>
            <input type="text" name="labelSort-XXNEWKEYXX" class="sorter" id="labelSort-XXNEWKEYXX" value="" style="width:50px;"/>
        </div>
        <div class="col-md-2">
            <x-global::forms.label>{!! __('label.label') !!}</x-global::forms.label>
            <input type="text" name="label-XXNEWKEYXX" id="label-XXNEWKEYXX" value="" />

        </div>
        <div class="col-md-2">
            <x-global::forms.label>{!! __('label.color') !!}</x-global::forms.label>
            <x-global::forms.select name="labelClass-XXNEWKEYXX" id="labelClass-XXNEWKEYXX" class="colorChosen" enhanced :search="false">
                <x-global::forms.select.option value="label-blue" color-class="label-blue">{!! __('label.blue') !!}</x-global::forms.select.option>
                <x-global::forms.select.option value="label-info" color-class="label-info">{!! __('label.dark-blue') !!}</x-global::forms.select.option>
                <x-global::forms.select.option value="label-darker-blue" color-class="label-darker-blue">{!! __('label.darker-blue') !!}</x-global::forms.select.option>
                <x-global::forms.select.option value="label-warning" color-class="label-warning">{!! __('label.yellow') !!}</x-global::forms.select.option>
                <x-global::forms.select.option value="label-success" color-class="label-success">{!! __('label.green') !!}</x-global::forms.select.option>
                <x-global::forms.select.option value="label-dark-green" color-class="label-dark-green">{!! __('label.dark-green') !!}</x-global::forms.select.option>
                <x-global::forms.select.option value="label-important" color-class="label-important">{!! __('label.red') !!}</x-global::forms.select.option>
                <x-global::forms.select.option value="label-danger" color-class="label-danger">{!! __('label.dark-red') !!}</x-global::forms.select.option>
                <x-global::forms.select.option value="label-pink" color-class="label-pink">{!! __('label.pink') !!}</x-global::forms.select.option>
                <x-global::forms.select.option value="label-purple" color-class="label-purple">{!! __('label.purple') !!}</x-global::forms.select.option>
                <x-global::forms.select.option value="label-brown" color-class="label-brown">{!! __('label.brown') !!}</x-global::forms.select.option>
                <x-global::forms.select.option value="label-default" color-class="label-default">{!! __('label.grey') !!}</x-global::forms.select.option>
            </x-global::forms.select>
        </div>
        <div class="col-md-2">
            <x-global::forms.label>{!! __('label.reportType') !!}</x-global::forms.label>
            <x-global::forms.select name="labelType-XXNEWKEYXX" id="labelType-XXNEWKEYXX">
                <option value="NEW">{!! __('status.new') !!}</option>
                <option value="INPROGRESS">{!! __('status.in_progress') !!}</option>
                <option value="DONE">{!! __('status.done') !!}</option>
                <option value="NONE">{!! __('status.dont_report') !!}</option>
            </x-global::forms.select>
        </div>
        <div class="col-md-2">
            <x-global::forms.label for="">{!! __('label.showInKanban') !!}</x-global::forms.label>
            <input type="checkbox" name="labelKanbanCol-XXNEWKEYXX" id="labelKanbanCol-XXNEWKEYXX"/>
        </div>
        <div class="remove">
            <br />
            <a href="javascript:void(0);" onclick="leantime.projectsController.removeStatus('XXNEWKEYXX')" class="delete"><span class="fa fa-trash"></span></a>
        </div>
    </div>
</x-global::elements.card>
</template>

@once @push('scripts')
<script type='text/javascript'>

    jQuery(document).ready(function() {

        @if (isset($_GET['integrationSuccess']))
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
        leantime.usersController.initUserEditModal();

        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initComplexEditor();
        }

    });

</script>
@endpush @endonce

@endsection
