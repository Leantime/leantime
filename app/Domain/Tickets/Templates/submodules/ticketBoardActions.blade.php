<div class="board-actions">
    @dispatchEvent('filters.afterLefthandSectionOpen')
    @include('tickets::submodules.ticketNewBtn')
    @include('tickets::submodules.ticketFilter')
    @dispatchEvent('filters.beforeLefthandSectionClose')
</div>
