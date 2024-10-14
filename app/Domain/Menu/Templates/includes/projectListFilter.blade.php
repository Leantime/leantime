<div class="projectListFilter">

    <form hx-target="#mainProjectSelector" hx-swap="outerHTML" hx-trigger="change">
        <i class="fas fa-filter"></i>
        <x-global::forms.select data-placeholder="" title=""
            hx-post="{{ BASE_URL }}/hx/menu/projectSelector/update-menu" hx-target="#mainProjectSelector"
            hx-swap="outerHTML" hx-indicator=".htmx-indicator, .htmx-loaded-content" name="client">
            <x-global::forms.select.select-option value="" data-placeholder="true">
                All Clients
            </x-global::forms.select.select-option>

            @foreach ($clients as $client)
                @if ($client['id'] > 0)
                    <x-global::forms.select.select-option :value="$client['id']" :selected="isset($projectSelectFilter['client']) && $projectSelectFilter['client'] == $client['id']">
                        {{ $client['name'] }}
                    </x-global::forms.select.select-option>
                @endif
            @endforeach
        </x-global::forms.select>

        <i class="fa-solid fa-diagram-project"></i>

        <x-global::forms.select data-placeholder="" name="groupBy"
            hx-post="{{ BASE_URL }}/hx/menu/projectSelector/update-menu" hx-target="#mainProjectSelector"
            hx-indicator=".htmx-indicator, .htmx-loaded-content" hx-swap="outerHTML">
            @foreach ($projectSelectGroupOptions as $key => $group)
                <x-global::forms.select.select-option :value="$key" :selected="$projectSelectFilter['groupBy'] == $key">
                    {{ $group }}
                </x-global::forms.select.select-option>
            @endforeach
        </x-global::forms.select>

        <input type="hidden" name="activeTab" value="" />

    </form>

</div>

<div class="htmx-indicator ml-m mr-m pt-l">
    <x-global::elements.loadingText type="project" count="5" includeHeadline="false" />
</div>
