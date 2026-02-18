<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-circle-nodes"></i></div>
    <div class="pagetitle">
       <h1>Integrations</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}
        <h5 class="subtitle">Sync Leantime with your external applications</h5>
        <p>Available Integrations</p>

        <div class="tw:grid tw:grid-cols-4 tw:gap-6">
            @foreach ($tpl->get('providers') as $provider)
                <div>
                    <div class="profileBox">
                        <div class="commentImage gradient">
                            <img src="{{ BASE_URL }}/{{ $provider->image }}"/>
                        </div>
                        <span class="userName">
                            <strong>{{ $provider->name }}</strong>
                            <br /><small>Available methods: {{ implode(', ', $provider->methods) }}</small>
                            <br /><br />
                            {!! $provider->description !!}
                        </span>
                        <br />

                        @if (isset($provider->button))
                            <x-global::button link="{{ $provider->button['url'] }}" type="primary">{{ $provider->button['text'] }}</x-global::button>
                        @else
                            <x-global::button link="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}" type="primary">Create New Integration</x-global::button>
                        @endif

                        <div class="clearall"></div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>

<script type="text/javascript">
   jQuery(document).ready(function() {
    });
</script>
