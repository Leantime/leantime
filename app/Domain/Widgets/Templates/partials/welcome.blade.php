@props([
    'includeTitle' => true,
    'randomImage' => '',
    'totalTickets' => 0,
    'projectCount' => 0,
    'closedTicketsCount' => 0,
    'ticketsInGoals' => 0,
    'doneTodayCount' => 0,
    'totalTodayCount' => 0,
])

<div class="welcome-widget">

    <div style="padding:0px 0px">
        <div style="font-size:18px; color:var(--main-titles-color); padding-bottom:15px; padding-top:8px">
            👋 {{ __('text.hi') }} {{ session()->get("userdata.name") }}

            <div class="tw:float-right">
                <x-global::button link="{{ BASE_URL }}/users/editOwn#theme" type="link" icon="fa-solid fa-palette" style="color:var(--main-titles-color); padding:0px; width:31px; line-height:31px; text-align: center;" data-tippy-content="{{ __('text.update_theme') }}"></x-global::button>

                <x-global::button link="#/widgets/widgetManager" type="link" icon="fa fa-fw fa-cogs" style="color:var(--main-titles-color); padding:0px; width:31px; line-height:31px; text-align: center;" data-tippy-content="{{ __('text.update_dashboard') }}">
                    @if($showSettingsIndicator)
                        <span class='new-indicator'></span>
                    @endif
                </x-global::button>
            </div>
        </div>

        <div class="tw:grid tw:grid-cols-2 tw:md:grid-cols-4 tw:gap-[10px]">

            <div class="bigNumberBox">
                <div class="bigNumberBoxInner">
                    <div class="bigNumberBoxNumber">⏱️ {{ $doneTodayCount }}/{{ $totalTodayCount }} </div>
                    <div class="bigNumberBoxText">{{ __("welcome_widget.timeboxed_completed") }}</div>
                </div>
            </div>

            <div class="bigNumberBox">
                <div class="bigNumberBoxInner">
                    <div class="bigNumberBoxNumber">🥳 {{ $closedTicketsCount }} </div>
                    <div class="bigNumberBoxText">{{ __("welcome_widget.tasks_completed") }}</div>
                </div>
            </div>

            <div class="bigNumberBox">
                <div class="bigNumberBoxInner">
                    <div class="bigNumberBoxNumber">📥 {{ $totalTickets }} </div>
                    <div class="bigNumberBoxText">{{ __("welcome_widget.tasks_left") }}</div>
                </div>
            </div>

            <div class="bigNumberBox">
                <div class="bigNumberBoxInner">
                    <div class="bigNumberBoxNumber">🎯 {{ $ticketsInGoals }} </div>
                    <div class="bigNumberBoxText">{{ __("welcome_widget.goals_contributing_to") }}</div>
                </div>
            </div>

        </div>
    </div>

    <div class="clear"></div>

    @dispatchEvent('afterWelcomeMessage')

    <div class="clear"></div>

</div>

@dispatchEvent('afterWelcomeMessageBox')
