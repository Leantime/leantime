<x-global::content.modal.modal-buttons/>

<?php
$status = $tpl->get('status');
$values = $tpl->get('values');
$projects = $tpl->get('relations');
?>

<div style="min-width:700px;">

<h4 class="widgettitle title-light"><i class="fa fa-key"></i> {{ __("headlines.api_key") }}</h4>

    @displayNotification()

<x-global::content.modal.form action="{{ BASE_URL }}/api/apiKey/<?=(int)$_GET['id'] ?>" method="post" class="stdform formModal" >
        <input type="hidden" name="<?=session("formTokenName")?>" value="<?=session("formTokenValue")?>" />
        <input type="hidden" name="save" value="1" />

        <div class="row" >
            <div class="col-md-6">

                <h4 class="widgettitle title-light">{{ __("label.basic_information") }}</h4>

                <label>{{ __("label.key") }}</label><div class="clearfix"></div>
                lt_<?php echo substr($values['user'], 0, 5) ?>***<br /><br />

                <label for="firstname">{{ __("label.key_name") }}</label><div class="clearfix"></div>
                    <input
                    type="text" name="firstname" id="firstname"
                    value="<?php echo $values['firstname'] ?>" /><br />


                    <x-global::forms.select 
                    name="role" 
                    id="role" 
                    labelText="{!! __('label.role') !!}"
                >
                    @foreach ($tpl->get('roles') as $key => $role)
                        <x-global::forms.select.select-option 
                            value="{{ $key }}" 
                            :selected="$key == $values['role']">
                            {!! __('label.roles.' . $role) !!}
                        </x-global::forms.select.select-option>
                    @endforeach
                </x-global::forms.select>
                
                <br />
                
                <x-global::forms.select 
                    name="status" 
                    id="status" 
                    labelText="{!! __('label.status') !!}"
                >
                    <x-global::forms.select.select-option value="a" :selected="strtolower($values['status']) == 'a'">
                        {!! __('label.active') !!}
                    </x-global::forms.select.select-option>
                
                    <x-global::forms.select.select-option value="" :selected="strtolower($values['status']) == ''">
                        {!! __('label.deactivated') !!}
                    </x-global::forms.select.select-option>
                </x-global::forms.select>
                

                    <div class="clearfix"></div>

                <p class="stdformbutton">
                    <input type="submit" name="save" id="save" value="{{ __("buttons.save") }}" class="button" />
                </p>

            </div>
            <div class="col-md-6">

                <h4 class="widgettitle title-light">{{ __("label.project_access") }}</h4>

                <div class="scrollableItemList">
                    <?php
                    $currentClient = '';
                    $i = 0;
                    $containerOpen = false;
                    foreach ($tpl->get('allProjects') as $row) {

                        if ($currentClient != $row['clientName']) {
                            if ($i > 0 && $containerOpen) {
                                echo"</div>";
                                $containerOpen = false;
                            }

                            echo "<h3 id='accordion_link_" . $i . "'>
                            <a href='#' onclick='accordionToggle(" . $i . ");' id='accordion_toggle_" . $i . "'><i class='fa fa-angle-down'></i> " . $tpl->escape($row['clientName']) . "</a>
                            </h3>
                            <div id='accordion_" . $i . "' class='simpleAccordionContainer'>";
                                $currentClient = $row['clientName'];
                                $containerOpen = true;
                        } ?>

                        <div class="item">
                            <x-global::forms.checkbox
                                name="projects[]"
                                id="project_{{ $row['id'] }}"
                                value="{{ $row['id'] }}"
                                :checked="is_array($projects) && in_array($row['id'], $projects)"
                                labelText="{{ $row['name'] }}"
                                labelPosition="right"
                            />
                            <div class="clearall"></div>
                        </div>
                        <?php $i++; ?>
                    <?php } ?>


                </div>

            </div>
        </div>

</x-global::content.modal.form>
</div>
<script>
    jQuery(".noClickProp.dropdown-menu").on("click", function(e) {
        e.stopPropagation();
    });

    function accordionToggle(id) {
        let currentLink = jQuery("#accordion_toggle_"+id).find("i.fa");
        if (currentLink.hasClass("fa-angle-right")){
            currentLink.removeClass("fa-angle-right");
            currentLink.addClass("fa-angle-down");
            jQuery('#accordion_'+id).slideDown("fast");
        } else {
            currentLink.removeClass("fa-angle-down");
            currentLink.addClass("fa-angle-right");
            jQuery('#accordion_'+id).slideUp("fast");
        }
    }
</script>

@endsection
