<x-global::actions.dropdown
    content-role="tertiary"
    position="end"
    button-shape="circle"
    :align="$align ?? 'end'">
    <x-slot:labelText>
        <i class='fa fa-ellipsis-v' aria-hidden='true'></i>
    </x-slot:labelText>
    <x-slot:menu>
        {{ $slot }}
    </x-slot:menu>
</x-global::actions.dropdown>
