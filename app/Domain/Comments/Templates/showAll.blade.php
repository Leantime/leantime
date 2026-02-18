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
            class="fa fa-comments"></span>{{ __('subtitles.discussion') }}
</h4>

<form method="post" accept-charset="utf-8" action="{{ $formUrl }}"
      id="commentForm">
    <a href="javascript:void(0);" onclick="toggleCommentBoxes(0)"
       class="tw:hidden" id="mainToggler"><span
                class="fa fa-plus-square"></span> {{ __('links.add_new_comment') }}
    </a>

    <div id="comment0" class="commentBox">
        <textarea rows="5" cols="50" class="tiptapSimple"
                  name="text"></textarea><br/>
        <x-global::button submit type="success" name="comment" class="tw:ml-0">{{ __('buttons.save') }}</x-global::button>
        <input type="hidden" name="comment" value="1"/>
        <input type="hidden" name="father" id="father" value="0"/>
        <br/>
    </div>
    <hr/>

    <div id="comments">
        <div>
            @foreach($tpl->get('comments') as $row)
                <div class="tw:block tw:p-2.5 tw:mt-2.5 tw:border-b tw:border-solid tw:border-[#f0f0f0]">
                    <img src="{{ BASE_URL }}/api/users?profileImage={{ $row['userId'] }}&v={{ format($row['userModified'])->timestamp() }}"
                         class="tw:float-left tw:w-[50px] tw:mr-2.5 tw:p-0.5"/>
                    <div class="right">{!! sprintf(
                        __('text.written_on'),
                        format($row['date'])->date(),
                        format($row['date'])->time()
                    ) !!}</div>
                    <strong>
                    {{ sprintf(__('text.full_name'), e($row['firstname']), e($row['lastname'])) }}
                    </strong><br/>
                    <div class="tw:ml-[60px]">{!! $row['text'] !!}</div>
                    <div class="tw:clear-both"></div>
                    <div class="tw:pl-[60px]">
                        <a href="javascript:void(0);" class="replyButton"
                           onclick="toggleCommentBoxes({{ $row['id'] }})">
                            <span class="fa fa-reply"></span> {{ __('links.reply') }}
                        </a>

                        @if($row['userId'] == session('userdata.id'))
                            |
                            <a href="{{ $deleteUrlBase . $row['id'] }}"
                               class="deleteComment">
                                <span class="fa fa-trash"></span> {{ __('links.delete') }}
                            </a>
                        @endif
                        <div class="tw:hidden"
                             id="comment{{ $row['id'] }}"
                             class="commentBox">
                            <br/><x-global::button submit type="secondary" name="comment">{{ __('links.reply') }}</x-global::button>
                        </div>
                    </div>
                    <div class="tw:clear-both"></div>
                </div>

                @if($comments->getReplies($row['id']))
                    @foreach($comments->getReplies($row['id']) as $comment)
                        <div class="tw:block tw:p-2.5 tw:pl-[60px] tw:border-b tw:border-solid tw:border-[#f0f0f0]">
                            <img src="{{ BASE_URL }}/api/users?profileImage={{ $comment['userId'] }}&v={{ $comment['userModified'] }}"
                                 class="tw:float-left tw:w-[50px] tw:mr-2.5 tw:p-0.5"/>
                            <div>
                                <div class="right">
                                    {!! sprintf(
                                        __('text.written_on'),
                                        format($comment['date'])->date(),
                                        format($comment['date'])->time()
                                    ) !!}
                                </div>
                                <strong>
                                {{ sprintf(__('text.full_name'), e($comment['firstname']), e($comment['lastname'])) }}
                                </strong><br/>
                                <p class="tw:ml-[60px]">{!! nl2br($comment['text']) !!}</p>
                                <div class="tw:clear-both"></div>

                                <div class="tw:pl-[60px]">
                                    @if($comment['userId'] == session('userdata.id'))
                                        <a href="{{ $deleteUrlBase . $comment['id'] }}"
                                           class="deleteComment">
                                            <span class="fa fa-trash"></span> {{ __('links.delete') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>

        @if(count($tpl->get('comments')) == 0)
            <div class="text-center">
                <div class="svgContainer tw:w-1/3">
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_real_time_collaboration_c62i.svg') !!}
                    {{ $language->__('text.no_comments') }}
                </div>
            </div>
        @endif
    </div>
</form>

<script type="text/javascript">

    if (window.leantime && window.leantime.tiptapController) {
        leantime.tiptapController.initSimpleEditor();
    }

    function toggleCommentBoxes(id) {
        @if($login::userIsAtLeast($roles::$commenter))
            if (id == 0) {
                jQuery('#mainToggler').hide();
            } else {
                jQuery('#mainToggler').show();
            }
            jQuery('.commentBox').hide('fast', function () {
                jQuery('.commentBox textarea').remove();
                jQuery('#comment' + id + '').prepend('<textarea rows="5" cols="75" name="text" class="tiptapSimple"></textarea>');
                if (window.leantime && window.leantime.tiptapController) {
                    leantime.tiptapController.initSimpleEditor();
                }
            });

            jQuery('#comment' + id + '').show('fast');
            jQuery('#father').val(id);
        @endif
    }

</script>
