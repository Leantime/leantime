@props([
    'milestone' => null,
    'noText' => false,
    'percentDone' => 0,
    'progressColor' => 'default'
 ])

<div class="ticketBox fixed">
    <div style="margin-bottom:5px;">
        <strong><a href="<?=BASE_URL ?>/tickets/showKanban?milestone={{ $milestone->id }}" >{{ $milestone->headline  }}</a></strong>
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

        <x-global::progress
            :value="$percentDone"
            :customColor="$progressColor !== 'default' ? '#'.$progressColor : null"
            :showLabel="false"
        />

        <script>
            tippy('[data-tippy-content]');
        </script>
    @endfragment
</div>
