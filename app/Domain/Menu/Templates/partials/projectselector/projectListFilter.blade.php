<div class="py-2">

    <form hx-target="#mainProjectSelector" hx-swap="innerHTML" hx-trigger="change" hx-post="{{ BASE_URL }}/hx/menu/projectSelector/update-menu" hx-indicator=".project-loading-indicator,.htmx-loaded-content">

        <div class="flex flex-row justify-self-start gap-x-sm">
            <x-global::forms.select data-placeholder="" name="client">
                <x-slot:leading-visual>
                    <i class="fas fa-filter"></i>
                </x-slot:leading-visual>
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

            <x-global::forms.select data-placeholder="" name="groupBy">
                <x-slot:leading-visual>
                    <i class="fa-solid fa-diagram-project"></i>
                </x-slot:leading-visual>
                @foreach ($projectSelectGroupOptions as $key => $group)
                    <x-global::forms.select.select-option :value="$key" :selected="$projectSelectFilter['groupBy'] == $key">
                        {{ $group }}
                    </x-global::forms.select.select-option>
                @endforeach
            </x-global::forms.select>
        </div>
        <input type="hidden" name="activeTab" value="" />

    </form>

</div>

<div class="project-loading-indicator htmx-indicator ml-m mr-m pt-l">
    <x-global::elements.loadingText type="project" count="5" includeHeadline="false" />
</div>
