@extends($layout)

@section('content')

<x-global::layout.page-header icon="fa fa-plug">
    {!! __('headlines.providers') !!}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

    </x-global::layout.content-box>
</div>

@once
@push('scripts')
<script type="text/javascript">
   jQuery(document).ready(function() {
   });
</script>
@endpush
@endonce

@endsection
