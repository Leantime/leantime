{{--
    One search result row.

    Props:
      result   Leantime\Domain\Search\Models\SearchResult
      tokens   Search tokens to highlight.
      variant  "compact" (header dropdown) or "full" (results page).

    When the result lives in the session's current project and declares a modal path, the link
    is a hash route that opens the modal in place. Otherwise it goes through /search/open, which
    switches the project and redirects.
--}}
@props([
    'result',
    'tokens' => [],
    'variant' => 'compact',
])

@php
    $opensInModal = $result->modalPath !== null
        && $result->projectId !== null
        && (int) session('currentProject') === (int) $result->projectId;

    $href = $opensInModal
        ? '#'.$result->modalPath
        : BASE_URL.'/search/open?type='.urlencode($result->type).'&id='.$result->id;

    // The dropdown is a combobox listbox (options); the results page is a plain list.
    $role = $variant === 'compact' ? 'option' : 'listitem';
@endphp

<a
    class="searchResult searchResult--{{ $variant }}"
    role="{{ $role }}"
    href="{{ $href }}"
    data-search-type="{{ $result->type }}"
    data-search-id="{{ $result->id }}"
>
    <span class="searchResult__icon {{ $result->icon }}" aria-hidden="true"></span>
    <span class="searchResult__body">
        <span class="searchResult__title">{!! \Leantime\Domain\Search\Support\Highlighter::mark($result->title, $tokens) !!}</span>
        @if ($result->snippet !== '')
            <span class="searchResult__snippet">{!! \Leantime\Domain\Search\Support\Highlighter::mark($result->snippet, $tokens) !!}</span>
        @endif
        @if ($result->badge !== '' || $result->projectName !== '' || $result->modified)
            <span class="searchResult__meta">
                @if ($result->badge !== '')
                    <span class="searchResult__badge">{{ $result->badge }}</span>
                @endif
                @if ($result->projectName !== '')
                    <span class="searchResult__project">{{ $result->projectName }}</span>
                @endif
                @if ($result->modified)
                    <span class="searchResult__date">{{ format($result->modified)->date() }}</span>
                @endif
            </span>
        @endif
    </span>
</a>
