@props([
    'entityId'        => null,      // row ID — used for DOM IDs (userDropdownMenuLink{id})
    'assignedUserId'  => null,      // currently assigned user id
    'assignedName'    => '',        // display name (firstname only or full name)
    'users'           => [],        // array of ['id', 'firstname', 'lastname', 'profileId']
    'showNameLabel'   => true,      // show text label beside avatar
    'showArrowIcon'   => false,     // show arrow_drop_down icon after name
    'showUnassign'    => false,     // include "not assigned" unassign item at top
    'dropdownClasses' => '',        // extra modifier classes (dropRight, lastDropdown, etc.)
])
{{--
    User assignment dropdown component.

    JS coupling requirements (DO NOT change these IDs/attributes without a JS refactor):
      - Trigger link id:  userDropdownMenuLink{entityId}
      - Avatar span id:   userImage{entityId}
      - Name span id:     user{entityId}
      - Item id:          userStatusChange{entityId}{userId}
      - data-value:       {entityId}_{userId}_{profileId}
      - data-label:       full name string

    The `ticketDropdown userDropdown noBg` base classes are always present — these are required
    by leantime.ticketsController.initUserDropdown() and leantime.ideasController.initUserDropdown()
    and leantime.canvasController.initUserDropdown() event delegation selectors.
--}}
<div class="dropdown ticketDropdown userDropdown noBg {{ $dropdownClasses }}">
    <a class="dropdown-toggle"
       href="javascript:void(0);"
       role="button"
       id="userDropdownMenuLink{{ $entityId }}"
       data-toggle="dropdown"
       aria-haspopup="true"
       aria-expanded="false"
       title="{{ $assignedName != '' ? e($assignedName) : __('dropdown.not_assigned') }}">
        <span class="text">
            @if ($assignedUserId != '' && $assignedUserId != null && $assignedUserId != 0)
                <span id="userImage{{ $entityId }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $assignedUserId }}" width="25" style="vertical-align: middle;" /></span>
            @else
                <span id="userImage{{ $entityId }}"><img src="{{ BASE_URL }}/api/users?profileImage=false" width="25" style="vertical-align: middle;" /></span>
            @endif
            @if ($showNameLabel)
                @if ($assignedName != '')
                    <span id="user{{ $entityId }}" class="user-name-label">{{ e($assignedName) }}</span>
                @else
                    <span id="user{{ $entityId }}" class="user-name-label">{{ __('dropdown.not_assigned') }}</span>
                @endif
            @else
                <span id="user{{ $entityId }}"></span>
            @endif
        </span>
        @if ($showArrowIcon)
            &nbsp;<x-globals::elements.icon name="arrow_drop_down" />
        @endif
    </a>
    <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink{{ $entityId }}">
        <li class="nav-header border">{{ __('dropdown.choose_user') }}</li>
        @if ($showUnassign)
            <li class="dropdown-item">
                <a href="javascript:void(0);"
                   onclick="document.activeElement.blur();"
                   data-label="{{ __('label.not_assigned_to_user') }}"
                   data-value="{{ $entityId }}_0_0"
                   id="userStatusChange{{ $entityId }}0">{{ __('label.not_assigned_to_user') }}</a>
            </li>
        @endif
        @foreach ($users as $user)
            <li class="dropdown-item">
                <a href="javascript:void(0);"
                   onclick="document.activeElement.blur();"
                   data-label="{{ sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) }}"
                   data-value="{{ $entityId }}_{{ $user['id'] }}_{{ $user['profileId'] }}"
                   id="userStatusChange{{ $entityId }}{{ $user['id'] }}">
                    <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}" width="25" style="vertical-align: middle; margin-right:5px;" />{{ sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
