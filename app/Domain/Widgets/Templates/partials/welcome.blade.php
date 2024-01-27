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

<div class="">

    <div style="padding:10px 0px">

        <div class="center">
            <span style="font-size:44px; color:var(--main-titles-color);">
                @php
                    $date = new DateTime();
                    if(!empty($_SESSION['usersettings.timezone'])){
                        $date->setTimezone(new DateTimeZone($_SESSION['usersettings.timezone']));
                    }
                    $date = $date->format(__("language.timeformat"));
                @endphp

                {{ $date }}
            </span><br />
            <span style="font-size:24px; color:var(--main-titles-color);">
                {{ __("welcome_widget.hi") }} {{ $currentUser['firstname'] }}
            </span><br />
            @dispatchEvent('afterGreeting')
            <br />
        </div>

        <div class="tw-flex tw-gap-x-[10px]">

            <div class="bigNumberBox tw-flex-1 tw-flex-grow">
                <div class="bigNumberBoxInner">
                    <div class="bigNumberBoxNumber">⏱️ {{ $doneTodayCount }}/{{ $totalTodayCount }} </div>
                    <div class="bigNumberBoxText">{{ __("welcome_widget.timeboxed_completed") }}</div>
                </div>
            </div>

            <div class="bigNumberBox tw-flex-1 tw-flex-grow">
                <div class="bigNumberBoxInner">
                    <div class="bigNumberBoxNumber">🥳 {{ $closedTicketsCount }} </div>
                    <div class="bigNumberBoxText">{{ __("welcome_widget.tasks_completed") }}</div>
                </div>
            </div>

            <div class="bigNumberBox tw-flex-1 tw-flex-grow ">

                <div class="bigNumberBoxInner">
                    <div class="bigNumberBoxNumber">📥 {{ $totalTickets }} </div>
                    <div class="bigNumberBoxText">{{ __("welcome_widget.tasks_left") }}</div>
                </div>
            </div>

            <div class="bigNumberBox tw-flex-1 tw-flex-grow">

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
