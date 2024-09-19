<?php

use Leantime\Domain\Menu\Repositories\Menu;

defined('RESTRICTED') or die('Restricted access');

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$project = $tpl->get('project');
$menuTypes = $tpl->get('menuTypes');

?>


<form action="" method="post" class="stdform">

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
                            class="w-[99%] main-title-input" 
                        />
                    </div>
                </div>
            </div>
        
            <div class="row">
                <div class="col-md-12">
                    <p>
                        {{ __("label.accomplish") }}
                        <br /><br />
                    </p>
                    <textarea name="details" id="details" class="complexEditor" rows="5" cols="50">
                        {{ htmlentities($project['details']) }}
                    </textarea>
                </div>
            </div>
        
            <div class="row padding-top">
                <div class="col-md-12">
                    <x-global::forms.button 
                        type="submit" 
                        name="save" 
                        id="save" 
                        class="button"
                    >
                        {{ __("buttons.save") }}
                    </x-global::forms.button>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">

            <div class="row marginBottom">
                <?php if ($tpl->get('projectTypes') && count($tpl->get('projectTypes')) > 1) {?>
                <div class="col-md-12 center">
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
                </div>
                <?php } ?>


            </div>
            <div class="row marginBottom">

                <div class="col-md-12 center">

                    <h4 class="widgettitle title-light"><span
                            class="fa fa-picture-o"></span>{{ __("label.project_avatar") }}</h4>

                    <img src='{{ BASE_URL }}/api/projects?projectAvatar=<?=$project['id']; ?>&v=<?=format($project['modified'])->timestamp() ?>'  class='profileImg' alt='Profile Picture' id="previousImage"/>
                    <div id="projectAvatar">
                    </div>

                    <div class="par">

                        <div class='fileupload fileupload-new' data-provides='fileupload'>
                            <input type="hidden"/>
                            <div class="input-append">
                                <div class="uneditable-input span3">
                                    <i class="fa-file fileupload-exists"></i>
                                    <span class="fileupload-preview"></span>
                                </div>
                                <span class="btn btn-file">
                                        <span class="fileupload-new">{{ __("buttons.select_file") }}</span>
                                        <span class='fileupload-exists'>{{ __("buttons.change") }}</span>
                                        <input type='file' name='file' onchange="leantime.projectsController.readURL(this)" accept=".jpg,.png,.gif,.webp"/>
                                    </span>

                                <a href='#' class='btn fileupload-exists' data-dismiss='fileupload' onclick="leantime.projectsController.clearCroppie()">{{ __("buttons.remove") }}</a>
                            </div>

                            <span id="save-picture" class="btn btn-primary fileupload-exists ld-ext-right">
                                <span onclick="leantime.projectsController.saveCroppie()">{{ __("buttons.save") }}</span>
                                <span class="ld ld-ring ld-spin"></span>
                            </span>
                        <input type="hidden" name="profileImage" value="1" />
                        <input id="picSubmit" type="submit" name="savePic" class="hidden"
                               value="{{ __("buttons.upload") }}"/>

                        </div>
                    </div>
                </div>

            </div>

                <?php $tpl->dispatchTplEvent("afterProjectAvatar", $project) ?>

                <div class="row marginBottom" style="margin-bottom: 30px;">
                    <div class="col-md-12">
                        <h4 class="widgettitle title-light"><span
                                class="fa fa-calendar"></span>{{ __("label.project_dates") }}</h4>


                        <label class="control-label">{{ __("label.project_start") }}</label>
                        <div class="">
                            <input type="text" class="dates" style="width:100px;" name="start" autocomplete="off"
                                   value="<?php echo format($project['start'])->date(); ?>" placeholder="<?=$tpl->__('language.dateformat') ?>"/>

                        </div>
                        <label class="control-label">{{ __("label.project_end") }}</label>
                        <div class="">
                            <input type="text" class="dates" style="width:100px;" name="end" autocomplete="off"
                                   value="<?php echo format($project['end'])->date(); ?>" placeholder="<?=$tpl->__('language.dateformat') ?>"/>

                        </div>
                    </div>

                </div>


                <div class="row" style="margin-bottom: 30px;">

                    <div class="col-md-12 " style="margin-bottom: 30px;">
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



            <div class="row marginBottom" style="margin-bottom: 30px;">
                <div class="col-md-12">
                    <h4 class="widgettitle title-light"><span
                            class="fa fa-wrench"></span>{{ __("label.settings") }}</h4>

            <input type="hidden" name="menuType" id="menuType"
                           value="<?php echo Menu::DEFAULT_MENU; ?>">

                    <div class="form-group">

                <label class="col-md-4 control-label" for="projectState">{{ __("label.project_state") }}</label>
                <div class="col-md-6">
                    <select name="projectState" id="projectState">
                        <option value="0" <?php if ($project['state'] == 0) {
                            ?> selected=selected
                                          <?php } ?>>{{ __("label.open") }}</option>

                        <option value="-1" <?php if ($project['state'] == -1) {
                            ?> selected=selected
                                           <?php } ?>>{{ __("label.closed") }}</option>

                    </select>
                </div>
            </div>

                </div>
            </div>

            <div class="row marginBottom" style="margin-bottom: 30px;">
                <div class="col-md-12 ">
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

            <div class="row" style="margin-bottom: 30px;">
                <div class="col-md-12 ">
                    <h4 class="widgettitle title-light"><span
                                class="fa fa-money-bill-alt"></span>{{ __("label.budgets") }}</h4>
                                <div class="form-group">
                                    <x-global::forms.text-input 
                                        type="text" 
                                        name="hourBudget" 
                                        id="hourBudget" 
                                        value="{{ $tpl->escape($project['hourBudget']) }}" 
                                        labelText="{{ __('label.hourly_budget') }}" 
                                        class="input-large col-md-6" 
                                    />
                                </div>
                                
                                <div class="form-group">
                                    <x-global::forms.text-input 
                                        type="text" 
                                        name="dollarBudget" 
                                        id="dollarBudget" 
                                        value="{{ $tpl->escape($project['dollarBudget']) }}" 
                                        labelText="{{ __('label.budget_cost') }}" 
                                        class="input-large col-md-6" 
                                    />
                                </div>
                                

                </div>
            </div>

        </div>

    </div>


</form>
