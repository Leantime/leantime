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

    @if($login::userIsAtLeast($roles::$commenter))
        <div class="mainToggler-{{ $formHash }}" id="">
            <div class="commentImage">
                <img src="{{ BASE_URL }}/api/users?profileImage={{ session('userdata.id') }}&v={{ format(session('userdata.modified'))->timestamp() }}" />
            </div>
            <div class="commentReply inactive">
                <a href="javascript:void(0);" onclick="toggleCommentBoxes(0, null, '{{ $formHash }}')">
                    {{ __('links.add_new_comment') }}
                </a>
            </div>
        </div>

        <div id="comment-{{ $formHash }}-0" class="commentBox-{{ $formHash }} commenterFields tw:hidden">
            <div class="commentImage">
                <img src="{{ BASE_URL }}/api/users?profileImage={{ session('userdata.id') }}&v={{ format(session('userdata.modified'))->timestamp() }}" />
            </div>
            <div class="commentReply">
                <textarea rows="5" cols="50" class="tiptapSimple" name="text"></textarea>
                <x-globals::forms.button submit type="success" name="comment" class="tw:ml-0">{{ __('buttons.save') }}</x-globals::forms.button>
            </div>
            <input type="hidden" name="comment" class="commenterField" value="1"/>
            <input type="hidden" name="father" class="commenterField" id="father-{{ $formHash }}" value="0"/>
            <input type="hidden" name="edit-comment-helper" class="commenterField" id="edit-comment-helper-{{ $formHash }}" />
            <br/>
        </div>
    @endif

    <div id="comments-{{ $formHash }}">
        <div>
            @foreach($tpl->get('comments') as $row)
                <div class="clearall">
                    <div class="commentImage" id="comment-image-to-hide-on-edit-{{ $formHash }}-{{ $row['id'] }}">
                        <img src="{{ BASE_URL }}/api/users?profileImage={{ $row['userId'] }}&v={{ format($row['userModified'])->timestamp() }}"/>
                    </div>
                    <div class="commentMain">
                        <div class="commentContent" id="comment-to-hide-on-edit-{{ $formHash }}-{{ $row['id'] }}">
                            <div class="right commentDate">
                                {!! sprintf(
                                    __('text.written_on'),
                                    format($row['date'])->date(),
                                    format($row['date'])->time()
                                ) !!}
                                    @if($login::userIsAtLeast($roles::$editor))
                                        <x-globals::actions.dropdown-menu container-class="pull-right tw:ml-2.5">
                                            @if(($row['userId'] == session('userdata.id')) || $login::userIsAtLeast($roles::$manager))
                                                <li><a href="{{ $deleteUrlBase . $row['id'] }}" class="deleteComment formModal">
                                                    <x-global::elements.icon name="delete" /> {{ __('links.delete') }}
                                                </a></li>
                                            @endif
                                            @if(($row['userId'] == session('userdata.id')) || $login::userIsAtLeast($roles::$manager))
                                                <li>
                                                    <a href="javascript:void(0);" onclick="toggleCommentBoxes({{ $row['id'] }}, null, '{{ $formHash }}', true)">
                                                        <x-global::elements.icon name="edit" /> {{ __('label.edit') }}
                                                    </a>
                                                </li>
                                            @endif
                                            @if(isset($tpl->get('ticket')->id))
                                                <li><a href="javascript:void(0);" onclick="leantime.ticketsController.addCommentTimesheetContent({{ $row['id'] }}, {{ $tpl->get('ticket')->id }});">{{ __('links.add_to_timesheets') }}</a></li>
                                            @endif
                                        </x-globals::actions.dropdown-menu>
                                    @endif
                            </div>
                            <span class="name">{{ sprintf(__('text.full_name'), e($row['firstname']), e($row['lastname'])) }}</span>
                            <div class="text mce-content-body" id="commentText-{{ $formHash }}-{{ $row['id'] }}">
                                <div id="comment-text-to-hide-{{ $formHash }}-{{ $row['id'] }}">{!! $tpl->escapeMinimal($row['text']) !!}</div>
                            </div>
                        </div>
                        <div class="commentLinks" id="comment-link-to-hide-on-edit-{{ $formHash }}-{{ $row['id'] }}">
                            @if($login::userIsAtLeast($roles::$commenter))
                                <a href="javascript:void(0);"
                                   onclick="toggleCommentBoxes({{ $row['id'] }}, null, '{{ $formHash }}')">
                                    <x-global::elements.icon name="reply" /> {{ __('links.reply') }}
                                </a>
                            @endif
                        </div>

                        <div class="replies">
                            @if($comments->getReplies($row['id']))
                                @foreach($comments->getReplies($row['id']) as $comment)
                                    <div>
                                        <div class="commentImage">
                                            <img src="{{ BASE_URL }}/api/users?profileImage={{ $comment['userId'] }}&v={{ format($comment['userModified'])->timestamp() }}"/>
                                        </div>
                                        <div class="commentMain">
                                            <div class="commentContent">
                                                <div class="right commentDate">
                                                    {!! sprintf(
                                                        __('text.written_on'),
                                                        format($comment['date'])->date(),
                                                        format($comment['date'])->time()
                                                    ) !!}
                                                </div>
                                                <span class="name">{{ sprintf(__('text.full_name'), e($comment['firstname']), e($comment['lastname'])) }}</span>
                                                <div class="text mce-content-body" id="comment-text-to-hide-reply-{{ $formHash }}-{{ $comment['id'] }}">{!! $tpl->escapeMinimal($comment['text']) !!}</div>
                                            </div>

                                            <div class="commentLinks">
                                                @if($login::userIsAtLeast($roles::$commenter))
                                                    <a href="javascript:void(0);"
                                                       onclick="toggleCommentBoxes({{ $row['id'] }}, null, '{{ $formHash }}')">
                                                        <x-global::elements.icon name="reply" /> {{ __('links.reply') }}
                                                    </a>
                                                    @if($comment['userId'] == session('userdata.id'))
                                                        <a href="{{ $deleteUrlBase . $comment['id'] }}"
                                                           class="deleteComment formModal">
                                                            <x-global::elements.icon name="delete" /> {{ __('links.delete') }}
                                                        </a>
                                                        <a href="javascript:void(0);" onclick="toggleCommentBoxes({{ $row['id'] }}, {{ $comment['id'] }}, '{{ $formHash }}', true, true)">
                                                            <x-global::elements.icon name="edit" /> {{ __('label.edit') }}
                                                        </a>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        <div class="clearall"></div>
                                    </div>
                                @endforeach
                            @endif
                            <div class="commentBox tw:hidden" id="comment-{{ $formHash }}-{{ $row['id'] }}">
                                <div class="commentImage">
                                    <img src="{{ BASE_URL }}/api/users?profileImage={{ session('userdata.id') }}&v={{ format(session('userdata.modified'))->timestamp() }}"/>
                                </div>
                                <div class="commentReply">
                                    <x-globals::forms.button submit type="primary" name="comment" id="submit-reply-button">{{ __('links.reply') }}</x-globals::forms.button>
                                    <x-globals::forms.button tag="button" type="primary" onclick="cancel({{ $row['id'] }}, '{{ $formHash }}')">{{ __('links.cancel') }}</x-globals::forms.button>
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

<script type="text/javascript">

    function toggleCommentBoxes(id, commentId, formHash, editComment = false, isReply = false) {
        @if($login::userIsAtLeast($roles::$commenter))

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

            // Destroy existing tiptap editors and remove their wrappers
            jQuery(`.commentBox-${formHash} .tiptap-wrapper`).each(function() {
                if (window.leantime && window.leantime.tiptapController && window.leantime.tiptapController.registry) {
                    leantime.tiptapController.registry.destroyWithin(this);
                }
                jQuery(this).remove();
            });
            jQuery(`.commentBox-${formHash} textarea`).remove();
            jQuery(`.commentBox-${formHash}`).hide();
            jQuery(`#comment-${formHash}-${id} .commentReply`).prepend(`<textarea rows="5" cols="75" name="text" id="editor_${formHash}-${id}" class="tiptapSimple">${editComment ? jQuery(`#comment-text-to-hide-${isReply ? 'reply-' : ''}${formHash}-${commentId || id}`).html() : ''}</textarea>`);
            if (window.leantime && window.leantime.tiptapController) {
                leantime.tiptapController.initSimpleEditor();
            }
            jQuery(`#comment-${formHash}-${id}`).show();
            jQuery(`#father-${formHash}`).val(id);

        @endif
    }
    function cancel(id, formHash) {
        @if($login::userIsAtLeast($roles::$commenter))
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
</script>
