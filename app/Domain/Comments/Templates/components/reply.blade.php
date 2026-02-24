<div id="#{{ $comment['id'] }}">
    <div class="commentImage">
        <x-users::profile-image :user="array('id'=> $comment['userId'], 'modified' => $comment['userModified'])" />
    </div>
    <div class="commentMain">
        <div class="commentContent">
            <div class="right commendDate">
            {!! sprintf(
                __('text.written_on'),
                format($comment['date'])->date(),
                format($comment['date'])->time()
            ) !!}
            </div>
            <span class="name">{!! sprintf(
                __('text.full_name'),
                $tpl->escape($comment['firstname']),
                $tpl->escape($comment['lastname'])
            ) !!}</span>

            <div class="text">
                {!! $tpl->escapeMinimal($comment['text']) !!}
            </div>

        </div>
        <div class="commentLinks">
            @if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$commenter))
                <a href="javascript:void(0);"
                   onclick="leantime.commentsController.toggleCommentBoxes({{ $comment['commentParent'] }})"
                   style="display:inline-flex; align-items:center; min-height:44px; padding:8px;">
                    <span class="fa fa-reply"></span> {{ __('links.reply') }}
                </a>
                @if($comment['userId'] == session("userdata.id"))
                    <a href="{{ CURRENT_URL }}?delComment={{ $comment['id'] }}"
                       class="deleteComment"
                       style="display:inline-flex; align-items:center; min-height:44px; padding:8px;">
                        <span class="fa fa-trash"></span> {{ __('links.delete') }}
                    </a>
                @endif
            @endif
        </div>
    </div>
    <div class="clearall"></div>
</div>
