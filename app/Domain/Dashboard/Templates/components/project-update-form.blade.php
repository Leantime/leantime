@props([
    'id' => '',
    'formHash' => '',
    'parentId' => '',
    'includeStatus' => true,
])

<form hx-post="{{ BASE_URL }}/hx/dashboard/projectUpdates/post?id={{ $id }}"
    hx-target="#project-update-card" hx-swap="outerHTML">
    @if ($login::userIsAtLeast($roles::$editor))

        <div id="commentReplyBox-{{ $formHash }}-{{ $parentId }}"
            class="commentBox-{{ $formHash }} commentReplyBox-{{ $formHash }} commenterFields hidden mb-sm">
            @if ($includeStatus)
                <x-global::forms.select name="status" id="projectStatus" class="ml-0 mb-[10px]" :labelText="__('label.project_status_is')">
                    @foreach (['green', 'yellow', 'red'] as $color)
                        <x-global::forms.select.select-option value="{{ $color }}">
                            {{ __("label.project_status_$color") }}
                        </x-global::forms.select.select-option>
                    @endforeach
                </x-global::forms.select>
            @endif
            <div class="commentReply">
                <x-global::forms.submit-button name="{{ __('links.save') }}" />
                <x-global::forms.reset-button name="{{ __('links.cancel') }}"
                    onclick="commentsComponent.resetForm(-1, '{{ $formHash }}')" />
            </div>

            <input type="hidden" name="saveComment" class="commenterField" value="1" />
            <input type="hidden" name="editComment" class="commenterField"
                id="edit-comment-{{ $formHash }}-{{ $parentId }}" value="" />
            <input type="hidden" name="father" class="commenterField" id="father-{{ $formHash }}"
                value="{{ $parentId }}" />

            <div class="clearall"></div>
            <br />
        </div>
    @endif
</form>


