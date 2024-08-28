@php
    use Leantime\Core\Support\DateTimeInfoEnum;
@endphp
@props([
    "comment",
    "formHash",
    "replyParent",
    "module",
    "moduleId",
    "statusUpdates" => false
])

<div id="comment-{{ $formHash }}-{{ $comment->id }}">

    <div class="commentMain">
        <div class="commentBox">
            <div class="commentImage">
                <x-users::profile-image :user="array('id'=> $comment->userId, 'modified' => $comment->userModified)"></x-users::profile-image>
            </div>
            <div class="commentContentRight">
                <div class="commentContent {{ $statusUpdates ? "statusUpdate commentStatus-".$comment->status : "" }}">
                    @if ($login::userIsAtLeast($roles::$editor))
                        <x-global::content.context-menu>
                            @if($module == "ticket")
                                <li><a href="javascript:void(0);" onclick="leantime.ticketsController.addCommentTimesheetContent({{ $comment->id }}, {{ $moduleId }});">{!! __("links.add_to_timesheets") !!}</a></li>
                            @endif
                            @if (($comment->userId == session("userdata.id")) || $login::userIsAtLeast($roles::$manager))
                                <li>
                                    <a href="javascript:void(0);" onclick="leantime.commentsComponent.toggleCommentBoxes({{ $replyParent }}, '{{ $formHash }}', {{ $comment->id }}, true)">
                                        <span class="fa fa-edit"></span> <?php echo $tpl->__('label.edit') ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" hx-delete="{{ BASE_URL }}/comments/comment-list/delete?commentId={{ $comment->id }}&module={{ $module }}&moduleId={{ $moduleId }}" hx-target="#comments-{{ $module }}-{{ $moduleId }}" class="delete">
                                        <span class="fa fa-trash"></span> {{  __('links.delete') }}
                                    </a>
                                </li>
                            @endif
                        </x-global::content.context-menu>
                    @endif
                    <span class="name">{{ printf($tpl->__('text.full_name'), $tpl->escape($comment->firstname), $tpl->escape($comment->lastname)) }}</span>
                    <div class="text mce-content-body" id="commentText-{{ $formHash }}-{{ $comment->id }}">
                        {!! $tpl->escapeMinimal($comment->text); !!}
                    </div>
                </div>
                <div class="commentLinks">
                <span class="commentDate"><x-global::dates.date-info :date="$comment->date" :type="DateTimeInfoEnum::HumanReadable"></x-global::dates.date-info> |
                @if ($login::userIsAtLeast($roles::$commenter))
                        <a href="javascript:void(0);"
                           class="secondary"
                           onclick="leantime.commentsComponent.toggleCommentBoxes({{ $replyParent }}, '{{ $formHash }}')">
                        <span class="fa fa-reply"></span> {{ __('links.reply') }}
                    </a>
                    @endif
                </div>
            </div>
            <div class="clearall"></div>
        </div>

        <div class="replies">
            @foreach($comment->replies as $reply)
                <x-comments::single-comment :comment="$reply" :formHash="$formHash" :replyParent="$comment->id" :module="$module" :moduleId="$moduleId"/>
            @endforeach
            @if ($login::userIsAtLeast($roles::$commenter))
                <x-comments::input :formHash="$formHash" :parentId="$comment->id" :module="$module" :moduleId="$moduleId" :includeStatus="false"/>
            @endif
        </div>
    </div>
    <div class="clearall"></div>
</div>
