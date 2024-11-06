<x-global::actions.dropdown labelText="<i class='fa fa-ellipsis-v' aria-hidden='true'></i>" contentRole="link" position="end" :align="$align ?? 'start'">
    <x-slot:menu>
        {{ $slot }}
    </x-slot:menu>
</x-global::actions.dropdown>
