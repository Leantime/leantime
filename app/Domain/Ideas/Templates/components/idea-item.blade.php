@props([
    'row',
    'key',
    // 'roles',
    // 'login',
    'users' => [],
    'id' => '',
])


@if (empty($id) == false)

    <div hx-get="{{ BASE_URL }}/hx/ideas/ideaItem?id={{ $id }}&key={{ $key }}" hx-trigger="load"
        hx-swap="innerHtml">
        loading..
    </div>
@else
    @if ($row->box == $key)
        <div class="ticketBox moveable" id="item_{{ $row->id }}">
            <div class="row">
                <div class="col-md-12">
                    @if ($login::userIsAtLeast($roles::$editor))
                        <div style="float:right;">
                            <x-global::content.context-menu>
                                <x-global::actions.dropdown.item>
                                    {!! __('subtitles.edit') !!}
                                </x-global::actions.dropdown.item>
                                <x-global::actions.dropdown.item href="#/ideas/ideaDialog/{{ $row->id }}"
                                    data="item_{{ $row->id }}">
                                    {!! __('links.edit_canvas_item') !!}
                                </x-global::actions.dropdown.item>
                                <x-global::actions.dropdown.item href="#/ideas/delCanvasItem/{{ $row->id }}"
                                    class="delete" data="item_{{ $row->id }}">
                                    {!! __('links.delete_canvas_item') !!}
                                </x-global::actions.dropdown.item>
                            </x-global::content.context-menu>
                        </div>
                    @endif
                    <h4><a href="{{ BASE_URL }}/ideas/advancedBoards/#/ideas/ideaDialog/{{ $row->id }}"
                            class="" data="item_{{ $row->id }}">
                            {{ $row->description }}
                        </a></h4>
                    <div class="mainIdeaContent">
                        <div class="kanbanCardContent">
                            <div class="kanbanContent" style="margin-bottom: 20px">
                                {!! $row->data !!}
                            </div>
                        </div>
                    </div>
                    <div class="clearfix" style="padding-bottom: 8px;"></div>
                    <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                        @php
                            $userImageUrl =
                                $row->authorFirstname != ''
                                    ? BASE_URL . '/api/users?profileImage=' . $row->author
                                    : BASE_URL . '/api/users?profileImage=false';
                        @endphp

                        <x-global::actions.dropdown class="f-left" align="start" contentRole="ghost">
                            <x-slot:labelText>
                                <span>
                                    <span id="userImage{{ $row->id }}">
                                        <img src="{{ $userImageUrl }}" width="25"
                                            style="vertical-align: middle;" />
                                    </span>
                                    <span id="user{{ $row->id }}"></span>
                                </span>
                            </x-slot:labelText>

                            <x-slot:menu>
                                <x-global::actions.dropdown.item variant="header-border">
                                    {!! __('dropdown.choose_user') !!}
                                </x-global::actions.dropdown.item>

                                @foreach ($users as $user)
                                    <x-global::actions.dropdown.item href="javascript:void(0);" :data-label="sprintf(__('text.full_name'), $user['firstname'], $user['lastname'])"
                                        :data-value="$row->id . '_' . $user['id'] . '_' . $user['profileId']" id="userStatusChange{{ $row->id . $user['id'] }}">
                                        <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}"
                                            width="25" style="vertical-align: middle; margin-right:5px;" />
                                        {{ sprintf(__('text.full_name'), $user['firstname'], $user['lastname']) }}
                                    </x-global::actions.dropdown.item>
                                @endforeach
                            </x-slot:menu>
                        </x-global::actions.dropdown>
                    </div>
                    <div class="pull-right" style="margin-right:10px;">
                        <a href="#/ideas/ideaDialog/{{ $row->id }}" data="item_{{ $row->id }}"
                            {{ $row->commentCount == 0 ? 'style="color: grey;"' : '' }}>
                            <span class="fas fa-comments"></span>
                        </a>
                        <small>{{ $row->commentCount }}</small>
                    </div>
                </div>
            </div>
            @if ($row->milestoneHeadline != '')
                <br />
                <div hx-trigger="load" hx-indicator=".htmx-indicator"
                    hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $row->milestoneId }}">
                    <div class="htmx-indicator">
                        {!! __('label.loading_milestone') !!}
                    </div>
                </div>
            @endif
        </div>
    @endif
@endif
