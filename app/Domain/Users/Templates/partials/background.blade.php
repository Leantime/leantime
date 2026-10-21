{{--
    "Background" tab of the user's own settings (Hxcontrollers\Background).

    Fragments:
      current-background  The gradient / image choice plus the page's background style (swapped
                          out of band). Returned after every change.
      unsplash-results    Unsplash search results (only reachable when Unsplash is configured).

    One change at a time: every change (gradient, photo, upload) syncs on #backgroundPicker with
    "drop", so clicks while a change is running are ignored and a slower response can't repaint a
    background the user already replaced. ("queue" would lose them: the response replaces the
    gradient button, and htmx drops queued requests of removed elements.) While a change runs,
    #backgroundPicker has .htmx-request (hx-indicator) and shows it is busy.
--}}
<style>
    #backgroundPicker.htmx-request { cursor: progress; }
    #backgroundPicker.htmx-request #currentBackground { opacity: 0.5; }
</style>
<div class="tw-max-w-4xl" id="backgroundPicker">
    <p>{{ __('text.set_your_background') }}</p>
    <br />

    @fragment('current-background')
    <div id="currentBackground">
        <label><strong>{{ __('label.background_type') }}</strong></label>
        {{-- Native buttons (not global::selectable) so the choice works with the keyboard. --}}
        <div class="tw-flex tw-gap-4" role="group" aria-label="{{ __('label.background_type') }}">
            <button type="button"
                    @class(['selectable tw-bg-transparent tw-border-0 tw-p-0', 'active' => $backgroundType !== 'image'])
                    aria-pressed="{{ $backgroundType !== 'image' ? 'true' : 'false' }}"
                    hx-post="{{ BASE_URL }}/hx/users/background/setType"
                    hx-sync="#backgroundPicker:drop"
                    hx-indicator="#backgroundPicker"
                    hx-vals='{"type": "gradient"}'
                    hx-target="#currentBackground"
                    hx-swap="outerHTML">
                <span class="selectableContent tw-block">
                    <span class="tw-block"
                          style="width:65px; height:65px; border-radius:10px; background:linear-gradient(135deg, {{ $primaryColor }} 20%, {{ $secondaryColor }} 100%);"></span>
                </span>
                <span class="selectable-label">{{ __('label.gradient') }}</span>
            </button>

            @if($backgroundType === 'image' && $backgroundImage !== '')
                {{-- The current image: shown, not a control (pick another image below). --}}
                <div class="selectable active">
                    <span class="selectableContent tw-block">
                        <img src="{{ $backgroundImage }}" alt="" style="width:65px; height:65px; object-fit:cover; border-radius:10px;" />
                    </span>
                    <span class="selectable-label">{{ __('label.image') }}</span>
                </div>
            @else
                <div class="tw-flex tw-items-center tw-w-[150px] tw-text-sm tw-opacity-70">
                    {{ __('text.pick_background_image_below') }}
                </div>
            @endif
        </div>

        @include('global::sections.backgroundStyle', ['oob' => true])
    </div>
    @endfragment

    <hr />

    <label><strong>{{ __('label.background_image') }}</strong></label>

    @if($unsplashEnabled)
        <x-global::forms.button-group segmented :label="__('label.background_source')" class="tw-mb-4">
            <x-global::forms.button class="active" aria-pressed="true"
                onclick="document.getElementById('unsplashSearch').classList.remove('tw-hidden'); document.getElementById('backgroundUpload').classList.add('tw-hidden');"
                leadingVisual="fa-solid fa-camera" :labelText="__('label.unsplash')" />
            <x-global::forms.button aria-pressed="false"
                onclick="document.getElementById('backgroundUpload').classList.remove('tw-hidden'); document.getElementById('unsplashSearch').classList.add('tw-hidden');"
                leadingVisual="fa-solid fa-upload" :labelText="__('label.upload')" />
        </x-global::forms.button-group>

        <div id="unsplashSearch">
            <form class="tw-flex tw-items-center tw-gap-2"
                  hx-get="{{ BASE_URL }}/hx/users/background/search"
                  hx-target="#unsplashResults"
                  hx-indicator="#unsplashLoading">
                <input type="text"
                       name="q"
                       class="main-search-input"
                       placeholder="{{ __('input.search_unsplash') }}"
                       aria-label="{{ __('label.search_for_image') }}"
                       hx-get="{{ BASE_URL }}/hx/users/background/search"
                       hx-trigger="load, keyup changed delay:500ms"
                       hx-target="#unsplashResults"
                       hx-indicator="#unsplashLoading" />
                {{-- Only the refresh button sends "page" (a changed query starts at 1); search results update it. --}}
                <input type="hidden" name="page" id="unsplashNextPage" value="2" />
                <x-global::forms.button inputType="submit" icon="fa fa-refresh" :ariaLabel="__('buttons.refresh_images')" />
                <div id="unsplashLoading" class="htmx-indicator-small">
                    <x-global::loader id="unsplashLoader" size="25px" />
                </div>
            </form>

            <div id="unsplashResults" class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-4 tw-mt-4">
                @fragment('unsplash-results')
                    @isset($nextPage)
                        <input type="hidden" name="page" id="unsplashNextPage" value="{{ $nextPage }}" hx-swap-oob="true" />
                    @endisset
                    @isset($photos)
                    @forelse($photos as $photo)
                        <div class="tw-rounded-lg tw-overflow-hidden">
                            <button type="button"
                                    class="tw-block tw-w-full tw-p-0 tw-border-0 tw-bg-transparent tw-cursor-pointer"
                                    title="{{ __('buttons.use_image') }}"
                                    hx-post="{{ BASE_URL }}/hx/users/background/useUnsplashPhoto"
                                    hx-sync="#backgroundPicker:drop"
                                    hx-indicator="#backgroundPicker"
                                    hx-vals="{{ json_encode(['photoId' => $photo['id']]) }}"
                                    hx-target="#currentBackground"
                                    hx-swap="outerHTML">
                                <img src="{{ $photo['thumbnailUrl'] }}"
                                     alt="{{ $photo['description'] }}"
                                     loading="lazy"
                                     class="tw-w-full tw-h-36 tw-object-cover tw-rounded-lg" />
                            </button>
                            <div class="tw-text-xs tw-mt-1 tw-truncate">
                                {!! sprintf(
                                    e(__('text.photo_by_on_unsplash')),
                                    '<a href="'.e($photo['photographerUrl']).'" target="_blank" rel="noopener noreferrer">'.e($photo['photographerName']).'</a>',
                                    '<a href="https://unsplash.com/?utm_source=leantime&amp;utm_medium=referral" target="_blank" rel="noopener noreferrer">Unsplash</a>'
                                ) !!}
                            </div>
                        </div>
                    @empty
                        <div class="tw-col-span-full">
                            <x-global::elements.empty-state compact icon="fa fa-image" :title="__('text.no_images_found')" />
                        </div>
                    @endforelse
                    @endisset
                @endfragment
            </div>
        </div>
    @endif

    <div id="backgroundUpload" @class(['tw-hidden' => $unsplashEnabled])>
        <form class="tw-flex tw-items-center tw-gap-2"
              hx-post="{{ BASE_URL }}/hx/users/background/upload"
              hx-sync="#backgroundPicker:drop"
              hx-encoding="multipart/form-data"
              hx-target="#currentBackground"
              hx-swap="outerHTML"
              hx-indicator="#backgroundPicker, #backgroundUploadLoading">
            <input type="file" name="file" accept="image/jpeg,image/png,image/gif,image/webp" required
                   aria-label="{{ __('label.background_image') }}" />
            <x-global::forms.button inputType="submit" contentRole="primary" :labelText="__('buttons.upload')" />
            <div id="backgroundUploadLoading" class="htmx-indicator-small">
                <x-global::loader id="backgroundUploadLoader" size="25px" />
            </div>
        </form>
    </div>
</div>
