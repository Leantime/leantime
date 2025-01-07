<x-global::actions.dropdown.item 
    href="javascript:void(0);" 
    :data-label="$name"
    :data-value="$projectId . '_' . $id . '_' . $tags"
    :id="'ticketMilestoneChange' . $projectId . $id"
    :style="'background-color:' . $tags"
    {{-- hx-swap-oob="beforeend" class="chip-list"    --}}
>
    {{$name}}
</x-global::actions.dropdown.item>