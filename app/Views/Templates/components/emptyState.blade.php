@props([
    'icon'        => 'fa-inbox',
    'headline'    => 'Nothing here yet',
    'description' => '',
    'actionLabel' => '',
    'actionHref'  => '',
    'actionAttrs' => '',   // extra HTML attributes (e.g. hx-post, data-*)
])

<div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-py-10 tw-text-center tw-gap-3">
    <span class="fa {{ $icon }} tw-text-4xl" style="color: var(--accent2); opacity: 0.6;"></span>
    <p class="tw-text-base tw-font-semibold" style="color: var(--primary-font-color);">{{ $headline }}</p>
    @if($description)
        <p class="tw-text-sm" style="color: var(--primary-font-color); opacity: 0.65;">{{ $description }}</p>
    @endif
    @if($actionLabel)
        <a href="{{ $actionHref ?: 'javascript:void(0);' }}" {!! $actionAttrs !!}
           class="btn btn-primary tw-mt-1">
            <i class="fa fa-plus"></i> {{ $actionLabel }}
        </a>
    @endif
</div>
