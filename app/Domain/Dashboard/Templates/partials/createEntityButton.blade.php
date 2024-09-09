@if ($login::userIsAtLeast($roles::$editor))
    <div class="btn-group pull-left" style="margin-right:5px;">
        <button class="btn btn-primary dropdown-toggle" type="button"
            data-toggle="dropdown"><?= $tpl->__('links.new_with_icon') ?> <span class="caret"></span></button>
        <x-global::actions.dropdown label-text="Options" contentRole="link" position="bottom" align="start"
            class="dropdown-menu">

            <x-slot:menu>
                <!-- Add Todo Menu Item -->
                <x-global::actions.dropdown.item variant="link" href="#/tickets/newTicket">
                    Add Todo
                </x-global::actions.dropdown.item>

                <!-- Add Milestone Menu Item -->
                <x-global::actions.dropdown.item variant="link" href="#/tickets/editMilestone">
                    Add Milestone
                </x-global::actions.dropdown.item>
            </x-slot:menu>

        </x-global::actions.dropdown>

    </div>
@endif
