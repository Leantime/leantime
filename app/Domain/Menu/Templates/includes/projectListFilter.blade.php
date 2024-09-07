<div class="projectListFilter">

    <form
          hx-target="#mainProjectSelector"
          hx-swap="outerHTML"
          hx-trigger="change">
        <i class="fas fa-filter"></i>
        <select data-placeholder="" title=""
                hx-post="{{ BASE_URL }}/hx/menu/projectSelector/update-menu"
                hx-target="#mainProjectSelector"
                hx-swap="outerHTML"
                hx-indicator=".htmx-indicator, .htmx-loaded-content"
                name="client">
            <option value="" data-placeholder="true">All Clients</option>
            @foreach ($clients as $client)
                @if($client['id'] > 0)
                    <option value='{{ $client['id'] }}'
                    @if (isset($projectSelectFilter['client']) && $projectSelectFilter['client'] == $client['id'])
                        selected='selected'
                    @endif
                   >{{ $client['name'] }}</option>
                @endif
            @endforeach
        </select>
        <i class="fa-solid fa-diagram-project"></i>
        <select data-placeholder="" name="groupBy"
                hx-post="{{ BASE_URL }}/hx/menu/projectSelector/update-menu"
                hx-target="#mainProjectSelector"
                hx-indicator=".htmx-indicator, .htmx-loaded-content"
                hx-swap="outerHTML">
            @foreach ($projectSelectGroupOptions as $key => $group)
                <option value='{{ $key }}'

                    {{  $projectSelectFilter["groupBy"] == $key ? " selected='selected' " : "" }}

                >{{ $group }}</option>

            @endforeach
        </select>
        <input type="hidden" name="activeTab" value="" />

    </form>

</div>

<div class="htmx-indicator ml-m mr-m pt-l">
    <x-global::elements.loadingText type="project" count="5" includeHeadline="false"/>
</div>

