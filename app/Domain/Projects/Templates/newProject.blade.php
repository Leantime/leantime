@extends($layout)

@section('content')

<?php
$project = $tpl->get('project');

?>

<div class="pageheader">

    <div class="pageicon"><span class="fa fa-suitcase"></span></div>
    <div class="pagetitle">
        <h5>{{ __("label.administration") }}</h5>
        <h1>{{ __("headline.new_project") }}</h1>
    </div>

</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        @displayNotification()

        <div class="tabbedwidget tab-primary projectTabs">

            <ul>
                <li><a href="#projectdetails">{{ __("tabs.projectdetails") }}</a></li>
            </ul>

            <div id="projectdetails">
                <form action="" method="post" class="">

                    <div class="row">

                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="form-group">
                                        <x-global::forms.text-input 
                                        type="text" 
                                        name="name" 
                                        id="name" 
                                        value="{{ $tpl->escape($project['name']) }}" 
                                        placeholder="{{ $tpl->__('input.placeholders.enter_title_of_project') }}" 
                                        variant="title" 
                                        class="w-[99%]" 
                                    />
                                                                        </div>
                                    <input type="hidden" name="projectState"  id="projectState" value="0" />

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <br />
                                    <p>
                                        {{ __("label.accomplish") }}
                                        {{ __("label.describe_outcome") }}
                                        <br /><br />
                                    </p>
                                    <textarea name="details" id="details" class="complexEditor" rows="5" cols="50"><?php echo htmlentities($project['details']) ?></textarea>

                                </div>
                            </div>
                            <div class="padding-top">
                                <?php if (isset($project['id']) && $project['id'] != '') : ?>
                                    <div class="pull-right padding-top">
                                        <a href="{{ BASE_URL }}/projects/delProject/<?php echo $project['id']?>" class="delete"><i class="fa fa-trash"></i> {{ __("buttons.delete") }}</a>
                                    </div>
                                <?php endif; ?>

                                <x-global::forms.button type="submit" :tag="'input'" :btnType="'secondary'" :btnState="'error'" name="save" id="save" value="{{ __('buttons.save') }}" />

                            </div>
                        </div>

                        <div class="col-md-4">

                            <?php if ($tpl->get('projectTypes') && count($tpl->get('projectTypes')) > 1) {?>
                                <h4 class="widgettitle title-light"><i class="fa-regular fa-rectangle-list"></i> Project Type</h4>
                                <p>The type of the project. This will determine which features are available.</p>
                                <select name="type">
                                    <?php foreach ($tpl->get('projectTypes') as $key => $type) { ?>
                                        <option value="<?=$tpl->escape($key)?>"
                                            <?php if ($project['type'] == $key) {
                                                echo " selected='selected' ";
                                            } ?>
                                        ><?=$tpl->__($tpl->escape($type))?></option>
                                    <?php } ?>
                                </select>
                                <br /><br />
                            <?php } ?>

                            <?php $tpl->dispatchTplEvent("beforeClientPicker", $project) ?>

                            <div style="margin-bottom: 30px;">
                                <h4 class="widgettitle title-light block"><span
                                        class="fa fa-calendar"></span>{{ __("label.project_dates") }}</h4>
                                <div>
                                    <label>{{ __("label.project_start") }}</label>
                                    <div class="">
                                        <input type="text" class="dates dateFrom" style="width:100px;" name="start" autocomplete="off"
                                               value="<?php echo $project['start']; ?>" placeholder="<?=$tpl->__('language.dateformat') ?>"/>

                                    </div>
                                    <label >{{ __("label.project_end") }}</label>
                                    <div class="">
                                        <input type="text" class="dates dateTo" style="width:100px;" name="end" autocomplete="off"
                                               value="<?php echo $project['end']; ?>" placeholder="<?=$tpl->__('language.dateformat') ?>"/>

                                    </div>
                                </div>

                            </div>

                            <div style="margin-bottom: 30px;">

                                <div class="">
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-building"></span>{{ __("label.client_product") }}</h4>
                                    <select name="clientId" id="clientId">

                                        <?php foreach ($tpl->get('clients') as $row) { ?>
                                            <option value="<?php echo $row['id']; ?>"
                                                <?php if ($project['clientId'] == $row['id']) {
                                                    ?> selected=selected
                                                <?php } ?>><?php $tpl->e($row['name']); ?></option>
                                        <?php } ?>

                                    </select>
                                    <?php if ($login::userIsAtLeast("manager")) { ?>
                                        <br /><a href="{{ BASE_URL }}/clients/newClient" target="_blank"><?=$tpl->__('label.client_not_listed'); ?></a>
                                    <?php } ?>


                                </div>
                            </div>

                            <div style="margin-bottom: 30px;">
                                <div class="">
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-lock-open"></span>{{ __("labels.defaultaccess") }}</h4>
                                    {{ __("text.who_can_access") }}
                                    <br /><br />

                                    <select name="globalProjectUserAccess" style="max-width:300px;">
                                        <option value="restricted" <?=$project['psettings'] == "restricted" ? "selected='selected'" : '' ?>>{{ __("labels.only_chose") }}</option>
                                        <option value="clients" <?=$project['psettings'] == "clients" ? "selected='selected'" : ''?>>{{ __("labels.everyone_in_client") }}</option>
                                        <option value="all" <?=$project['psettings'] == "all" ? "selected='selected'" : ''?>>{{ __("labels.everyone_in_org") }}</option>
                                    </select>

                                </div>
                            </div>

                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {

        jQuery("#projectdetails select").chosen();
        leantime.dateController.initDateRangePicker(".dateFrom", ".dateTo", 2);

        leantime.projectsController.initProjectTabs();
        leantime.editorController.initComplexEditor();

        }
    );

</script>

@endsection
