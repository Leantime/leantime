@props([
    'plugin' => new \Leantime\Domain\Plugins\Models\Plugins()
])

<div class="col-md-4">
    <div class="ticketBox fixed" style="padding-top:0px; overflow: hidden;">
        <div class="row">
            <div class="col-md-12 tw-p-none tw-overflow-hidden tw-mb-m tw-text-center tw-max-h-[150px]">
                <img src="{{ $plugin->getPluginImageData() }}" style="max-height:350px"/>

                @if($plugin->type == "marketplace")
                    <div class="certififed label-default tw-absolute tw-top-[10px] tw-right-[10px] tw-text-primary tw-rounded-full tw-text-sm"

                         data-tippy-content="This plugin was downloaded from the Leantime Marketplace and is signature verified">
                        <i class="fa fa-certificate"></i>
                        Certified
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h5 class="subtitle">{{ $plugin->name }}<br /></h5>
            </div>
        </div>
        <div class="row" style="margin-bottom:15px;">

            <div class="col-md-4">
                {{ __("text.version") }} {{ $plugin->version }}
            </div>
            <div class="col-md-8">
                {{ $plugin->description }}<br />
                @if (is_array($plugin->authors) && count($plugin->authors) > 0)
                    {{ __("text.by") }} <a href="mailto:{{ $plugin->authors[0]->email }}">{{ $plugin->authors[0]->name }}</a>
                @endif
                | <a href="{{ $plugin->homepage }}" target="_blank"> {{ $tpl->__("text.visit_site") }} </a><br />
            </div>
        </div>
        <div class="row" style="border-top:1px solid var(--main-border-color);">
            @if($plugin->type !== "system")
                <div class="col-md-8" style="padding-top:10px;">
                    @if (!$plugin->enabled)
                        <a href="{{ BASE_URL }}/plugins/show?enable={{ $plugin->id }}" class=""><i class="fa-solid fa-plug-circle-check"></i> {{ __('buttons.enable') }}</a> |
                        <a href="{{ BASE_URL }}/plugins/show?remove={{ $plugin->id }}" class="delete"><i class="fa fa-trash"></i> {{ __('buttons.remove')  }}</a>
                    @else
                        <a href="{{ BASE_URL }}/plugins/show?disable={{ $plugin->id }}" class="delete"><i class="fa-solid fa-plug-circle-xmark"></i> {{ __('buttons.disable')  }}</a>
                    @endif
                </div>
                <div class="col-md-4" style="padding-top:10px; text-align:right;">
                    @if (file_exists(APP_ROOT . '/app/plugins/' . $plugin->foldername . '/controllers/class.settings.php')) {?>
                    <a href="{{ BASE_URL }}/{{ $plugin->foldername  }}/settings"><i class="fa fa-cog"></i> Settings</a>
                    @endif
                </div>
            @else
                <p>System Plugin, cannot be disabled or removed</p>
            @endif
        </div>
    </div>
</div>
