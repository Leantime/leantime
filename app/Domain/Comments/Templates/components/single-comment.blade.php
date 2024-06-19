@php
    use Leantime\Core\Support\DateTimeInfoEnum;
@endphp
@props([
    "comment",
    "formHash",
    "replyParent",
])

<div class="commentImage">
    <x-users::profile-image :user="array('id'=> $comment->userId, 'modified' => $comment->userModified)"></x-users::profile-image>
</div>
<div class="commentMain">
    <div class="commentContent">
        <div class="right commentDate">
            <x-global::dates.date-info :date="$comment->date" :type="DateTimeInfoEnum::WrittenOnAt" ></x-global::dates.date-info>
            @if ($login::userIsAtLeast($roles::$editor))
                <x-global::content.context-menu>
                    @if (($comment->userId == session("userdata.id")) || $login::userIsAtLeast($roles::$manager))
                        <li>
                            <a href="{{ $comment->id }}" class="deleteComment formModal">
                                <span class="fa fa-trash"></span> {{  __('links.delete') }}
                            </a>
                        </li>
                    @endif
                </x-global::content.context-menu>
            @endif
        </div>
        <span class="name">{{ printf($tpl->__('text.full_name'), $tpl->escape($comment->firstname), $tpl->escape($comment->lastname)) }}</span>
        <div class="text mce-content-body" id="commentText-{{ $formHash }}-{{ $comment->id }}">
            <?php echo $tpl->escapeMinimal($comment->text); ?>
        </div>
    </div>
    <div class="commentLinks">
        @if ($login::userIsAtLeast($roles::$commenter))
            <a href="javascript:void(0);"
               onclick="leantime.commentsController.toggleCommentBoxes({{ $replyParent }}, '{{ $formHash }}')">
                <span class="fa fa-reply"></span> {{ __('links.reply') }}
            </a>
        @endif
    </div>
    <div class="replies">
        @foreach($comment->replies as $reply)
            <div>
                <x-comments::single-comment :comment="$reply" :formHash="$formHash" :replyParent="$replyParent"/>
            </div>
        @endforeach
        @if ($login::userIsAtLeast($roles::$commenter) && count($comment->replies) > 0)
            <div style="display:none;" id="comment-{{ $formHash }}-{{ $comment->id }}" class="commentBox-{{ $formHash }}">
                <div class="commentImage">
                    <x-users::profile-image :user="array('id'=> session('userdata.id'), 'modified' => session('userdata.modified'))" ></x-users::profile-image>
                </div>
                <div class="commentReply">
                    <x-global::forms.submit-button name="{{ __('links.reply') }}" />
                </div>
                <div class="clearall"></div>
            </div>
        @endif
    </div>
</div>
<div class="clearall"></div>
