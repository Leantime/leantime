@props([
    'session' => null,
    'itemsByType' => [],
    'itemTypes' => [],
    'canEdit' => false,
    'focusType' => null,
])

@php
    $typeIcons = [
        'talking_point' => 'fa-comments',
        'action_item' => 'fa-check-square',
        'feedback' => 'fa-comment-dots',
        'goal' => 'fa-bullseye',
        'blocker' => 'fa-triangle-exclamation',
    ];
@endphp

@if ($session === null)
    <div class="tw-p-m">
        <p>{{ __('text.oneonone.session_not_found') }}</p>
    </div>
@else
    <div class="row">
        @foreach ($itemTypes as $typeKey => $typeLabel)
            @php
                $items = $itemsByType[$typeKey] ?? [];
                $icon = $typeIcons[$typeKey] ?? 'fa-list';
            @endphp

            <div class="col-md-6 col-sm-12 tw-mb-m">
                <div class="tw-p-m tw-rounded tw-h-full"
                     style="background:var(--secondary-background); border:1px solid var(--main-border-color);">

                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-s">
                        <h5 class="tw-m-0">
                            <span class="fa {{ $icon }}"></span>
                            {{ __($typeLabel) }}
                            <small style="color:var(--grey);">({{ count($items) }})</small>
                        </h5>
                    </div>

                    {{-- Existing items --}}
                    @if (count($items) === 0)
                        <p class="tw-text-sm tw-mb-s" style="color:var(--grey);">
                            {{ __('text.oneonone.no_items_yet') }}
                        </p>
                    @else
                        <ul class="tw-list-none tw-p-0 tw-m-0 tw-mb-s">
                            @foreach ($items as $item)
                                @include('oneonone::partials.sessionItem', [
                                    'item' => $item,
                                    'canEdit' => $canEdit,
                                ])
                            @endforeach
                        </ul>
                    @endif

                    {{-- Add new --}}
                    @if ($canEdit)
                        <form
                            hx-post="{{ BASE_URL }}/hx/oneonone/sessionItems/addItem"
                            hx-target="#oneononeItemList"
                            hx-swap="innerHTML"
                            hx-on::after-request="this.querySelector('input[name=content]').value=''">
                            @if(isset($csrf_token))
                                <input type="hidden" name="csrf_token" value="{{ $csrf_token }}">
                            @endif
                            <input type="hidden" name="sessionId" value="{{ $session['id'] }}">
                            <input type="hidden" name="type" value="{{ $typeKey }}">
                            <div class="tw-flex tw-gap-s">
                                <input type="text" name="content"
                                       class="form-control tw-flex-1"
                                       placeholder="{{ __('placeholder.oneonone.add_' . $typeKey) }}"
                                       maxlength="2000"
                                       required
                                       @if($focusType === $typeKey) autofocus @endif>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <span class="fa fa-plus"></span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
