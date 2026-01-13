@php use Leantime\Domain\Auth\Models\Roles; @endphp
@dispatchEvent('beforeHeadMenu')

<ul class="headmenu pull-right">
    @dispatchEvent('insideHeadMenu')

    @include('timesheets::partials.stopwatch', [
               'progressSteps' => $onTheClock
           ])

    @if ($login::userIsAtLeast("manager", true))
        <li class="notificationDropdown appsLink">
        <a
            class="dropdown-toggle profileHandler newsDropDownHandler"
            hx-get="{{ BASE_URL }}/plugins/marketplaceplugins/getLatest"
            hx-target="#pluginNewsDropdown"
            hx-indicator=".htmx-news-indicator"
            hx-trigger="click"
            preload="mouseover"
            data-toggle='dropdown'
            data-tippy-content='{{ __('popover.latest_plugins') }}'
        >
            <i class="fa-solid fa-puzzle-piece"></i>

        </a>

        <div class='dropdown-menu tw-p-m tw-h-screen tw-overflow-y-auto' id='pluginNewsDropdown'>
            <div class="htmx-indicator htmx-news-indicator">
                <x-global::loadingText type="text" count="3" includeHeadline="true" />
            </div>
        </div>
    </li>
    @endif

    <li class="notificationDropdown">
        <a
            class="dropdown-toggle profileHandler newsDropDownHandler"
            hx-get="{{ BASE_URL }}/notifications/news/get"
            hx-target="#newsDropdown"
            hx-indicator=".htmx-news-indicator"
            hx-trigger="click"
            preload="mouseover"
            data-toggle='dropdown'
            data-tippy-content='{{ __('popover.latest_updates') }}'
        >
            <span class="fa-solid fa-bolt-lightning"></span>
            <span hx-get="{{ BASE_URL }}/notifications/news-badge/get" hx-trigger="load" hx-target="this"></span>

        </a>

        <div class='dropdown-menu tw-p-m tw-h-screen tw-overflow-y-auto' id='newsDropdown'>
            <div class="htmx-indicator htmx-news-indicator">
                <x-global::loadingText type="text" count="3" includeHeadline="true" />
            </div>
        </div>
    </li>

    <li class="notificationDropdown">
        <a
            href='javascript:void(0);'
            class="dropdown-toggle profileHandler notificationHandler"
            data-toggle='dropdown'
            data-tippy-content='{{ __('popover.notifications') }}'
        >
            <span class="fa-solid fa-bell"></span>
            @if($totalNewNotifications>0)
                <span class='notificationCounter'>{{ $totalNewNotifications }}</span>
            @endif
        </a>

        <div class='dropdown-menu' id='notificationsDropdown'>

            <div class="scroll-wrapper">

                <ul id='notificationsList' class='notificationViewLists'>
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

    <li class="notificationDropdown">
        <a
            href='javascript:void(0);'
            class="dropdown-toggle profileHandler mentionHandler"
            data-toggle='dropdown'
            data-tippy-content='Mentions'
        >
            <span class="fa-solid fa-at"></span>
            @if($totalNewMentions > 0)
                <span class='notificationCounter'>{{ $totalNewMentions }}</span>
            @endif
        </a>

        <div class='dropdown-menu' id='mentionsDropdown'>

            <div class="scroll-wrapper">

                <ul id='mentionsList' class='notificationViewLists'>
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

    <li class="userloggedinfo">
        <a
            href='javascript:void(0);'
            class="dropdown-toggle"
            data-toggle='dropdown'
            data-tippy-content='{{ __('popover.help') }}'
        >
            <span class="fa-solid fa-question-circle"></span>
        </a>
        <ul class="dropdown-menu pull-right">
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
            <li class="nav-header border">System</li>
            <li><a href="https://github.com/Leantime/leantime/releases" target="_blank">Leantime V{{ app(\Leantime\Core\Configuration\AppSettings::class)->appVersion }}</a></li>
        </ul>
    </li>

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
            href="{{ BASE_URL }}/timesheets/showMy"
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
                href="{{ BASE_URL }}/timesheets/showAll/"
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
                        jQuery("#notificationsDropdown .notificationViewLists li.new").removeClass("new");
                        jQuery("#notificationsDropdown .notificationCounter").fadeOut();
                    })
                });

                jQuery('.mentionHandler').on('click', function () {
                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: leantime.appUrl + '/api/notifications',
                            data: {
                                id: 'all',
                                action: 'read',
                                type: 'mention'
                            }
                        }
                    ).done(function () {
                        jQuery("#mentionsDropdown .notificationViewLists li.new").removeClass("new");
                        jQuery("#mentionsDropdown .notificationCounter").fadeOut();
                    })
                });

                jQuery('.notificationDropdown .dropdown-menu').on('click', function (e) {
                    e.stopPropagation();
                });

                jQuery('#notificationsDropdown li, #mentionsDropdown li').click(function () {
                    const url = jQuery(this).data('url');
                    const id = jQuery(this).data('id');

                    window.location.href = url;
                })
            });
        </script>
    @endpush
@endonce
