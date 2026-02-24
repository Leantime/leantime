@if ($login::userIsAtLeast($roles::$editor))
    <x-globals::elements.button-dropdown :label="$tpl->__('links.new_with_icon')" type="primary" class="pull-left" style="margin-right:5px;">
        <li><a href="#/tickets/newTicket">Add Todo</a></li>
        <li><a href="#/tickets/editMilestone">Add Milestone</a></li>
    </x-globals::elements.button-dropdown>
@endif

