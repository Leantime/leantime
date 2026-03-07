<div class="commentBox tw:hidden" id="comment{!! $commentId !!}">
    <div class="commentImage">
        <x-users::profile-image :user="$user" />
    </div>
    <div class="commentReply">
        <input
            type="submit"
            value="{{ __('links.reply') }}"
            name="comment"
            class="btn btn-default"
        />
    </div>
    <div class="clearall"></div>
</div>
