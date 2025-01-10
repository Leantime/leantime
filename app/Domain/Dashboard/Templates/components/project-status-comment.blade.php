@props(['comment', 'project_id', 'ticket' => null, 'formHash' => md5(CURRENT_URL), 'replyParent'])

@php
    $status = $comment->status ?? '';

@endphp

<div class="clearall">
    <div>
        <div class="commentContent statusUpdate commentStatus-{{ $status }}">
            <strong class="fancyLink">
                {{ sprintf(__('text.report_written_on'), format($comment->date)->date(), format($comment->date)->time()) }}
            </strong>
            @if ($login::userIsAtLeast($roles::$editor))
                <div class="inlineDropDownContainer float-right ml-[10px]">
                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-ellipsis-v"></i>
                    </a>

                    <ul class="dropdown-menu">
                        @if ($comment->userId == session('userdata.id'))
                            <li>
                                <a href="{{ BASE_URL }}/hx/dashboard/project-updates/get?id={{ $project_id }}&&delComment={{ $comment->id }}"
                                    class="deleteComment">
                                    <span class="fa fa-trash"></span>
                                    {{ __('links.delete') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif

            <div class="text" id="commentText-{{ $comment->id }}">
                {!! __($comment->text) !!}
            </div>
        </div>

        <div class="commentLinks">
            <small class="right">
                {!! sprintf(
                    __('text.written_on_by'),
                    format($comment->date)->date(),
                    format($comment->date)->time(),
                    $tpl->escape($comment->firstname),
                    $tpl->escape($comment->lastname),
                ) !!}
            </small>

            @if ($login::userIsAtLeast($roles::$commenter))
                <a href="javascript:void(0);"
                    onclick="commentsComponent.toggleCommentBoxes({{ $replyParent }}, '{{ $formHash }}')">
                    <span class="fa fa-reply"></span> {{ __('links.reply') }}
                </a>
            @endif
        </div>

        <div class="replies">
            @foreach ($comment->replies ?? [] as $reply)
                <x-dashboard::project-status-comment :comment="$reply" :project_id="$project_id" :ticket="$ticket ?? null"
                    :replyParent="$comment->id" />
            @endforeach

            @if ($login::userIsAtLeast($roles::$commenter))
                <x-dashboard::project-update-form :formHash="$formHash" :parentId="$comment->id" :id="$project_id"
                    :includeStatus="false" />
            @endif
        </div>
    </div>
</div>
