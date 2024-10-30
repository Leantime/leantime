@props([
    'comments' => [],
    'delUrlBase' => '',
    'id' => '',
])

@if (!empty($id))
    <div hx-get="{{ BASE_URL }}/hx/dashboard/projectUpdates/get?id={{ $id }}" hx-trigger="load"
        hx-swap="innerHtml">
        loading...
    </div>
@else
    <div id="project-update-card">
        <x-global::content.card variation="content">
            <x-slot:card-context-buttons>
                @if ($login::userIsAtLeast($roles::$editor))
                    <x-global::forms.button content-role="ghost" data-tippy-content="{{ __('label.copy_url_tooltip') }}"
                        onclick="leantime.dashboardController.commentsController.toggleCommentBoxes(0); jQuery('.noCommentsMessage').toggle();">
                        <i class="fa fa-plus"></i> {{ __('links.add_new_report') }}
                    </x-global::forms.button>
                @endif
            </x-slot:card-context-buttons>

            <x-slot:card-title>{{ __('subtitles.project_updates') }}</x-slot:card-title>

            <x-dashboard::project-update-form :id="$id" />

            <div id="comments">
                @foreach ($comments as $row)
                    @if ($loop->iteration == 3)
                        <a href="javascript:void(0);" onclick="jQuery('.readMore').toggle('fast')">
                            {{ __('links.read_more') }}
                        </a>
                        <div class="readMore mt-[20px]" style="display: none;">
                    @endif

                    <div class="clearall">
                        <div>
                            <div class="commentContent statusUpdate commentStatus-{{ $row['status'] }}">
                                <strong class="fancyLink">
                                    {{ sprintf(__('text.report_written_on'), format($row['date'])->date(), format($row['date'])->time()) }}
                                </strong>
                                @if ($login::userIsAtLeast($roles::$editor))
                                    <div class="inlineDropDownContainer float-right ml-[10px]">
                                        <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </a>

                                        <ul class="dropdown-menu">
                                            @if ($row['userId'] == session('userdata.id'))
                                                <li>
                                                    <a href="{{ BASE_URL }}/hx/dashboard/project-updates/get?id={{ $id }}&&delComment={{ $row['id'] }}"
                                                        class="deleteComment">
                                                        <span class="fa fa-trash"></span>
                                                        {{ __('links.delete') }}
                                                    </a>
                                                </li>
                                            @endif

                                            @isset($ticket->id)
                                                <li>
                                                    <a href="javascript:void(0);"
                                                        onclick="leantime.ticketsController.addCommentTimesheetContent({{ $row['id'] }}, {{ $ticket->id }})">
                                                        {{ __('links.add_to_timesheets') }}
                                                    </a>
                                                </li>
                                            @endisset
                                        </ul>
                                    </div>
                                @endif

                                <div class="text" id="commentText-{{ $row['id'] }}">
                                    {!! __($row['text']) !!}
                                </div>
                            </div>

                            <div class="commentLinks">
                                <small class="right">
                                    {!! sprintf(
                                        __('text.written_on_by'),
                                        format($row['date'])->date(),
                                        format($row['date'])->time(),
                                        $tpl->escape($row['firstname']),
                                        $tpl->escape($row['lastname']),
                                    ) !!}
                                </small>

                                @if ($login::userIsAtLeast($roles::$commenter))
                                    <a href="javascript:void(0);"
                                        onclick="leantime.dashboardController.commentsController.toggleCommentBoxes({{ $row['id'] }});">
                                        <span class="fa fa-reply"></span> {{ __('links.reply') }}
                                    </a>
                                @endif
                            </div>

                            <div class="replies">
                                @if ($row['replies'])
                                    @foreach ($row['replies'] as $comment)
                                        <x-comments::reply :comment="$comment" :iteration="$loop->iteration" />
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                @if (count($comments) >= 3)
            </div> <!-- Close readMore div -->
@endif
</div> <!-- Close comments div -->

@if (count($comments) == 0)
    <div style="padding-left:0px; clear:both;" class="noCommentsMessage">
        {{ __('text.no_updates') }}
    </div>
@endif
<div class="clearall"></div>
</x-global::content.card>
</div>
@endif
