@php
    use \Leantime\Core\Support\DateTimeInfoEnum;
@endphp

@props([
    "title" => "",
    "formHash" => md5(CURRENT_URL),
    "statusUpdates" => false
])

<h5 class="subtitle">{{ $title }}</h5>

<div id="comments-{{ $formHash }}">
    @if ($login::userIsAtLeast($roles::$commenter))
        <div class="mainToggler-{{ $formHash }}" id="">
            <div class="commentImage">
                <x-users::profile-image :user="array('id'=> session('userdata.id'), 'modified' => session('userdata.modified'))"></x-users::profile-image>
            </div>
            <div class="commentReply inactive">
                <a href="javascript:void(0);" onclick="leantime.commentsComponent.toggleCommentBoxes(0, '{{ $formHash }}')">
                    {{  __('links.add_new_comment') }}
                </a>
            </div>
        </div>
        <x-comments::input :formHash="$formHash" :parentId="0" :module="$module" :moduleId="$moduleId" :includeStatus="true" :statusUpdates="$statusUpdates"/>
    @endif

    @foreach ($comments as $comment)
        <x-comments::single-comment :comment="$comment" :formHash="$formHash" :replyParent="$comment->id" :module="$module" :moduleId="$moduleId" :statusUpdates="$statusUpdates"/>
    @endforeach

    @if (count($comments) == 0)
        <div class="clearall noCommentsMessage tw-pl-0">
            {{ __('text.no_comments') }}
        </div>
    @endif

    <div class="clearall"></div>
</div>



