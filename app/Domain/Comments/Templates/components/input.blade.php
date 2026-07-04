<div class="commentBox tw-hidden" id="comment{!! $commentId !!}">
    <div class="commentImage">
        <x-users::profile-image :user="$user" />
    </div>
    <div class="commentReply">
        <x-global::forms.button tag="input" inputType="submit" contentRole="default" :labelText="__('links.reply')" name="comment" />
    </div>
    <div class="clearall"></div>
</div>
