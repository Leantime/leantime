@extends($layout)

@section('content')

@php
    $comments = app()->make(Leantime\Domain\Comments\Repositories\Comments::class);
    $formUrl = CURRENT_URL;

    // Controller may not redirect. Make sure delComment is only added once
    if (str_contains($formUrl, '?delComment=')) {
        $urlParts = explode('?delComment=', $formUrl);
        $deleteUrlBase = $urlParts[0] . '?delComment=';
    } else {
        $deleteUrlBase = $formUrl . '?delComment=';
    }
@endphp

<h4 class="widgettitle title-light"><span
            class="fa fa-comments"></span>{!! __('subtitles.discussion') !!}
</h4>

<form method="post" accept-charset="utf-8" action="{{ $formUrl }}"
      id="commentForm">
    <a href="javascript:void(0);" onclick="toggleCommentBoxes(0)"
       style="display:none;" id="mainToggler"><span
                class="fa fa-plus-square"></span> {!! __('links.add_new_comment') !!}
    </a>

    <div id="comment0" class="commentBox">
        <textarea rows="5" cols="50" class="tiptapSimple"
                  name="text"></textarea><br/>
        <input type="submit" value="{{ __('buttons.save') }}"
               name="comment" class="btn btn-default btn-success"
               style="margin-left: 0px;"/>
        <input type="hidden" name="comment" value="1"/>
        <input type="hidden" name="father" id="father" value="0"/>
        <br/>
    </div>
    <hr/>

    <div id="comments">
        <div>
            @foreach ($__get_comments as $row)
                <div style="display:block; padding:10px; margin-top:10px; border-bottom:1px solid #f0f0f0;">
                    <img src="{{ BASE_URL }}/api/users?profileImage={{ $row['userId'] }}&v={{ format($row['userModified'])->timestamp() }}"
                         style="float:left; width:50px; margin-right:10px; padding:2px;"/>
                    <div class="right">{!! sprintf(__('text.written_on'), format($row['date'])->date(), format($row['date'])->time()) !!}</div>
                    <strong>
                    {!! sprintf(__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])) !!}
                    </strong><br/>
                    <div style="margin-left:60px;">{!! $row['text'] !!}</div>
                    <div class="clear"></div>
                    <div style="padding-left:60px" class="commentLinks">
                        <a href="javascript:void(0);" class="replyButton"
                           onclick="toggleCommentBoxes({{ $row['id'] }})">
                            <span class="fa fa-reply"></span> {!! __('links.reply') !!}
                        </a>

                        @if ($row['userId'] == session('userdata.id'))
                            <a href="{{ $deleteUrlBase . $row['id'] }}"
                               class="deleteComment">
                                <span class="fa fa-trash"></span> {!! __('links.delete') !!}
                            </a>
                        @endif
                        <span class="comment-reactions" id="reactions-{{ $row['id'] }}"
                             hx-get="{{ BASE_URL }}/hx/comments/reactions/get?commentId={{ $row['id'] }}"
                             hx-trigger="load"
                             hx-swap="outerHTML">
                        </span>
                        <div style="display:none;"
                             id="comment{{ $row['id'] }}"
                             class="commentBox">
                            <br/><input type="submit"
                                        value="{{ __('links.reply') }}"
                                        name="comment" class="btn btn-default"/>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>

                @if ($comments->getReplies($row['id']))
                    @foreach ($comments->getReplies($row['id']) as $comment)
                        <div style="display:block; padding:10px; padding-left: 60px; border-bottom:1px solid #f0f0f0;">
                            <img src="{{ BASE_URL }}/api/users?profileImage={{ $comment['userId'] }}&v={{ $comment['userModified'] }}"
                                 style="float:left; width:50px; margin-right:10px; padding:2px;"/>
                            <div>
                                <div class="right">
                                    {!! sprintf(__('text.written_on'), format($comment['date'])->date(), format($comment['date'])->time()) !!}
                                </div>
                                <strong>
                                {!! sprintf(__('text.full_name'), $tpl->escape($comment['firstname']), $tpl->escape($comment['lastname'])) !!}
                                </strong><br/>
                                <p style="margin-left:60px;">{!! nl2br($comment['text']) !!}</p>
                                <div class="clear"></div>

                                <div style="padding-left:60px" class="commentLinks">
                                    @if ($comment['userId'] == session('userdata.id'))
                                        <a href="{{ $deleteUrlBase . $comment['id'] }}"
                                           class="deleteComment">
                                            <span class="fa fa-trash"></span> {!! __('links.delete') !!}
                                        </a>
                                    @endif
                                    <span class="comment-reactions" id="reactions-{{ $comment['id'] }}"
                                         hx-get="{{ BASE_URL }}/hx/comments/reactions/get?commentId={{ $comment['id'] }}"
                                         hx-trigger="load"
                                         hx-swap="outerHTML">
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>

        @if (count($__get_comments) == 0)
            <div class="text-center">
                <div style='width:33%' class='svgContainer'>
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_real_time_collaboration_c62i.svg') !!}
                    {{ $tpl->escape($language->__('text.no_comments')) }}
                </div>
            </div>
        @endif
    </div>
</form>

@once
@push('scripts')
<script type='text/javascript'>

    // Initialize Tiptap simple editor
    if (window.leantime && window.leantime.tiptapController) {
        leantime.tiptapController.initSimpleEditor();
    }

    function toggleCommentBoxes(id) {
        @if ($login::userIsAtLeast($roles::$commenter))
            if (id == 0) {
                jQuery('#mainToggler').hide();
            } else {
                jQuery('#mainToggler').show();
            }

            // Destroy existing Tiptap editors before removing textareas
            jQuery('.commentBox').each(function() {
                var wrapper = jQuery(this).find('.tiptap-wrapper');
                if (wrapper.length && window.leantime && window.leantime.tiptapController) {
                    leantime.tiptapController.registry.destroyWithin(wrapper[0]);
                }
            });

            jQuery('.commentBox').hide('fast', function () {
                // Remove both textarea and any tiptap wrapper
                jQuery('.commentBox textarea').remove();
                jQuery('.commentBox .tiptap-wrapper').remove();

                // Create new textarea with tiptapSimple class
                jQuery('#comment' + id + '').prepend('<textarea rows="5" cols="75" name="text" class="tiptapSimple"></textarea>');

                // Initialize Tiptap editor on the new textarea
                if (window.leantime && window.leantime.tiptapController) {
                    leantime.tiptapController.initSimpleEditor();
                }
            });

            jQuery('#comment' + id + '').show('fast');
            jQuery('#father').val(id);
        @endif
    }

    // Reaction emoji picker - uses keys that map to the Reactions model
    var reactionOptions = [
        { key: 'like', emoji: '👍' },
        { key: 'love', emoji: '❤️' },
        { key: 'celebrate', emoji: '🎉' },
        { key: 'funny', emoji: '😄' },
        { key: 'interesting', emoji: '🤔' },
        { key: 'support', emoji: '💯' }
    ];
    var activeReactionPicker = null;

    function toggleReactionPicker(btn, commentId) {
        // Close any existing picker
        if (activeReactionPicker) {
            activeReactionPicker.remove();
            activeReactionPicker = null;
        }

        // Create picker element
        var picker = document.createElement('div');
        picker.className = 'reaction-emoji-picker show';
        picker.innerHTML = '<div class="reaction-emoji-picker__grid">' +
            reactionOptions.map(function(r) {
                return '<button type="button" class="reaction-emoji-picker__btn" ' +
                    'onclick="addReaction(\'' + r.key + '\', ' + commentId + ')">' +
                    r.emoji + '</button>';
            }).join('') +
        '</div>';

        // Position the picker near the button
        var btnRect = btn.getBoundingClientRect();
        picker.style.position = 'fixed';
        picker.style.left = btnRect.left + 'px';
        picker.style.top = (btnRect.bottom + 5) + 'px';

        document.body.appendChild(picker);
        activeReactionPicker = picker;

        // Close on click outside
        setTimeout(function() {
            document.addEventListener('click', closeReactionPicker);
        }, 0);
    }

    function closeReactionPicker(e) {
        if (activeReactionPicker && !activeReactionPicker.contains(e.target) && !e.target.classList.contains('add-reaction-btn')) {
            activeReactionPicker.remove();
            activeReactionPicker = null;
            document.removeEventListener('click', closeReactionPicker);
        }
    }

    function addReaction(reactionKey, commentId) {
        if (activeReactionPicker) {
            activeReactionPicker.remove();
            activeReactionPicker = null;
        }

        // Make HTMX request to toggle reaction
        htmx.ajax('POST', '{{ BASE_URL }}/hx/comments/reactions/toggle?commentId=' + commentId, {
            values: { reaction: reactionKey },
            target: '#reactions-' + commentId,
            swap: 'outerHTML'
        });
    }

</script>
@endpush
@endonce

@endsection
