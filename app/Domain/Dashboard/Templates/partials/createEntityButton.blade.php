@if ($login::userIsAtLeast($roles::$editor))
    <x-global::actions.dropdown variant="button" class="pull-left" style="margin-right:5px;">
        <x-slot:trigger><?=$tpl->__("links.new_with_icon") ?> <span class="caret"></span></x-slot:trigger>
        <li><a href="#/tickets/newTicket">{{ __('links.add_todo_no_icon', 'Add To-Do') }}</a></li>
        <li><a href="#/tickets/editMilestone">{{ __('label.addMilestone', 'Add Milestone') }}</a></li>


    </x-global::actions.dropdown>
@endif

