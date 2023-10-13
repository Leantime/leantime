@extends($layout)

@section('content')

    <x-global::pageheader :icon="'fa fa-plug'">
        <h1>My Apps</h1>
    </x-global::pageheader>


<div class="maincontent">

    @include('plugins::partials.plugintabs',  ["currentUrl" => "installed"])

    <div class="maincontentinner">

        {!!  $tpl->displayNotification(); !!}

        <div class="row">
            <div class="col-lg-12">
                <h5 class="subtitle">
                    {{ __("text.installed_plugins") }}
                </h5>
                <div class="row sortableTicketList">
                    @foreach($tpl->get("installedPlugins") as $installedPlugins)
                        <div class="col-md-4">
                            <div class="ticketBox fixed">
                                <div class="row">
                                    <div class="col-md-12" style="max-height:150px; overflow:hidden;margin-bottom:15px; text-align:center;">
                                        <img src="{{ $installedPlugins->getPluginImageData() }}" style="max-height:350px"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="subtitle">{{ $installedPlugins->name }}<br /></h5>
                                    </div>
                                </div>
                                <div class="row" style="margin-bottom:15px;">

                                    <div class="col-md-4">

                                        {{ __("text.version") }} {{ $installedPlugins->version }}

                                    </div>
                                    <div class="col-md-8">
                                        {{ $installedPlugins->description }}<br />
                                        @if (is_array($installedPlugins->authors) && count($installedPlugins->authors) > 0)
                                            {{ __("text.by") }} <a href="mailto:{{ $installedPlugins->authors[0]->email }}">{{ $installedPlugins->authors[0]->name }}</a>
                                        @endif
                                        | <a href="{{ $installedPlugins->homepage }}" target="_blank"> {{ $tpl->__("text.visit_site") }} </a><br />
                                    </div>
                                </div>
                                <div class="row" style="border-top:1px solid var(--main-border-color);">
                                    <div class="col-md-8" style="padding-top:10px;">
                                        @if (!$installedPlugins->enabled)
                                            <a href="{{ BASE_URL }}/plugins/show?enable={{ $installedPlugins->id }}" class=""><i class="fa-solid fa-plug-circle-check"></i> {{ __('buttons.enable') }}</a> |
                                            <a href="{{ BASE_URL }}/plugins/show?remove={{ $installedPlugins->id }}" class="delete"><i class="fa fa-trash"></i> {{ __('buttons.remove')  }}</a>
                                        @else
                                            <a href="{{ BASE_URL }}/plugins/show?disable={{ $installedPlugins->id }}" class="delete"><i class="fa-solid fa-plug-circle-xmark"></i> {{ __('buttons.disable')  }}</a>
                                        @endif
                                    </div>
                                    <div class="col-md-4" style="padding-top:10px; text-align:right;">
                                        @if (file_exists(APP_ROOT . '/app/plugins/' . $installedPlugins->foldername . '/controllers/class.settings.php')) {?>
                                            <a href="{{ BASE_URL }}/{{ $installedPlugins->foldername  }}/settings"><i class="fa fa-cog"></i> Settings</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if ($tpl->get("installedPlugins") === false || count($tpl->get("installedPlugins")) == 0)
                            {{ __("text.no_plugins_installed") }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
