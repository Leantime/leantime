@php
    use \Leantime\Core\Support\DateTimeInfoEnum;
@endphp

@props([
    "title" => "",
    "formHash" => md5(CURRENT_URL)
])

<h5 class="subtitle">{{ $title }}</h5>

<form hx-post="{{ BASE_URL }}/comments/comment-list/save?module={{ $module }}&moduleId={{ $moduleId }}"
      hx-target="#comments-{{ $module }}-{{ $moduleId }}">

    @if ($login::userIsAtLeast($roles::$commenter))
        <div class="mainToggler-{{ $formHash }}" id="">
            <div class="commentImage">
                <x-users::profile-image :user="array('id'=> session('userdata.id'), 'modified' => session('userdata.modified'))"></x-users::profile-image>
            </div>
            <div class="commentReply inactive">
                <a href="javascript:void(0);" onclick="leantime.commentsController.toggleCommentBoxes(0, '{{ $formHash }}')">
                    {{  __('links.add_new_comment') }}
                </a>
            </div>
        </div>

        <div id="comment-{{ $formHash }}-0" class="commentBox-{{ $formHash }} commenterFields tw-hidden" >
            <div class="commentImage">
                <x-users::profile-image :user="array('id'=> session('userdata.id'), 'modified' => session('userdata.modified'))" ></x-users::profile-image>
            </div>
            <div class="commentReply">
                <x-global::forms.text-editor :value="''" :editorId="$formHash" :type="\Leantime\Core\Support\EditorTypeEnum::Simple"></x-global::forms.text-editor>
                <x-global::forms.submit-button name="comment" />
            </div>
            <input type="hidden" name="comment" class="commenterField" value="1"/>
            <input type="hidden" name="father" class="commenterField" id="father-{{ $formHash }}" value="0"/>
            <br/>
        </div>
    @endif

    <div id="comments-{{ $formHash }}">
        @foreach ($comments as $comment)
            <div class="commentMain">
                <x-comments::single-comment :comment="$comment" :formHash="$formHash" :replyParent="$comment->id" />
            </div>
        @endforeach
    </div>
    @if (count($comments) == 0)
        <div style="padding-left:0px; clear:both;" class="noCommentsMessage">
            {{ __('text.no_comments') }}
        </div>
    @endif
    <div class="clearall"></div>
</form>


