<x-global::actions.dropdown contentRole="ghost" position="end" :align="$align ?? 'start'">
    <x-slot:labelText>
        <i class='fa fa-ellipsis-v' aria-hidden='true'></i>
    </x-slot:labelText>
    <x-slot:menu>
        {{ $slot }}
    </x-slot:menu>
</x-global::actions.dropdown>
