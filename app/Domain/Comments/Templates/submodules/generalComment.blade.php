@php
    $comments = app()->make(Leantime\Domain\Comments\Repositories\Comments::class);
    $formUrl = CURRENT_URL;
    $formHash = md5($formUrl);

    // Controller may not redirect. Make sure delComment is only added once
    if (str_contains($formUrl, '?delComment=')) {
        $urlParts = explode('?delComment=', $formUrl);
        $deleteUrlBase = $urlParts[0] . '?delComment=';
    } else {
        $deleteUrlBase = $formUrl . '?delComment=';
    }
@endphp

<form method="post" accept-charset="utf-8" action="{{ $formUrl }}" id="commentForm-{{ $formHash }}" class="formModal">

    @if ($login::userIsAtLeast($roles::$commenter))
        <div class="mainToggler-{{ $formHash }}" id="">
            <div class="commentImage">
                <img src="{{ BASE_URL }}/api/users?profileImage={{ session('userdata.id') }}&v={{ format(session('userdata.modified'))->timestamp() }}" />
            </div>
            <div class="commentReply inactive">
                <a href="javascript:void(0);" onclick="toggleCommentBoxes(0, null, '{{ $formHash }}')">
                    {!! __('links.add_new_comment') !!}
                </a>
            </div>
        </div>

        <div id="comment-{{ $formHash }}-0" class="commentBox-{{ $formHash }} commenterFields" style="display:none;">
            <div class="commentImage">
                <img src="{{ BASE_URL }}/api/users?profileImage={{ session('userdata.id') }}&v={{ format(session('userdata.modified'))->timestamp() }}" />
            </div>
            <div class="commentReply">
                <textarea rows="5" cols="50" class="tiptapSimple" name="text"></textarea>
                <input type="submit" value="{{ __('buttons.save') }}" name="comment" class="btn btn-primary btn-success" style="margin-left: 0px;"/>
            </div>
            <input type="hidden" name="comment" class="commenterField" value="1"/>
            <input type="hidden" name="father" class="commenterField" id="father-{{ $formHash }}" value="0"/>
            <input type="hidden" name="edit-comment-helper" class="commenterField" id="edit-comment-helper-{{ $formHash }}" />
            <br/>
        </div>
    @endif

    <div id="comments-{{ $formHash }}">
        <div>
            @foreach ($comments as $row)
                <div class="clearall">
                    <div class="commentImage" id="comment-image-to-hide-on-edit-{{ $formHash }}-{{ $row['id'] }}">
                        <img src="{{ BASE_URL }}/api/users?profileImage={{ $row['userId'] }}&v={{ format($row['userModified'])->timestamp() }}"/>
                    </div>
                    <div class="commentMain">
                        <div class="commentContent" id="comment-to-hide-on-edit-{{ $formHash }}-{{ $row['id'] }}">
                            <div class="right commentDate">
                                {!! sprintf(__('text.written_on'), format($row['date'])->date(), format($row['date'])->time()) !!}
                                    @if ($login::userIsAtLeast($roles::$editor))
                                        <div class="inlineDropDownContainer" style="float:right; margin-left:10px;">
                                            <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            </a>

                                            <ul class="dropdown-menu">
                                                @if (($row['userId'] == session('userdata.id')) || $login::userIsAtLeast($roles::$manager))
                                                    <li><a href="{{ $deleteUrlBase . $row['id'] }}" class="deleteComment formModal">
                                                        <span class="fa fa-trash"></span> {!! __('links.delete') !!}
                                                    </a></li>
                                                @endif
                                                @if (($row['userId'] == session('userdata.id')) || $login::userIsAtLeast($roles::$manager))
                                                    <li>
                                                        <a href="javascript:void(0);" onclick="toggleCommentBoxes({{ $row['id'] }}, null, '{{ $formHash }}', true)">
                                                            <span class="fa fa-edit"></span> {!! __('label.edit') !!}
                                                        </a>
                                                    </li>
                                                @endif
                                                @if (isset($ticket->id))
                                                        <li><a href="javascript:void(0);" onclick="leantime.ticketsController.addCommentTimesheetContent({{ $row['id'] }}, {{ $ticket->id }});">{!! __('links.add_to_timesheets') !!}</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    @endif
                            </div>
                            <span class="name">{!! sprintf(__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])) !!}</span>
                            <div class="text tiptap-content" id="commentText-{{ $formHash }}-{{ $row['id'] }}">
                                <div id="comment-text-to-hide-{{ $formHash }}-{{ $row['id'] }}">{!! $tpl->escapeMinimal($row['text']) !!}</div>
                            </div>
                        </div>
                        <div class="commentLinks" id="comment-link-to-hide-on-edit-{{ $formHash }}-{{ $row['id'] }}">
                            @if ($login::userIsAtLeast($roles::$commenter))
                                <a href="javascript:void(0);"
                                   onclick="toggleCommentBoxes({{ $row['id'] }}, null, '{{ $formHash }}')">
                                    <span class="fa fa-reply"></span> {!! __('links.reply') !!}
                                </a>
                            @endif
                            <span class="comment-reactions" id="reactions-{{ $row['id'] }}"
                                 hx-get="{{ BASE_URL }}/hx/comments/reactions/get?commentId={{ $row['id'] }}"
                                 hx-trigger="load"
                                 hx-swap="outerHTML">
                            </span>
                        </div>

                        <div class="replies">
                            @if ($comments->getReplies($row['id']))
                                @foreach ($comments->getReplies($row['id']) as $comment)
                                    <div>
                                        <div class="commentImage">
                                            <img src="{{ BASE_URL }}/api/users?profileImage={{ $comment['userId'] }}&v={{ format($comment['userModified'])->timestamp() }}"/>
                                        </div>
                                        <div class="commentMain">
                                            <div class="commentContent">
                                                <div class="right commentDate">
                                                    {!! sprintf(__('text.written_on'), format($comment['date'])->date(), format($comment['date'])->time()) !!}
                                                </div>
                                                <span class="name">{!! sprintf(__('text.full_name'), $tpl->escape($comment['firstname']), $tpl->escape($comment['lastname'])) !!}</span>
                                                <div class="text tiptap-content" id="comment-text-to-hide-reply-{{ $formHash }}-{{ $comment['id'] }}">{!! $tpl->escapeMinimal($comment['text']) !!}</div>
                                            </div>

                                            <div class="commentLinks">
                                                @if ($login::userIsAtLeast($roles::$commenter))
                                                    <a href="javascript:void(0);"
                                                       onclick="toggleCommentBoxes({{ $row['id'] }}, null, '{{ $formHash }}')">
                                                        <span class="fa fa-reply"></span> {!! __('links.reply') !!}
                                                    </a>
                                                    @if ($comment['userId'] == session('userdata.id'))
                                                        <a href="{{ $deleteUrlBase . $comment['id'] }}"
                                                           class="deleteComment formModal">
                                                            <span class="fa fa-trash"></span> {!! __('links.delete') !!}
                                                        </a>
                                                        <a href="javascript:void(0);" onclick="toggleCommentBoxes({{ $row['id'] }}, {{ $comment['id'] }}, '{{ $formHash }}', true, true)">
                                                            <span class="fa fa-edit"></span> {!! __('label.edit') !!}
                                                        </a>
                                                    @endif
                                                @endif
                                                <span class="comment-reactions" id="reactions-{{ $comment['id'] }}"
                                                     hx-get="{{ BASE_URL }}/hx/comments/reactions/get?commentId={{ $comment['id'] }}"
                                                     hx-trigger="load"
                                                     hx-swap="outerHTML">
                                                </span>
                                            </div>
                                        </div>
                                        <div class="clearall"></div>
                                    </div>
                                @endforeach
                            @endif
                            <div style="display:none;" id="comment-{{ $formHash }}-{{ $row['id'] }}" class="commentBox">
                                <div class="commentImage">
                                    <img src="{{ BASE_URL }}/api/users?profileImage={{ session('userdata.id') }}&v={{ format(session('userdata.modified'))->timestamp() }}"/>
                                </div>
                                <div class="commentReply">
                                    <input type="submit" value="{{ __('links.reply') }}" name="comment" id="submit-reply-button" class="btn btn-primary"/>
                                    <input type="button" onclick="cancel({{ $row['id'] }}, '{{ $formHash }}')" value="{{ __('links.cancel') }}" class="btn btn-primary"/>
                                </div>
                                <div class="clearall"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="clearall"></div>
</form>

<script type='text/javascript'>

    jQuery(document).ready(function() {
        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initSimpleEditor();
        }
    });

    function toggleCommentBoxes(id, commentId, formHash, editComment = false, isReply = false) {
        @if ($login::userIsAtLeast($roles::$commenter))

            if (parseInt(id, 10) === 0) {
                jQuery(`.mainToggler-${formHash}`).hide();
            } else {
                jQuery(`.mainToggler-${formHash}`).show();
            }
            if (editComment) {
                jQuery(`#comment-to-hide-on-edit-${formHash}-${id}`).hide();
                jQuery(`#comment-link-to-hide-on-edit-${formHash}-${id}`).hide();
                jQuery(`#comment-image-to-hide-on-edit-${formHash}-${id}`).hide();
                jQuery(`#edit-comment-helper-${formHash}`).val(commentId || id);
                jQuery('#submit-reply-button').val('{{ __('buttons.save') }}');
            }

            // Destroy existing Tiptap editors before removing textareas
            jQuery(`.commentBox-${formHash}`).each(function() {
                var wrapper = jQuery(this).find('.tiptap-wrapper');
                if (wrapper.length && window.leantime && window.leantime.tiptapController) {
                    leantime.tiptapController.registry.destroyWithin(wrapper[0]);
                }
            });

            jQuery(`.commentBox-${formHash} textarea`).remove();
            jQuery(`.commentBox-${formHash} .tiptap-wrapper`).remove();
            jQuery(`.commentBox-${formHash}`).hide();

            // Create textarea with tiptapSimple class
            var initialContent = editComment ? jQuery(`#comment-text-to-hide-${isReply ? 'reply-' : ''}${formHash}-${commentId || id}`).html() : '';
            jQuery(`#comment-${formHash}-${id} .commentReply`).prepend(`<textarea rows="5" cols="75" name="text" id="editor_${formHash}-${id}" class="tiptapSimple">${initialContent}</textarea>`);

            // Initialize Tiptap editor
            if (window.leantime && window.leantime.tiptapController) {
                leantime.tiptapController.initSimpleEditor();
                // Focus the editor after a short delay to allow initialization
                setTimeout(function() {
                    var editorEl = document.querySelector(`#comment-${formHash}-${id} .tiptap-editor`);
                    if (editorEl) {
                        var editor = leantime.tiptapController.registry.get(editorEl);
                        if (editor) {
                            editor.commands.focus('end');
                        }
                    }
                }, 100);
            }

            jQuery(`#comment-${formHash}-${id}`).show();
            jQuery(`#father-${formHash}`).val(id);

        @endif
    }
    function cancel(id, formHash) {
        @if ($login::userIsAtLeast($roles::$commenter))
            jQuery(`#comment-to-hide-on-edit-${formHash}-${id}`).show();
            jQuery(`.commentBox-${formHash} textarea`).remove();
            jQuery(`#comment-link-to-hide-on-edit-${formHash}-${id}`).show();
            jQuery(`#comment-image-to-hide-on-edit-${formHash}-${id}`).show();
            jQuery(`#comment-${formHash}-${id}`).hide();
        @endif
    }

    jQuery(".confetti").click(function(){
        confetti({
            spread: 70,
            origin: { y: 1.2 },
        });
    });

    function respondToVisibility(element, callback) {
        var options = {
            root: document.documentElement,
        };

        var observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                callback(entry.intersectionRatio > 0);
            });
        }, options);

        observer.observe(element);
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
