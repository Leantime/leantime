<th {{ $attributes->merge(['class' => 'px-6 py-3 relative group']) }}>
    {{ $slot }}
    <span class="resize-handle absolute top-0 right-0 bottom-0 bg-base-300 w-[3px] cursor-col-resize opacity-0 group-hover:opacity-25 hover:!opacity-75"></span>
</th>
