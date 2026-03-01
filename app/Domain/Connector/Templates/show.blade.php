<div class="pageheader">
    <div class="pageicon"><x-global::elements.icon name="hub" /></div>
    <div class="pagetitle">
       <h1>Integrations</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}
        <h5 class="subtitle">Sync Leantime with your external applications</h5>
        <p>Available Integrations</p>

        <div class="row">
            @foreach ($tpl->get('providers') as $provider)
                <div class="col-md-3">
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
                            <x-globals::forms.button link="{{ $provider->button['url'] }}" type="primary">{{ $provider->button['text'] }}</x-globals::forms.button>
                        @else
                            <x-globals::forms.button link="{{ BASE_URL }}/connector/integration?provider={{ $provider->id }}" type="primary">Create New Integration</x-globals::forms.button>
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
