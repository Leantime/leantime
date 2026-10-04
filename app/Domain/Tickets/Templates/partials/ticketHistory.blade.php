{{--
    Change history of a ticket (History tab). Rendered by Hxcontrollers\TicketHistory.

    @var bool $historyAvailable  false when the ticket is missing or not visible to the user
    @var \Leantime\Domain\Tickets\Models\TicketHistoryEntry[] $historyEntries  newest first
    All values are user content and are escaped with {{ }}.
--}}
<div class="ticketHistory">
    @if (! $historyAvailable)
        <p class="tw-p-m">{{ __('text.ticket_history_not_available') }}</p>
    @elseif (empty($historyEntries))
        <p class="tw-p-m">{{ __('text.ticket_history_empty') }}</p>
    @else
        <ul class="tw-list-none tw-m-0 tw-p-0">
            @foreach ($historyEntries as $entry)
                @php
                    $changedAtRelative = '';
                    $changedAtAbsolute = trim(format($entry->dateModified)->date().' '.format($entry->dateModified)->time());
                    try {
                        $changedAtRelative = dtHelper()->parseDbDateTime($entry->dateModified)->setToUserTimezone()->diffForHumans();
                    } catch (\Throwable $e) {
                        $changedAtRelative = $changedAtAbsolute;
                    }
                @endphp
                <li class="tw-flex tw-gap-m tw-py-sm" style="border-bottom:1px solid var(--main-border-color);">
                    @if ($entry->userId)
                        <img src="{{ BASE_URL }}/api/users?profileImage={{ $entry->userId }}"
                             alt="" class="tw-rounded-full" style="width:32px; height:32px; flex-shrink:0;" />
                    @endif
                    <div class="tw-flex-1">
                        <div>
                            <strong>{{ $entry->userName }}</strong>
                            &middot;
                            <span title="{{ $changedAtAbsolute }}">{{ $changedAtRelative }}</span>
                        </div>
                        <div>
                            <span class="tw-font-semibold">{{ $entry->fieldLabel }}</span>:
                            @if ($entry->isDescriptionChange)
                                <em>{{ __('text.ticket_history_description_changed') }}</em>
                            @else
                                @if ($entry->oldValue !== null)
                                    <span style="text-decoration:line-through; opacity:0.7;">{{ $entry->oldValue }}</span>
                                    <i class="fa fa-arrow-right tw-mx-xs" aria-hidden="true"></i>
                                @endif
                                <span>{{ $entry->newValue ?? '' }}</span>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
