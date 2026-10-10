@php use Leantime\Domain\Auth\Models\Roles; @endphp
@dispatchEvent('beforeHeadMenu')

<ul class="headmenu pull-right">
    <x-search::searchBar />

    @dispatchEvent('insideHeadMenu')

    @include('timesheets::partials.stopwatch', [
               'onTheClock' => $onTheClock
           ])

    @if ($login::userIsAtLeast("manager", true))
        <x-global::actions.dropdown variant="panel" as="li" menu-class="tw-p-m tw-h-screen tw-overflow-y-auto" menu-id="pluginNewsDropdown" class="notificationDropdown appsLink" keep-open>
            <x-slot:trigger class="profileHandler newsDropDownHandler" hx-get="{{ BASE_URL }}/plugins/marketplaceplugins/getLatest" hx-target="#pluginNewsDropdown" hx-indicator=".htmx-news-indicator" hx-trigger="click" preload="mouseover" data-tippy-content="{{ __('popover.latest_plugins') }}"><i class="fa-solid fa-puzzle-piece"></i></x-slot:trigger>
            <div class="htmx-indicator htmx-news-indicator">
                <x-global::loadingText type="text" count="3" includeHeadline="true" />
            </div>

        </x-global::actions.dropdown>
    @endif

    <x-global::actions.dropdown variant="panel" as="li" menu-class="tw-p-m tw-h-screen tw-overflow-y-auto" menu-id="newsDropdown" class="notificationDropdown" keep-open>
        <x-slot:trigger class="profileHandler newsDropDownHandler" hx-get="{{ BASE_URL }}/notifications/news/get" hx-target="#newsDropdown" hx-indicator=".htmx-news-indicator" hx-trigger="click" preload="mouseover" data-tippy-content="{{ __('popover.latest_updates') }}">
            <span class="fa-solid fa-bolt-lightning"></span>
            <span hx-get="{{ BASE_URL }}/notifications/news-badge/get" hx-trigger="load" hx-target="this"></span>
        </x-slot:trigger>
        <div class="htmx-indicator htmx-news-indicator">
            <x-global::loadingText type="text" count="3" includeHeadline="true" />
        </div>

    </x-global::actions.dropdown>

    <x-global::actions.dropdown variant="panel" as="li" menu-id="notificationsDropdown" class="notificationDropdown" keep-open>
        <x-slot:trigger class="profileHandler notificationHandler" data-tippy-content="{{ __('popover.notifications') }}">
            <span class="fa-solid fa-bell"></span>
            @if($newNotificationCount>0)
                <span class='notificationCounter'>{{ $newNotificationCount }}</span>
            @endif
        </x-slot:trigger>
        <div class='dropdownTabs'>
            <a
                href='javascript:void(0);'
                class='notifcationTabs active'
                id="notificationsListLink"
                onclick="toggleNotificationTabs('notifications')"
            >Notification ({{ $totalNewNotifications }})</a>
            <a
                href='javascript:void(0);'
                class='notifcationTabs'
                id="mentionsListLink"
                onclick="toggleNotificationTabs('mentions')"
            >Mentions ({{ $totalNewMentions }})</a>
        </div>

        <div class="scroll-wrapper">

            <ul id='notificationsList' class='notifcationViewLists'>
                @if ($totalNotificationCount === 0)
                    <p style='padding: 10px'>{{ __('text.no_notifications') }}</p>
                @endif

                @foreach ($notifications as $notif)
                    @if ($notif['type'] == 'mention')
                        @continue
                    @endif

                    <li
                        @if ($notif['read'] == 0)
                            class='new'
                        @endif
                        data-url="{{ $notif['url'] }}"
                        data-id="{{ $notif['id'] }}"
                    >
                        <a href="{{ $notif['url'] }}" data-notification-type="{{ $notif['type'] ?? '' }}" data-notification-module="{{ $notif['module'] ?? '' }}">
                            <span class="notificationProfileImage">
                                <img src="{{ BASE_URL }}/api/users?profileImage={{ $notif['authorId'] }}"/>
                            </span>
                            <span class="notificationDate">
                                {{ format($notif['datetime'])->date() }}
                                {{ format($notif['datetime'])->time() }}
                            </span>
                            <span class="notificationTitle">{!! strip_tags($tpl->convertRelativePaths($notif['message'])) !!}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <ul id='mentionsList' style='display:none;' class='notificationViewLists'>
                @if ($totalMentionCount === 0)
                    <p style="padding: 10px">{{ __('text.no_notifications') }}</p>
                @endif

                @foreach ($notifications as $notif)
                    @if ($notif['type'] != 'mention')
                        @continue
                    @endif

                    <li
                        @if ($notif['read'] == 0)
                            class='new'
                        @endif
                        data-url="{{ $notif['url'] }}"
                        data-id="{{ $notif['id'] }}"
                    >
                        <a href="{{ $notif['url'] }}" data-notification-type="{{ $notif['type'] ?? '' }}" data-notification-module="{{ $notif['module'] ?? '' }}">
                            <span class="notificationProfileImage">
                                <img src="{{ BASE_URL }}/api/users?profileImage={{ $notif['authorId'] }}"/>
                            </span>
                            <span class="notificationDate">
                                {{ format($notif['datetime'])->date() }}
                                {{ format($notif['datetime'])->time() }}
                            </span>
                            <span class="notificationTitle">{!! strip_tags($tpl->convertRelativePaths($notif['message'])) !!}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

        </div>

    </x-global::actions.dropdown>

    <x-global::actions.dropdown variant="panel" as="li" menu-as="ul" menu-class="pull-right" class="userloggedinfo">
        <x-slot:trigger data-tippy-content="{{ __('popover.help') }}"><span class="fa-solid fa-question-circle"></span></x-slot:trigger>
        <li class="nav-header">
            {{ __("headline.support") }}
        </li>
        <li>
            <a href='#/help/showOnboardingDialog?route={{ $request->getCurrentRoute() }}'>
            {!! __("menu.what_is_this_page") !!}
            </a>
        </li>
        <li>
            <a href='https://support.leantime.io' target="_blank">
                {!! __("menu.knowledge_base") !!}
                </a>
        </li>
        <li>
            <a href='https://github.com/Leantime/leantime/issues' target="_blank">
                {!! __("menu.submit_bug") !!}
            </a>
        </li>
        <li class="nav-header border">{!! __("menu.leantime_community") !!}</li>
        <li>
            <a href='https://discord.gg/4zMzJtAq9z' target="_blank">
                {!! __("menu.community") !!}
            </a>
        </li>
        <li>
            <a href='https://leantime.io/contact-us' target="_blank">
                {!! __("menu.contact_us") !!}
            </a>
        </li>
        <li class="nav-header border">{{ __('label.system', 'System') }}</li>
        <li><a href="https://github.com/Leantime/leantime/releases" target="_blank">Leantime V{{ app(\Leantime\Core\Configuration\AppSettings::class)->appVersion }}</a></li>

    </x-global::actions.dropdown>

    <li>
        <div class="userloggedinfo">

            @include("auth::partials.loginInfo")

        </div>

        @dispatchEvent('afterUser')

    </li>

    @dispatchEvent('beforeHeadMenuClose')

</ul>

<ul class="headmenu work-modes" style="height: 50px; float: left;">

    @dispatchEvent('afterHeadMenuOpen')
    <li>
        @include('menu::projectSelector')
    </li>
    <li>
        <a
            href="{{ BASE_URL }}/dashboard/home"
            @if ($menuType == 'personal')
                class="active"
            @endif
            data-tippy-content="{{ __('popover.my_work') }}"
        >{!! __('menu.my_work') !!}</a>
    </li>
    @if ($login::userIsAtLeast("manager", true))
        <li>
            @if($login::userHasRole("manager"))
                <a
                    href="{{ BASE_URL }}/projects/showAll/"
                    @if ($menuType == 'company')
                        class="active"
                    @endif
                    data-tippy-content="{{ __('popover.company') }}"
                >{!! __('menu.company') !!}</a>
            @else
            <a
                href="{{ BASE_URL }}/setting/editCompanySettings/"
                @if ($menuType == 'company')
                    class="active"
                @endif
                data-tippy-content="{{ __('popover.company') }}"
            >{!! __('menu.company') !!}</a>
            @endif
        </li>
    @endif

</ul>



@dispatchEvent('afterHeadMenu')

@once
    @push('scripts')
        <script>
            function toggleNotificationTabs(active) {
                jQuery(".notifcationTabs").removeClass("active");
                jQuery('#' + active + 'ListLink').addClass("active");
                jQuery('.notifcationViewLists').hide();
                jQuery('#' + active + 'List').show();
            }

            jQuery(document).ready(function () {
                jQuery('.notificationHandler').on('click', function () {
                    leantime.rpc('Notifications.Notifications.markRead', { id: 'all' })
                        .then(function () {
                            jQuery(".notifcationViewLists li.new").removeClass("new");
                            jQuery(".notificationCounter").fadeOut();
                        })
                        .catch(function (e) { console.error('Could not mark notifications read', e); });
                });

                jQuery('#notificationsDropdown li').click(function () {
                    const url = jQuery(this).data('url');
                    const id = jQuery(this).data('id');

                    window.location.href = url;
                })
            });
        </script>
    @endpush
@endonce
