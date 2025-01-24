@extends($layout)

@section('content')


    @include('tickets::includes.ticketHeader')

    <div class="maincontent">

        @include('tickets::includes.ticketBoardTabs')

        <div class="maincontentinner">

            <div class="row">
                <div class="col-md-4">
                    <?php
                    $tpl->dispatchTplEvent('filters.afterLefthandSectionOpen');
                    ?>

                    <div class="flex mb-2 gap-3">
                        @include('tickets::includes.ticketNewBtn')
                        @include('tickets::includes.ticketFilter')
                    </div>

                    <?php
                    $tpl->dispatchTplEvent('filters.beforeLefthandSectionClose');
                    ?>
                </div>

                <div class="col-md-4 center">

                </div>
                <div class="col-md-4">

                </div>
            </div>

            <div class="clearfix"></div>


            <div class="flex sticky top-[110px] justify-start z-[9]">

                @foreach ($allKanbanColumns as $key => $statusRow)
                    <div class="column">
                        <h4
                            class="flex justify-between items-center widgettitle title-primary title-border-{{ $statusRow['class'] }}">
                            <div>
                                <strong class="count">0</strong>
                                {{ $tpl->e($statusRow['name']) }}
                            </div>

                            @if ($login::userIsAtLeast($roles::$manager))
                                <!-- Determine Label Text for the Dropdown -->
                                @php
                                    $labelText = '<i class="fa fa-ellipsis-v" aria-hidden="true"></i>';
                                @endphp

                                <!-- Context Menu Component -->
                                <x-global::content.context-menu :label-text="$labelText" contentRole="link" position="bottom"
                                    align="end">
                                    <!-- Dropdown Items -->
                                    <x-global::actions.dropdown.item class="font-normal"
                                        href="#/setting/editBoxLabel?module=ticketlabels&label={{ $key }}">
                                        {!! __('headlines.edit_label') !!}
                                    </x-global::actions.dropdown.item>
                                    <x-global::actions.dropdown.item class="font-normal"
                                        href="{{ BASE_URL }}/projects/showProject/{{ session('currentProject') }}#todosettings">
                                        {!! __('links.add_remove_col') !!}
                                    </x-global::actions.dropdown.item>
                                </x-global::content.context-menu>
                            @endif

                        </h4>

                        <div class="">
                            <a href="javascript:void(0);" style="padding:10px; display:block; width:100%;"
                                id="ticket_new_link_{{ $key }}"
                                onclick="jQuery('#ticket_new_link_{{ $key }}').toggle('fast'); jQuery('#ticket_new_{{ $key }}').toggle('fast', function() { jQuery(this).find('input[name=headline]').focus(); });">
                                <i class="fas fa-plus-circle"></i> Add To-Do</a>

                            <div class="hideOnLoad" id="ticket_new_{{ $key }}"
                                style="padding-top:5px; padding-bottom:5px;">

                                <form hx-post="/hx/tickets/showKanban" hx-indicator="#save-indicator" hx-swap="none"
                                    class="mb-2 quickadd-ticket" data-key="{{ $key }}">
                                    <x-global::forms.text-input type="text" class="mb-2" name="headline"
                                        placeholder="Enter To-Do Title" title="{{ __('label.headline') }}" />

                                    <input type="hidden" name="milestone" value="{{ $searchCriteria['milestone'] }}" />
                                    <input type="hidden" name="status" value="{{ $key }}" />
                                    <input type="hidden" name="sprint" value="{{ session('currentSprint') }}" />


                                    <x-global::forms.button type="submit" scale="sm" name="quickadd">
                                        Save
                                    </x-global::forms.button>

                                    <x-global::forms.button tag="a" scale="sm" content-role="secondary"
                                        href="javascript:void(0);"
                                        onclick="jQuery('#ticket_new_{{ $key }}, #ticket_new_link_{{ $key }}').toggle('fast');">
                                        {{ __('links.cancel') }}
                                    </x-global::forms.button>
                                    <div id="save-indicator" class="htmx-indicator">
                                        <span class="loading loading-spinner"></span> Saving...
                                    </div>

                                </form>

                                <div class="clearfix"></div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            <x-tickets::kanban-board :allKanbanColumns="$allKanbanColumns" :searchCriteria="$searchCriteria" />

        </div>

    </div>

    <script type="module">
        import "@mix('/js/Domain/Tickets/Js/ticketsController.js')"

        jQuery(document).ready(function() {
            document.body.addEventListener('htmx:afterSettle', function() {
                @if ($login::userIsAtLeast($roles::$editor))
                    var ticketStatusList = [
                        @foreach ($tpl->get('allTicketStates') as $key => $statusRow)
                            '{{ $key }}',
                        @endforeach
                    ];
                    ticketsController.initTicketKanban(ticketStatusList);
                @else
                    leantime.authController.makeInputReadonly(".maincontentinner");
                @endif

                ticketsController.setUpKanbanColumns();



            });

            jQuery(document).on("htmx:afterRequest", ".quickadd-ticket", function() {
                let key = jQuery(this).data('key');
                htmx.trigger(`#ticketColumn_${key}`, 'reload');
                jQuery(this).find('input[name=headline]').val('');
            });


            jQuery("#modal-wrapper #main-page-modal").on('close', function() {
                jQuery('.ticketColumn').each(function() {
                    htmx.trigger(this, 'reload');
                });
            });
        });
    </script>
@endsection
