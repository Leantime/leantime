@php
use Leantime\Domain\Auth\Models\Roles;
$isAdmin = in_array(session('userdata.role'), [Roles::$owner, Roles::$admin]);
$adminRouteTitles = [
'dashboard.adminHome' => 'Admin Dashboard',
'users.showAll' => 'User Management',
'users.editUser' => 'Edit User',
'users.newUser' => 'New User',
'clients.showAll' => 'Client Orgs',
'clients.newClient' => 'New Client',
'clients.showClient' => 'Client Details',
'oneonone.showTeam' => '1:1 Sessions',
'oneonone.showSession' => '1:1 Session',
'oneonone.newSession' => 'New 1:1 Session',
'users.editOwn' => 'My Profile',
'timesheets.showAll' => 'All Timesheets',
'projects.showAll' => 'All Projects',
'weekly-planning.showTeam' => 'Weekly Planning',
'weekly-planning.showBlockers'=> 'Team Blockers',
'weekly-planning.showCommitments' => 'Team Commitments',
'setting.editCompanySettings' => 'Company Settings',
];
@endphp
@dispatchEvent('beforeHeadMenu')

@if($isAdmin)
<div style="float:left; display:flex; align-items:center; height:50px; padding-left:20px; color:#fff; font-size:16px; font-weight:600; letter-spacing:.3px; opacity:.95;">
    {{ $adminRouteTitles[\Leantime\Core\Controller\Frontcontroller::getCurrentRoute()] ?? 'Admin' }}
</div>
@endif

<ul class="headmenu pull-right">
    @dispatchEvent('insideHeadMenu')

    @if(!$isAdmin && session('userdata.role') !== Roles::$commenter)
    @php
    $isDeveloper = session('userdata.role') === Roles::$editor;
    @endphp

    @if($isDeveloper)
    {{-- Developers get only the Notepad icon (no quick-add dropdown). --}}
    <li>
        <a
            href="javascript:void(0);"
            onclick="leantimeNotepad.open();"
            data-tippy-content="My Notepad">
            <span class="fa-solid fa-sticky-note"></span>
        </a>
    </li>
    @else
    {{-- TL / CM get the full +New quick-add dropdown. --}}
    <li class="notificationDropdown quickAddDropdown">
        <a
            href="javascript:void(0);"
            class="dropdown-toggle profileHandler"
            data-toggle="dropdown"
            data-tippy-content="{{ __('popover.quick_add') }}">
            <span class="fa-solid fa-plus"></span>
            <span class="tw-ml-xs tw-hidden md:tw-inline">{{ __('menu.quick_add') }}</span>
        </a>
        <ul class="dropdown-menu pull-right">
            <li class="nav-header">{{ __('menu.quick_add_header') }}</li>
            <li>
                <a href="#/tickets/newTicket">
                    <i class="fa fa-fw fa-thumb-tack"></i> {{ __('menu.quick_add_task') }}
                </a>
            </li>
            <li>
                <a href="{{ BASE_URL }}/wiki/show">
                    <i class="fa fa-fw fa-sticky-note"></i> {{ __('menu.quick_add_note') }}
                </a>
            </li>
            <li>
                <a href="{{ BASE_URL }}/files/browse">
                    <i class="fa fa-fw fa-file-arrow-up"></i> {{ __('menu.quick_add_file') }}
                </a>
            </li>
            @if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$manager, true))
            <li>
                <a href="{{ BASE_URL }}/projects/newProject">
                    <i class="fa fa-fw fa-suitcase"></i> {{ __('menu.quick_add_project') }}
                </a>
            </li>
            <li>
                <a href="#/users/newUser">
                    <i class="fa fa-fw fa-user-plus"></i> {{ __('menu.quick_add_invite') }}
                </a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    @include('timesheets::partials.stopwatch', [
    'onTheClock' => $onTheClock
    ])

    @endif {{-- end commenter hide --}}

    <li class="notificationDropdown">
        <a
            href='javascript:void(0);'
            class="dropdown-toggle profileHandler notificationHandler"
            data-toggle='dropdown'
            data-tippy-content='{{ __('popover.notifications') }}'>
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
                    onclick="toggleNotificationTabs('notifications')">Notification ({{ $totalNewNotifications }})</a>
                <a
                    href='javascript:void(0);'
                    class='notifcationTabs'
                    id="mentionsListLink"
                    onclick="toggleNotificationTabs('mentions')">Mentions ({{ $totalNewMentions }})</a>
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
                        @if ($notif['read']==0)
                        class='new'
                        @endif
                        data-url="{{ $notif['url'] }}"
                        data-id="{{ $notif['id'] }}">
                        <a href="{{ $notif['url'] }}">
                            <span class="notificationProfileImage">
                                <img src="{{ BASE_URL }}/api/users?profileImage={{ $notif['authorId'] }}" />
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
                        @if ($notif['read']==0)
                        class='new'
                        @endif
                        data-url="{{ $notif['url'] }}"
                        data-id="{{ $notif['id'] }}">
                        <a href="{{ $notif['url'] }}">
                            <span class="notificationProfileImage">
                                <img src="{{ BASE_URL }}/api/users?profileImage={{ $notif['authorId'] }}" />
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

    @if(!$isAdmin)
    <li class="userloggedinfo">
        <a
            href='javascript:void(0);'
            class="dropdown-toggle"
            data-toggle='dropdown'
            data-tippy-content='{{ __('popover.help') }}'>
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
    @endif {{-- end !isAdmin help --}}

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

    {{-- TL (teamlead) and CM (manager) get their own dashboard with project list + company actions,
         so the Projects selector and Company tab are redundant for them. Hide entirely. --}}
    @php
    $hideWorkModes = in_array(session('userdata.role'), [Roles::$owner, Roles::$admin, Roles::$teamlead, Roles::$manager])
    || session('userdata.role') === Roles::$commenter;
    @endphp

    {{-- Home button + project switcher — shown for ALL roles when inside a project --}}
    @if($menuType === 'project')
    <li style="display:flex; align-items:center; height:50px; padding-left:8px;">
        <a href="{{ BASE_URL }}/dashboard/home"
            style="display:inline-flex; align-items:center; gap:6px;
                   padding:5px 12px; border-radius:var(--element-radius);
                   background:rgba(255,255,255,.1); color:#fff;
                   text-decoration:none; font-size:13px; font-weight:600;
                   transition:background .15s; white-space:nowrap;"
            onmouseover="this.style.background='rgba(255,255,255,.2)'"
            onmouseout="this.style.background='rgba(255,255,255,.1)'"
            data-tippy-content="Back to Home">
            <i class="fa fa-home"></i>
            <span class="tw-hidden md:tw-inline">Home</span>
        </a>
    </li>

    @if(!empty($headMenuAllProjects))
    <li style="display:flex; align-items:center; height:50px; padding-left:4px;" class="notificationDropdown">
        <a href="javascript:void(0);"
            class="dropdown-toggle"
            data-toggle="dropdown"
            style="display:inline-flex; align-items:center; gap:6px;
                   padding:5px 12px; border-radius:var(--element-radius);
                   background:rgba(255,255,255,.1); color:#fff;
                   text-decoration:none; font-size:13px; font-weight:600;
                   max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
                   transition:background .15s;"
            onmouseover="this.style.background='rgba(255,255,255,.2)'"
            onmouseout="this.style.background='rgba(255,255,255,.1)'"
            data-tippy-content="Switch Project">
            <i class="fa fa-layer-group"></i>
            <span class="tw-hidden md:tw-inline" style="overflow:hidden; text-overflow:ellipsis; max-width:130px;">
                {{ !empty($headMenuCurrentProject['name']) ? $headMenuCurrentProject['name'] : 'Switch Project' }}
            </span>
            <i class="fa fa-caret-down" style="font-size:11px; opacity:.7; flex-shrink:0;"></i>
        </a>
        <ul class="dropdown-menu" style="min-width:220px; max-height:360px; overflow-y:auto;">
            <li class="nav-header" style="padding:8px 14px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; opacity:.6;">
                <i class="fa fa-layer-group" style="margin-right:5px;"></i> Switch Project
            </li>
            @foreach($headMenuAllProjects as $proj)
            <li>
                <a href="{{ BASE_URL }}/projects/changeCurrentProject/{{ $proj['id'] }}"
                    style="display:flex; align-items:center; gap:8px; padding:7px 14px; font-size:13px;
                           {{ (!empty($headMenuCurrentProject['id']) && (int)$headMenuCurrentProject['id'] === (int)$proj['id']) ? 'font-weight:700; color:var(--accent1);' : '' }}">
                    @if(!empty($headMenuCurrentProject['id']) && (int)$headMenuCurrentProject['id'] === (int)$proj['id'])
                    <i class="fa fa-check" style="color:var(--accent1); width:12px; flex-shrink:0;"></i>
                    @else
                    <i class="fa fa-fw" style="width:12px; flex-shrink:0;"></i>
                    @endif
                    <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $proj['name'] }}</span>
                </a>
            </li>
            @endforeach
        </ul>
    </li>
    @endif
    @endif {{-- end project context --}}

    @if(! $hideWorkModes && $menuType !== 'project')
    @if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$teamlead, true))
    <li>
        @if($login::userHasRole("manager"))
        <a
            href="{{ BASE_URL }}/projects/showAll/"
            @if ($menuType=='company' )
            class="active"
            @endif
            data-tippy-content="{{ __('popover.company') }}">{!! __('menu.company') !!}</a>
        @elseif($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$admin, true))
        <a
            href="{{ BASE_URL }}/setting/editCompanySettings/"
            @if ($menuType=='company' )
            class="active"
            @endif
            data-tippy-content="{{ __('popover.company') }}">{!! __('menu.company') !!}</a>
        @else
        {{-- Team leads land on their weekly team view --}}
        <a
            href="{{ BASE_URL }}/weekly-planning/showTeam"
            @if ($menuType=='company' )
            class="active"
            @endif
            data-tippy-content="{{ __('popover.company') }}">{!! __('menu.company') !!}</a>
        @endif
    </li>
    @endif
    @endif {{-- end work-modes hide --}}

</ul>



@dispatchEvent('afterHeadMenu')

<style>
    #notepadPopupOverlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .45);
        z-index: 9999;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding-top: 70px;
        backdrop-filter: blur(2px);
    }

    #notepadPopup {
        width: 100%;
        max-width: 720px;
        max-height: calc(100vh - 100px);
        background: var(--primary-background, #fff);
        border-radius: var(--box-radius, 12px);
        box-shadow: var(--large-shadow, 0 12px 32px rgba(0, 0, 0, .25));
        display: flex;
        flex-direction: column;
        overflow: hidden;
        animation: notepadIn .15s ease-out;
    }

    @keyframes notepadIn {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .notepad-popup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 22px;
        border-bottom: 1px solid var(--main-border-color, #e5e5e5);
        background: var(--secondary-background);
    }

    .notepad-icon-badge {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--accent1);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .notepad-close-btn {
        background: none;
        border: none;
        font-size: 18px;
        color: var(--grey);
        cursor: pointer;
        padding: 6px 10px;
        border-radius: 6px;
    }

    .notepad-close-btn:hover {
        background: rgba(0, 0, 0, .06);
        color: var(--primary-font-color);
    }

    .notepad-popup-body {
        padding: 18px 22px;
        overflow-y: auto;
        flex: 1;
    }

    .notepad-day {
        background: var(--secondary-background);
        border: 1px solid var(--main-border-color, #e5e5e5);
        border-radius: var(--box-radius, 8px);
        padding: 14px 16px;
        margin-bottom: 14px;
    }

    .notepad-day-header {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--main-border-color, #e5e5e5);
    }

    .notepad-day-header .date {
        font-size: 15px;
        font-weight: 600;
        color: var(--primary-font-color);
    }

    .notepad-day-header .date small {
        color: var(--grey);
        font-weight: 400;
        margin-left: 8px;
        font-size: 12px;
    }

    .notepad-task {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 5px 4px;
        border-radius: 5px;
        transition: background .15s;
    }

    .notepad-task:hover {
        background: rgba(0, 0, 0, .03);
    }

    .notepad-task input[type="checkbox"] {
        width: 17px;
        height: 17px;
        cursor: pointer;
        flex-shrink: 0;
        accent-color: var(--accent1);
    }

    .notepad-task input[type="text"] {
        flex: 1;
        border: none;
        background: transparent;
        padding: 3px 5px;
        font-size: 14px;
        outline: none;
        color: var(--primary-font-color);
    }

    .notepad-task input[type="text"]:focus {
        background: rgba(0, 0, 0, .04);
        border-radius: 4px;
    }

    .notepad-task.done input[type="text"] {
        text-decoration: line-through;
        color: var(--grey);
    }

    .notepad-task .delete-btn {
        opacity: 0;
        background: none;
        border: none;
        color: var(--grey);
        cursor: pointer;
        padding: 3px 7px;
        transition: opacity .15s, color .15s;
    }

    .notepad-task:hover .delete-btn {
        opacity: 1;
    }

    .notepad-task .delete-btn:hover {
        color: #e74c3c;
    }

    .notepad-add-btn {
        margin-top: 8px;
        background: none;
        border: 1px dashed var(--main-border-color, #ddd);
        color: var(--grey);
        padding: 7px 12px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 13px;
        width: 100%;
        text-align: left;
        transition: border-color .15s, color .15s;
    }

    .notepad-add-btn:hover {
        border-color: var(--accent1);
        color: var(--accent1);
    }

    .notepad-new-input {
        width: 100%;
        border: 1px solid var(--accent1);
        background: var(--primary-background, #fff);
        padding: 7px 11px;
        border-radius: 5px;
        margin-top: 8px;
        font-size: 14px;
        outline: none;
        color: var(--primary-font-color);
    }
</style>

<script>
    window.leantimeNotepad = (function() {
        var overlay, body, debounceTimers = {},
            loaded = false;

        function init() {
            overlay = document.getElementById('notepadPopupOverlay');
            body = document.getElementById('notepadPopupBody');

            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) close();
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && overlay.style.display !== 'none') close();
            });

            // Delegate all task interactions inside the popup body
            body.addEventListener('input', function(e) {
                var input = e.target;
                if (!input.matches || !input.matches('.notepad-task input[type="text"]')) return;
                var taskId = input.dataset.taskId;
                var content = input.value;
                debounce('save-' + taskId, function() {
                    postForm('/hx/notepad/tasks/save', {
                        id: taskId,
                        content: content
                    });
                }, 500);
            });

            body.addEventListener('change', function(e) {
                var cb = e.target;
                if (!cb.matches || !cb.matches('.notepad-task input[type="checkbox"]')) return;
                var taskId = cb.dataset.taskId;
                var row = cb.closest('.notepad-task');
                if (row) row.classList.toggle('done', cb.checked);
                postForm('/hx/notepad/tasks/toggle', {
                    id: taskId,
                    done: cb.checked ? 1 : 0
                });
            });

            body.addEventListener('keydown', function(e) {
                if (e.key !== 'Enter') return;
                var input = e.target;
                if (!input.matches) return;

                if (input.matches('.notepad-new-input')) {
                    e.preventDefault();
                    var section = input.closest('.notepad-day');
                    var date = section.dataset.date;
                    var content = input.value.trim();
                    if (!content) {
                        input.remove();
                        showAddButton(section);
                        return;
                    }
                    addTask(section, date, content);
                } else if (input.matches('.notepad-task input[type="text"]')) {
                    e.preventDefault();
                    showNewInput(input.closest('.notepad-day'));
                }
            });

            body.addEventListener('click', function(e) {
                var btn = e.target.closest('.notepad-add-btn');
                if (btn) {
                    showNewInput(btn.closest('.notepad-day'));
                    return;
                }
                var del = e.target.closest('.notepad-task .delete-btn');
                if (del) {
                    e.preventDefault();
                    var section = del.closest('.notepad-day');
                    deleteTask(section, del.dataset.taskId, section.dataset.date);
                }
            });
        }

        function open() {
            if (!overlay) init();
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (!loaded) load();
        }

        function close() {
            if (!overlay) return;
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }

        function load() {
            fetch(leantime.appUrl + '/hx/notepad/tasks/load', {
                credentials: 'include',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'HX-Request': 'true'
                }
            }).then(function(r) {
                return r.text();
            }).then(function(html) {
                body.innerHTML = html;
                loaded = true;
            });
        }

        function postForm(path, data) {
            var fd = new FormData();
            Object.keys(data).forEach(function(k) {
                fd.append(k, data[k]);
            });
            return fetch(leantime.appUrl + path, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'HX-Request': 'true'
                },
                body: fd
            });
        }

        function showNewInput(section) {
            var existing = section.querySelector('.notepad-new-input');
            if (existing) {
                existing.focus();
                return;
            }
            var input = document.createElement('input');
            input.type = 'text';
            input.className = 'notepad-new-input';
            input.placeholder = 'New task… (Enter to save, Esc to cancel)';
            var addBtn = section.querySelector('.notepad-add-btn');
            if (addBtn) addBtn.style.display = 'none';
            section.appendChild(input);
            input.focus();
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    input.remove();
                    showAddButton(section);
                }
            });
            input.addEventListener('blur', function() {
                if (!input.value.trim()) {
                    input.remove();
                    showAddButton(section);
                }
            });
        }

        function showAddButton(section) {
            var addBtn = section.querySelector('.notepad-add-btn');
            if (addBtn) addBtn.style.display = '';
        }

        function addTask(section, date, content) {
            postForm('/hx/notepad/tasks/add', {
                    taskDate: date,
                    content: content
                })
                .then(function(r) {
                    return r.text();
                })
                .then(function(html) {
                    section.innerHTML = html;
                });
        }

        function deleteTask(section, taskId, date) {
            postForm('/hx/notepad/tasks/delete', {
                    id: taskId,
                    taskDate: date
                })
                .then(function(r) {
                    return r.text();
                })
                .then(function(html) {
                    section.innerHTML = html;
                });
        }

        function debounce(id, fn, ms) {
            clearTimeout(debounceTimers[id]);
            debounceTimers[id] = setTimeout(fn, ms);
        }

        return {
            open: open,
            close: close
        };
    })();
</script>
{{-- ====================== END PERSONAL NOTEPAD POPUP ====================== --}}

@once
@push('scripts')
<script>
    function toggleNotificationTabs(active) {
        jQuery(".notifcationTabs").removeClass("active");
        jQuery('#' + active + 'ListLink').addClass("active");
        jQuery('.notifcationViewLists').hide();
        jQuery('#' + active + 'List').show();
    }

    jQuery(document).ready(function() {
        jQuery('.notificationHandler').on('click', function() {
            jQuery.ajax({
                type: 'PATCH',
                url: leantime.appUrl + '/api/notifications',
                data: {
                    id: 'all',
                    action: 'read'
                }
            }).done(function() {
                jQuery(".notifcationViewLists li.new").removeClass("new");
                jQuery(".notificationCounter").fadeOut();
            })
        });

        jQuery('.notificationDropdown .dropdown-menu').on('click', function(e) {
            e.stopPropagation();
        });

        jQuery('notificationsDropdown li').click(function() {
            const url = jQuery(this).data('url');
            const id = jQuery(this).data('id');

            window.location.href = url;
        })
    });
</script>
@endpush
@endonce