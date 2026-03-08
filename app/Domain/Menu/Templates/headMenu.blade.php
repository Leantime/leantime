@php use Leantime\Domain\Auth\Models\Roles; @endphp
@dispatchEvent('beforeHeadMenu')

<ul class="headmenu pull-right">
    @dispatchEvent('insideHeadMenu')

    @include('timesheets::partials.stopwatch', [
               'onTheClock' => $onTheClock
           ])

    @if ($login::userIsAtLeast("manager", true))
        <li class="dropdown notificationDropdown appsLink">
        <a href="javascript:void(0)"
            class="dropdown-toggle profileHandler newsDropDownHandler"
            data-toggle="dropdown"
            aria-label="{{ __('popover.latest_plugins') }}"
            aria-haspopup="true"
            aria-expanded="false"
            hx-get="{{ BASE_URL }}/plugins/marketplaceplugins/getLatest"
            hx-target="#pluginNewsDropdown"
            hx-indicator=".htmx-news-indicator"
            hx-trigger="click"
            preload="mouseover"
            data-tippy-content='{{ __('popover.latest_plugins') }}'
        >
            <x-globals::elements.icon name="extension" />

        </a>

        <div class='dropdown-menu' id='pluginNewsDropdown' aria-live="polite">
            <div class="htmx-indicator htmx-news-indicator" role="status">
                <x-globals::feedback.skeleton type="text" count="3" includeHeadline="true" />
            </div>
        </div>
    </li>
    @endif

    <li class="dropdown notificationDropdown">
        <a href="javascript:void(0)"
            class="dropdown-toggle profileHandler newsDropDownHandler"
            data-toggle="dropdown"
            aria-label="{{ __('popover.latest_updates') }}"
            aria-haspopup="true"
            aria-expanded="false"
            hx-get="{{ BASE_URL }}/notifications/news/get"
            hx-target="#newsDropdown"
            hx-indicator=".htmx-news-indicator"
            hx-trigger="click"
            preload="mouseover"
            data-tippy-content='{{ __('popover.latest_updates') }}'
        >
            <x-globals::elements.icon name="electric_bolt" />
            <span class="tw:inline-block" hx-get="{{ BASE_URL }}/notifications/news-badge/get" hx-trigger="load" hx-target="this"></span>

        </a>

        <div class='dropdown-menu' id='newsDropdown' aria-live="polite">
            <div class="htmx-indicator htmx-news-indicator" role="status">
                <x-globals::feedback.skeleton type="text" count="3" includeHeadline="true" />
            </div>
        </div>
    </li>

    <li class="dropdown notificationDropdown">
        <a href="javascript:void(0)"
            class="dropdown-toggle profileHandler notificationHandler"
            data-toggle="dropdown"
            aria-label="{{ __('popover.notifications') }}"
            aria-haspopup="true"
            aria-expanded="false"
            data-tippy-content='{{ __('popover.notifications') }}'
        >
            <x-globals::elements.icon name="notifications" />
            @if($newNotificationCount>0)
                <x-globals::elements.badge state="danger" scale="xs" class="notificationCounter">{{ $newNotificationCount }}</x-globals::elements.badge>
            @endif
        </a>

        <div class='dropdown-menu' id='notificationsDropdown'>

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
                        <p class="tw:p-2.5">{{ __('text.no_notifications') }}</p>
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
                            <a href="{{ $notif['url'] }}">
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

                <ul id='mentionsList' class='notificationViewLists tw:hidden'>
                    @if ($totalMentionCount === 0)
                        <p class="tw:p-2.5">{{ __('text.no_notifications') }}</p>
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
                            <a href="{{ $notif['url'] }}">
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
        </div>

    </li>

    <li class="dropdown userloggedinfo">
        <x-globals::actions.dropdown-menu
            variant="icon"
            leadingVisual="help"
            position="left"
            data-tippy-content='{{ __("popover.help") }}'
            aria-label="{{ __('popover.help') }}"
        >
            <x-globals::actions.dropdown-item :header="true">{{ __("headline.support") }}</x-globals::actions.dropdown-item>
            <x-globals::actions.dropdown-item href="#/help/showOnboardingDialog?route={{ $request->getCurrentRoute() }}">{!! __("menu.what_is_this_page") !!}</x-globals::actions.dropdown-item>
            <x-globals::actions.dropdown-item href="https://support.leantime.io" target="_blank">{!! __("menu.knowledge_base") !!}</x-globals::actions.dropdown-item>
            <x-globals::actions.dropdown-item href="https://github.com/Leantime/leantime/issues" target="_blank">{!! __("menu.submit_bug") !!}</x-globals::actions.dropdown-item>
            <li class="nav-header border">{!! __("menu.leantime_community") !!}</li>
            <x-globals::actions.dropdown-item href="https://discord.gg/4zMzJtAq9z" target="_blank">{!! __("menu.community") !!}</x-globals::actions.dropdown-item>
            <x-globals::actions.dropdown-item href="https://leantime.io/contact-us" target="_blank">{!! __("menu.contact_us") !!}</x-globals::actions.dropdown-item>
            <li class="nav-header border">System</li>
            <x-globals::actions.dropdown-item href="https://github.com/Leantime/leantime/releases" target="_blank">Leantime V{{ app(\Leantime\Core\Configuration\AppSettings::class)->appVersion }}</x-globals::actions.dropdown-item>
        </x-globals::actions.dropdown-menu>
    </li>

    <li>
        <div class="userloggedinfo">

            @include("auth::partials.loginInfo")

        </div>

        @dispatchEvent('afterUser')

    </li>

    @dispatchEvent('beforeHeadMenuClose')

</ul>

<ul class="headmenu work-modes tw:h-[50px] pull-left">

    @dispatchEvent('afterHeadMenuOpen')
    <li class="dropdown">
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
                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: leantime.appUrl + '/api/notifications',
                            data: {
                                id: 'all',
                                action: 'read'
                            }
                        }
                    ).done(function () {
                        jQuery(".notifcationViewLists li.new").removeClass("new");
                        jQuery(".notificationCounter").fadeOut();
                    })
                });

                jQuery('.notificationDropdown .dropdown-menu').on('click', function (e) {
                    e.stopPropagation();
                });

                jQuery('notificationsDropdown li').click(function () {
                    const url = jQuery(this).data('url');
                    const id = jQuery(this).data('id');

                    window.location.href = url;
                })
            });
        </script>
    @endpush
@endonce
