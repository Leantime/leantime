@props([
    'milestone' => null,
    'noText' => false,
    'percentDone' => 0,
    'progressColor' => 'default'
 ])

<x-global::elements.card compact class="ticketBox fixed milestoneCard">
    <div class="row">
        <div class="col-md-8" style="margin-bottom:5px;">
            <strong><a href="<?=BASE_URL ?>/tickets/showKanban?milestone={{ $milestone->id }}" >{{ $milestone->headline  }}</a></strong>
        </div>
        <div class="col-md-4 align-right">

        </div>
    </div>
    @fragment('progress')

        @if($noText === false || $noText === null)
            <div class="row progress-wrapper">
                <div class="col-md-7 percent-label">
                    {{ __("label.due") }}
                    <?php echo format($milestone->editTo )->date($tpl->__("text.no_date_defined")); ?>
                </div>
                <div class="col-md-5 percent-label" style="text-align:right">
                    <?=sprintf($tpl->__("text.percent_complete"), format($percentDone)->decimal())?>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <x-global::feedback.progress :value="$percentDone"
                    :color="$progressColor !== 'default' ? '#' . $progressColor : null"
                    :label="sprintf(__('text.percent_complete'), format($percentDone)->decimal())"
                    :data-tippy-content="sprintf(__('text.percent_complete'), format($percentDone)->decimal())" />
            </div>
        </div>

        <script>
            // Idempotent init — a bare tippy('[data-tippy-content]') here re-instanced
            // every tooltip on the page each time a milestone card rendered (flashing
            // + duplicate header tooltips). initTooltips skips already-instanced els.
            window.leantime?.initTooltips?.();
        </script>
    @endfragment
</x-global::elements.card>
