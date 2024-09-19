@extends($layout)

@section('content')

    <?php
    $values = $tpl->get('client');
    $users = $tpl->get('users');
    ?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5>{{ __("label.administration") }}</h5>
        <h1><?php $tpl->e($values['name']); ?></h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">
        @displayNotification()

            <div class="tabbedwidget tab-primary clientTabs">

            <ul>
                <li><a href="#clientDetails">{{ __("label.client_details") }}</a></li>
                <li><a href="#comment"><?php echo sprintf($tpl->__('tabs.discussion_with_count'), count($tpl->get('comments'))); ?></a></li>
                <li><a href="#files"><?php echo sprintf($tpl->__('tabs.files_with_count'), count($tpl->get('files'))); ?></a></li>
            </ul>

                <div id='clientDetails'>
                    <form action="" method="post">

                    <div class="row row-fluid">
                        <div class="col-md-6">
                            <h4 class="widgettitle title-light">
                                <span class="fa fa-leaf"></span> {{ __('subtitle.details') }}
                            </h4>
                        
                            <div class="form-group">
                                <x-global::forms.text-input 
                                    inputType="text" 
                                    name="id" 
                                    id="id" 
                                    value="{{ $tpl->escape($values['id']) }}" 
                                    labelText="{{ __('label.client_id') }}" 
                                    readonly 
                                />
                            </div>
                        
                            <div class="form-group">
                                <x-global::forms.text-input 
                                    inputType="text" 
                                    name="name" 
                                    id="name" 
                                    value="{{ $tpl->escape($values['name']) }}" 
                                    labelText="{{ __('label.name') }}" 
                                />
                            </div>
                        
                            <div class="form-group">
                                <x-global::forms.text-input 
                                    inputType="text" 
                                    name="email" 
                                    id="email" 
                                    value="{{ $tpl->escape($values['email']) }}" 
                                    labelText="{{ __('label.email') }}" 
                                />
                            </div>
                        
                            <div class="form-group">
                                <x-global::forms.text-input 
                                    inputType="text" 
                                    name="internet" 
                                    id="internet" 
                                    value="{{ $tpl->escape($values['internet']) }}" 
                                    labelText="{{ __('label.url') }}" 
                                />
                            </div>
                        
                            <div class="form-group">
                                <x-global::forms.text-input 
                                    inputType="text" 
                                    name="street" 
                                    id="street" 
                                    value="{{ $tpl->escape($values['street']) }}" 
                                    labelText="{{ __('label.street') }}" 
                                />
                            </div>
                        
                            <div class="form-group">
                                <x-global::forms.text-input 
                                    inputType="text" 
                                    name="zip" 
                                    id="zip" 
                                    value="{{ $tpl->escape($values['zip']) }}" 
                                    labelText="{{ __('label.zip') }}" 
                                />
                            </div>
                        
                            <div class="form-group">
                                <x-global::forms.text-input 
                                    inputType="text" 
                                    name="city" 
                                    id="city" 
                                    value="{{ $tpl->escape($values['city']) }}" 
                                    labelText="{{ __('label.city') }}" 
                                />
                            </div>
                        
                            <div class="form-group">
                                <x-global::forms.text-input 
                                    inputType="text" 
                                    name="state" 
                                    id="state" 
                                    value="{{ $tpl->escape($values['state']) }}" 
                                    labelText="{{ __('label.state') }}" 
                                />
                            </div>
                        
                            <div class="form-group">
                                <x-global::forms.text-input 
                                    inputType="text" 
                                    name="country" 
                                    id="country" 
                                    value="{{ $tpl->escape($values['country']) }}" 
                                    labelText="{{ __('label.country') }}" 
                                />
                            </div>
                        
                            <div class="form-group">
                                <x-global::forms.text-input 
                                    inputType="text" 
                                    name="phone" 
                                    id="phone" 
                                    value="{{ $tpl->escape($values['phone']) }}" 
                                    labelText="{{ __('label.phone') }}" 
                                />
                            </div>
                        </div>
                                                <div class="col-md-6">
                            <h4 class="widgettitle title-light"><span class="fa fa-users"></span> {{ __("subtitles.users_assigned_to_this_client") }}</h4>
                            <a href="#/users/newUser?preSelectedClient=<?=$values['id'] ?>" class="btn btn-primary"><i class='fa fa-plus'></i> <?=$tpl->__('buttons.add_user') ?> </a>
                            <table class='table table-bordered'>
                                <colgroup>
                                    <col class="con1" />
                                    <col class="con0"/>
                                    <col class="con1" />
                                </colgroup>
                                <thead>
                                <tr>
                                    <th>{{ __("label.name") }}</th>
                                    <th>{{ __("label.email") }}</th>
                                    <th>{{ __("label.phone") }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($tpl->get('userClients') as $user) : ?>
                                    <tr>
                                        <td>
                                        <?php printf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])); ?>
                                        </td>
                                        <td><a href='mailto:<?php $tpl->e($user['username']); ?>'><?php $tpl->e($user['username']); ?></a></td>
                                        <td><?php $tpl->e($user['phone']); ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                        <?php if (count($tpl->get('userClients')) == 0) {
                                            echo "<tr><td colspan='3'>" . $tpl->__('text.no_users_assigned_to_this_client') . '</td></tr>';
                                        } ?>
                                    </tbody>
                                </table>

                            </div>

                        </div>

                    <div class="row">
                        <div class="col-md-6">
                            <input type="submit" name="save" id="save"
                                   value="{{ __("buttons.save") }}" class="btn btn-primary" />
                        </div>
                        <div class="col-md-6 align-right">
                            <a href="{{ BASE_URL }}/clients/delClient/<?php $tpl->e($_GET['id']); ?>" class="delete"><i class="fa fa-trash"></i> {{ __("links.delete") }}</a>
                        </div>
                    </div>

                    </form>
                </div>

                <div id='comment'>

                <form method="post" action="{{ BASE_URL }}/clients/showClient/<?php echo $tpl->e($_GET['id']); ?>#comment">
                    <input type="hidden" name="comment" value="1" />
                    @include("comments::includes.generalComment", ["formUrl" => BASE_URL . "/clients/showClient/" . $tpl->escape($_GET['id'])])
                </form>


                </div>

                <div id='files'>

                    <div class="mediamgr_category">
                        <form action='#files' method='POST' enctype="multipart/form-data">

                            <div class="par f-left" style="margin-right: 15px;">

                                <div class='fileupload fileupload-new' data-provides='fileupload'>
                                    <input type="hidden" />
                                    <div class="input-append">
                                        <div class="uneditable-input span3">
                                            <i class="fa-file fileupload-exists"></i><span
                                                class="fileupload-preview"></span>
                                        </div>
                                        <span class="btn btn-file">
                                            <span class="fileupload-new"><?= $tpl->__('label.select_file') ?></span>
                                            <span class='fileupload-exists'><?= $tpl->__('label.change') ?></span>
                                            <input type='file' name='file' />
                                        </span>
                                        <a href='#' class='btn fileupload-exists'
                                            data-dismiss='fileupload'><?= $tpl->__('buttons.remove') ?></a>
                                    </div>
                                </div>
                            </div>

                            <input type="submit" name="upload" class="button"
                                value="<?= $tpl->__('buttons.upload') ?>" />

                        </form>
                    </div>

                    <div class="mediamgr_content">

                        <ul id='medialist' class='listfile'>
                            <?php foreach ($tpl->get('files') as $file) : ?>
                            <li class="<?php echo $file['moduleId']; ?>">
                                <x-global::content.context-menu
                                    label-text="<i class='fa fa-ellipsis-v' aria-hidden='true'></i>" contentRole="link"
                                    position="bottom" align="start" class="ticketDropDown" style="float:right;">

                                    <x-slot:menu>
                                        <!-- File Section Header -->
                            <li class="nav-header">{{ __('subtitles.file') }}</li>

                            <!-- Download Link -->
                            <x-global::actions.dropdown.item variant="link"
                                href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}">
                                {{ __('links.download') }}
                            </x-global::actions.dropdown.item>

                            <!-- Delete Link (Only for Admins) -->
                            @if ($login::userIsAtLeast($roles::$admin))
                                <x-global::actions.dropdown.item variant="link"
                                    href="{{ BASE_URL }}/clients/showClient/{{ $_GET['id'] }}?delFile={{ $file['id'] }}"
                                    class="delete">
                                    <i class="fa fa-trash"></i> {{ __('links.delete') }}
                                </x-global::actions.dropdown.item>
                            @endif
                            </x-slot:menu>

                            </x-global::content.context-menu>

                            <a class="cboxElement"
                                href="<?= BASE_URL ?>/files/get?module=<?php echo $file['module']; ?>&encName=<?php echo $file['encName']; ?>&ext=<?php $tpl->e($file['extension']); ?>&realName=<?php $tpl->e($file['realName']); ?>">
                                <?php if (in_array(strtolower($file['extension']), $tpl->get('imgExtensions'))) :  ?>
                                <img style='max-height: 50px; max-width: 70px;'
                                    src="<?= BASE_URL ?>/files/get?module=<?php echo $file['module']; ?>&encName=<?php echo $file['encName']; ?>&ext=<?php $tpl->e($file['extension']); ?>&realName=<?php $tpl->e($file['realName']); ?>"
                                    alt="" />
                                <?php else : ?>
                                <img style='max-height: 50px; max-width: 70px;'
                                    src='<?= BASE_URL ?>/dist/images/thumbs/doc.png' />
                                <?php endif; ?>
                                <span class="filename"><?php $tpl->e($file['realName']); ?></span>
                            </a>
                            </li>
                            <?php endforeach; ?>
                            <br class="clearall" />
                        </ul>

                    </div><!--mediamgr_content-->
                    <div style='clear:both'>&nbsp;</div>


                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>

        jQuery(document).ready(function($) {
            leantime.clientsController.initClientTabs();
        });

        <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>
    </script>
