<x-global::actions.dropdown
    content-role='ghost'
    button-shape="circle"
    variant="card"
    data-tippy-content='{{ __('popover.notifications') }}'>
    <x-slot:labelText>
        <span class="fa-solid fa-bell"></span>
        @if($newNotificationCount>0)
            <span class='notificationCounter'>{{ $newNotificationCount }}</span>
        @endif
    </x-slot:labelText>
    <x-slot:card-content>
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
    </x-slot:card-content>
</x-global::actions.dropdown>

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
