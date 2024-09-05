<dialog class="modal overflow-y-auto scroll-py-5">
    <div class="modal-box w-fit overflow-y-visible max-w-none max-h-none mt-xl mb-xl top-1/4 max-sm:modal-bottom max-sm:w-full">
        <form method="dialog">
            <div class="absolute right-2 top-2">
                <button class="btn btn-sm btn-circle btn-ghost float-right">✕</button>
                @stack('modalbuttons')

            </div>
        </form>
        @yield('content')
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
    @stack('scripts')
</dialog>
