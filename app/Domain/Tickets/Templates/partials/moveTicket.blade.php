<x-global::content.modal.modal-buttons/>

<?php
$ticket = $tpl->get("ticket");
?>

<?php if ($ticket->type == "milestone") {?>
    <h4 class="widgettitle title-light"><?=$tpl->__("headline.move_milestone"); ?> </h4>
<?php } else { ?>
    <h4 class="widgettitle title-light"><?=$tpl->__("headline.move_todo"); ?> </h4>
<?php } ?>


    <x-global::content.modal.form action="{{ BASE_URL }}/tickets/moveTicket/<?=$ticket->id ?>">
        <h3>#<?=$ticket->id ?> - <?=$tpl->escape($ticket->headline); ?></h3> <br />
        <p>
            <?php if ($ticket->type == "milestone") {?>
                {{ __("text.moving_milestones") }}
            <?php } else { ?>
                {{ __("text.moving") }}
            <?php } ?>

            <br /><br />
        </p>

        <x-global::forms.select 
            id="projectSelector" 
            name="projectId" 
            labelText="{!! __('label.project') !!}"
        >
            @php
                $i = 0;
                $lastClient = '';
            @endphp
        
            @foreach ($tpl->get('projects') as $projectRow)
                @if ($lastClient != $projectRow['clientName'])
                    @if ($i > 0)
                        </optgroup>
                    @endif
                    @php
                        $lastClient = $projectRow['clientName'];
                    @endphp
                    <optgroup label="{{$projectRow['clientName'])}}">
                @endif
                <x-global::forms.select.select-option value="{{ $projectRow['id'] }}">
                    {{$projectRow['name']) }}
                </x-global::forms.select.select-option>
                @php $i++; @endphp
            @endforeach
        
            @if ($i > 0)
                </optgroup>
            @endif
        </x-global::forms.select>
        <br /><br /><br /><br />
        <br />
        <x-global::forms.button 
            type="submit"
            name="move"
            >
            {{ __('buttons.move') }}
        </x-global::forms.button>
            <a class="pull-right" href="javascript:void(0);" onclick="jQuery.nmTop().close();">{{ __("buttons.back") }}</a>
            <div class="clearall"></div>
        <br />
    </x-global::content.modal.form>


<script>
    <?php if (isset($_GET['closeModal'])) { ?>
        jQuery.nmTop().close();
    <?php } ?>

    jQuery(document).ready(function(){
        jQuery("#projectSelector").chosen();
    });
</script>



