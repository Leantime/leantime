@props([
    'buttonClass' => 'btn btn-primary',  // Default button class
    'menuClass' => 'dropdown-content menu bg-base-100 rounded-box z-50 w-52 p-2 shadow',  // Default menu class
])

<div {{ $attributes->merge(['class' => 'dropdown']) }}>
    <!-- Dropdown Button -->
    <label tabindex="0" class="{{ $buttonClass }}">
        {{ $button ?? 'Dropdown' }}
    </label>

    <!-- Dropdown Menu -->
    <ul tabindex="0" class="{{ $menuClass }}">
        {{ $menu }}
    </ul>
</div>



{{-- <x-dropdown 
    buttonClass="btn btn-secondary"
    menuClass="menu dropdown-content p-2 shadow-lg bg-base-200 rounded-box w-64"
    class="mt-4 mb-2"
>
    <!-- Slot for the Dropdown Button -->
    <x-slot:button>
        Options
    </x-slot:button>

    <!-- Slot for the Dropdown Menu -->
    <x-slot:menu>
        <li><a href="/profile" class="block px-4 py-2 hover:bg-gray-200">Profile</a></li>
        <li><a href="/settings" class="block px-4 py-2 hover:bg-gray-200">Settings</a></li>
        <li><a href="/logout" class="block px-4 py-2 hover:bg-gray-200">Logout</a></li>
    </x-slot:menu>
</x-dropdown> --}}
