@php use Leantime\Domain\Auth\Models\Roles; @endphp

@dispatchEvent('beforeHeadMenu')

<ul class="headmenu pull-right" hx-boost="true" hx-indicator="#global-loader">
    @dispatchEvent('insideHeadMenu')

    @include('timesheets::includes.stopwatch', [
               'progressSteps' => $onTheClock
           ])
    @if ($login::userIsAtLeast("admin"))
        <li class="appsLink">
            <a href="{{ BASE_URL }}/plugins/marketplace" data-tippy-content="{{ __('menu.leantime_apps_tooltip') }}"><span class="fa fa-puzzle-piece"></span></a>
        </li>
    @endif
    <li class="notificationDropdown">
        <a
            class="dropdown-toggle profileHandler newsDropDownHandler"
            hx-get="{{ BASE_URL }}/hx/notifications/news/get"
            hx-target="#newsDropdown"
            hx-indicator=".htmx-indicator"
            hx-trigger="click"
            data-toggle='dropdown'
            data-tippy-content='{{ __('popover.latest_updates') }}'
        >
            <span class="fa-solid fa-bolt-lightning"></span>
            <span hx-get="{{ BASE_URL }}/hx/notifications/news-badge/get" hx-trigger="load" hx-target="this"></span>

        </a>

        <div class='dropdown-menu p-m' id='newsDropdown'>
            <div class="htmx-indicator">
                <x-global::elements.loadingText type="text" count="3" includeHeadline="true" />
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
            @if($newNotificationCount>0)
                <span class='notificationCounter'>{{ $newNotificationCount }}</span>
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
                                <span class="notificationTitle">{!! $tpl->convertRelativePaths($notif['message']) !!}</span>
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
                            <a href="{{ $notif['url'] }}">
                                <span class="notificationProfileImage">
                                    <img src="{{ BASE_URL }}/api/users?profileImage={{ $notif['authorId'] }}"/>
                                </span>
                                <span class="notificationDate">
                                    {{ format($notif['datetime'])->date() }}
                                    {{ format($notif['datetime'])->time() }}
                                </span>
                                <span class="notificationTitle">{!! $tpl->convertRelativePaths($notif['message']) !!}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

            </div>
        </div>

    </li>

    <li>
        <div class="userloggedinfo">

            @include("auth::includes.loginInfo")

        </div>

        @dispatchEvent('afterUser')

    </li>

    @dispatchEvent('beforeHeadMenuClose')

</ul>

<ul class="headmenu" hx-boost="true" hx-indicator="#global-loader">

    @dispatchEvent('afterHeadMenuOpen')
    <li>
        @include('menu::includes.projectSelector')
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
            <a
                href="{{ BASE_URL }}/setting/editCompanySettings/"
                @if ($menuType == 'company')
                    class="active"
                @endif
                data-tippy-content="{{ __('popover.company') }}"
            >{!! __('menu.company') !!}</a>
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
