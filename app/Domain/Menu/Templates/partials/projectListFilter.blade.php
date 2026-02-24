<div class="projectListFilter">

    <form
          hx-target="#mainProjectSelector"
          hx-swap="outerHTML"
          hx-trigger="change">
        <i class="fas fa-filter"></i>
        <x-globals::forms.select :bare="true" data-placeholder="" title=""
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
        </x-globals::forms.select>
        <i class="fa-solid fa-diagram-project"></i>
        <x-globals::forms.select :bare="true" data-placeholder="" name="groupBy"
                hx-post="{{ BASE_URL }}/hx/menu/projectSelector/update-menu"
                hx-target="#mainProjectSelector"
                hx-indicator=".htmx-indicator, .htmx-loaded-content"
                hx-swap="outerHTML">
            @foreach ($projectSelectGroupOptions as $key => $group)
                <option value='{{ $key }}'

                    {{  $projectSelectFilter["groupBy"] == $key ? " selected='selected' " : "" }}

                >{{ $group }}</option>

            @endforeach
        </x-globals::forms.select>
        <input type="hidden" name="activeTab" value="" />

    </form>

</div>

<div class="htmx-indicator tw:ml-m tw:mr-m tw:pt-l" role="status">
    <x-globals::feedback.skeleton type="project" count="5" includeHeadline="false"/>
</div>

