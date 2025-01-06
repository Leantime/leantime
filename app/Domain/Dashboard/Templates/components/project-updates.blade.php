@props([
    'comments' => [],
    'delUrlBase' => '',
    'id' => '',
    'formHash' => md5(CURRENT_URL),
    'project_id' => '',
])

@if (!empty($id))
    <div hx-get="{{ BASE_URL }}/hx/dashboard/projectUpdates/get?id={{ $id }}" hx-trigger="load"
        hx-swap="innerHtml">
        <x-global::content.card variation="content">
            <div id='htmx-loader' class="justify-center align-center htmx-loader">
                <x-global::elements.loader id="loadingthis" size="25px" />
            </div>
            <div class="error-message" style="display: none; ">There is an error loading this section. Please try again.
            </div>
        </x-global::content.card>
    </div>
@else
    <div id="project-update-card">
        <x-global::content.card variation="content">
            <x-slot:card-context-buttons>
                @if ($login::userIsAtLeast($roles::$editor))
                    <x-global::forms.button content-role="ghost" data-tippy-content="{{ __('label.copy_url_tooltip') }}"
                        onclick="commentsComponent.toggleCommentBoxes(0, '{{ $formHash }}')">
                        <i class="fa fa-plus"></i> {{ __('links.add_new_report') }}
                    </x-global::forms.button>
                @endif
            </x-slot:card-context-buttons>

            <x-slot:card-title>{{ __('subtitles.project_updates') }}</x-slot:card-title>

            <x-dashboard::project-update-form :formHash="$formHash" :parentId="0"  />


            <div id="comments">
                @foreach ($comments as $row)
                    @if ($loop->iteration == 3)
                        <a href="javascript:void(0);" onclick="jQuery('.readMore').toggle('fast')">
                            {{ __('links.read_more') }}
                        </a>
                        <div class="readMore mt-[20px]" style="display: none;">
                    @endif

                    <x-dashboard::project-status-comment
                        :comment="$row"
                        :project_id="$project_id"
                        :ticket="$ticket ?? null"
                        :formHash="$formHash"
                        :replyParent="$row['id']"
                    />
                @endforeach

                @if (count($comments) >= 3)
            </div>
@endif
</div>

@if (count($comments) == 0)
    <div style="padding-left:0px; clear:both;" class="noCommentsMessage">
        {{ __('text.no_updates') }}
    </div>
@endif
<div class="clearall"></div>
</x-global::content.card>
    </div>
    <script type="module">
        import "@mix('/js/Domain/Comments/Js/commentsComponent.js')"
    </script>
@endif


