<div class="commentBox tw:hidden" id="comment{!! $commentId !!}">
    <div class="commentImage">
        <x-users::profile-image :user="$user" />
    </div>
    <div class="commentReply">
        <x-globals::forms.button :submit="true" contentRole="secondary" name="comment">{{ __('links.reply') }}</x-globals::forms.button>
    </div>
    <div class="clearall"></div>
</div>
