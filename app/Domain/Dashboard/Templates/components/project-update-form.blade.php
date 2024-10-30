@props([
    'id' => '',
])

<form 
    hx-post="{{ BASE_URL }}/hx/dashboard/projectUpdates/post?id={{ $id }}" 
    hx-target="#project-update-card" 
    hx-swap="outerHTML"
>
    <input type="hidden" name="comment" value="1" />
    
    @if ($login::userIsAtLeast($roles::$editor))
        <div id="comment0" class="commentBox hidden">
            {{-- Status Select --}}
            <x-global::forms.select 
                name="status" 
                id="projectStatus" 
                class="ml-0 mb-[10px]"
                :labelText="__('label.project_status_is')"
            >
                @foreach(['green', 'yellow', 'red'] as $color)
                    <x-global::forms.select.select-option value="{{ $color }}">
                        {{ __("label.project_status_$color") }}
                    </x-global::forms.select.select-option>
                @endforeach
            </x-global::forms.select>

            {{-- Comment Reply Section --}}
            <div class="commentReply">
                <textarea 
                    rows="5" 
                    cols="50" 
                    class="tinymceSimple w-full" 
                    name="text"
                ></textarea>

                {{-- Save Button --}}
                <x-global::forms.button 
                    type="submit" 
                    name="comment" 
                    contentRole='primary'
                >
                    {{ __('buttons.save') }}
                </x-global::forms.button>

                {{-- Cancel Button --}}
                <x-global::forms.button 
                    tag="a" 
                    href="javascript:void(0);"
                    onclick="leantime.dashboardController.commentsController.toggleCommentBoxes(-1); jQuery('.noCommentsMessage').toggle();"
                    content-role="ghost" 
                    class="leading-[50px]"
                >
                    {{ __('links.cancel') }}
                </x-global::forms.button>

                {{-- Hidden Fields --}}
                <input type="hidden" name="comment" value="1" />
                <input type="hidden" name="father" id="father" value="0" />
            </div>
        </div>
    @endif
</form> 