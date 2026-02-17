@props([
    'milestone' => null,
    'noText' => false,
    'percentDone' => 0,
    'progressColor' => 'default'
 ])

<div class="ticketBox fixed">
    <div class="tw:grid tw:grid-cols-12">
        <div class="tw:col-span-8" style="margin-bottom:5px;">
            <strong><a href="<?=BASE_URL ?>/tickets/showKanban?milestone={{ $milestone->id }}" >{{ $milestone->headline  }}</a></strong>
        </div>
        <div class="tw:col-span-4 tw:text-right">

        </div>
    </div>
    @fragment('progress')

        @if($noText === false || $noText === null)
            <div class="tw:flex tw:justify-between progress-wrapper">
                <div class="percent-label">
                    {{ __("label.due") }}
                    <?php echo format($milestone->editTo )->date($tpl->__("text.no_date_defined")); ?>
                </div>
                <div class="percent-label" style="text-align:right">
                    <?=sprintf($tpl->__("text.percent_complete"), format($percentDone)->decimal())?>
                </div>
            </div>
        @endif
                <div class="progress" data-tippy-content="<?=sprintf($tpl->__("text.percent_complete"), format($percentDone)->decimal())?>">
                    <div class="progress-bar progress-bar-success"
                         role="progressbar"
                         aria-valuenow="{{ $percentDone }}"
                         aria-valuemin="0" aria-valuemax="100"
                         style="width: {{ $percentDone }}%; {{ $progressColor !== 'default' ? ' background: #'.$progressColor.'; ' : '' }}">
                        <span class="sr-only">{{ sprintf($tpl->__("text.percent_complete"), format($percentDone)->decimal()) }}</span>
                    </div>
                </div>

        <script>
            tippy('[data-tippy-content]');
        </script>
    @endfragment
</div>
