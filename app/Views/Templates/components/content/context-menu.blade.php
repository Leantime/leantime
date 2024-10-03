<x-global::actions.dropdown label-text="<i class='fa fa-ellipsis-v' aria-hidden='true'></i>" contentRole="link" position="bottom" :align="$align ?? 'start'">
    <x-slot:menu>
        {{ $slot }}
    </x-slot:menu>
</x-global::actions.dropdown>
