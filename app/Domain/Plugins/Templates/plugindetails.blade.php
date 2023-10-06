@extends($layout)

@section('content')
    @foreach ($versions as $version)
        <div class="tw-p-4 tw-border-b">
            @if (! empty($version['thumbnail_url']))
                <img src="{{ $version['thumbnail_url'] }}" alt="{{ $version['name'] }}" class="tw-w-full tw-h-auto tw-mb-4">
            @endif

            @if (! empty($version['name']))
                <h2 class="tw-text-2xl">{{ $version['name'] }}</h2>
            @endif

            @if (! empty($version['description']))
                <p>{{ $version['description'] }}</p>
            @endif

            @if (! empty($version['marketplace_url']))
                <a
                    href="{{ $version['marketplace_url'] }}"
                    class="btn btn-primary"
                    target="_blank"
                    rel="noopener noreferrer"
                >Get a license</a>
            @endif
        </div>
    @endforeach
@endsection
