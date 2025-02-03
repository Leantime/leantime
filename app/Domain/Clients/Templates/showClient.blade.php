<x-global::content.modal.modal-buttons />

<?php
$values = $tpl->get('client');
$users = $tpl->get('users');
?>

{{-- <div class="pageheader">
    <div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5>{{ __("label.administration") }}</h5>
        <h1>{{$values->name}}</h1>
    </div>
</div><!--pageheader--> --}}

{{-- <h3>{{$values->name}}</h3> --}}

    @displayNotification()
    <div>
        <x-global::content.tabs name="clientTabs" variant="bordered" size="md" class="mb-2">
            <x-slot:headings class="col-md-5">
                <x-global::content.tabs.heading name="clientDetails">{{ __("label.client_details") }}</x-global::content.tabs.heading>
                <x-global::content.tabs.heading name="comment"><?php echo sprintf($tpl->__('tabs.discussion_with_count'), count($tpl->get('comments'))); ?></x-global::content.tabs.heading>
                <x-global::content.tabs.heading name="files"><?php echo sprintf($tpl->__('tabs.files_with_count'), count($tpl->get('files'))); ?></x-global::content.tabs.heading>
            </x-slot:headings>

            <x-slot:contents>
                <x-global::content.tabs.content name="clientDetails" ariaLabel="Client Details" classExtra="p-sm" :checked="true">
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
                                        value="{{ $values->id }}"
                                        labelText="{{ __('label.client_id') }}"
                                        readonly
                                    />
                                </div>
                                <div class="form-group">
                                    <x-global::forms.text-input
                                        inputType="text"
                                        name="name"
                                        id="name"
                                        value="{{ $values->name }}"
                                        labelText="{{ __('label.name') }}"
                                    />
                                </div>
                                <div class="form-group">
                                    <x-global::forms.text-input
                                        inputType="text"
                                        name="email"
                                        id="email"
                                        value="{{ $values->email }}"
                                        labelText="{{ __('label.email') }}"
                                    />
                                </div>
                                <div class="form-group">
                                    <x-global::forms.text-input
                                        inputType="text"
                                        name="internet"
                                        id="internet"
                                        value="{{ $values->internet }}"
                                        labelText="{{ __('label.url') }}"
                                    />
                                </div>
                                <div class="form-group">
                                    <x-global::forms.text-input
                                        inputType="text"
                                        name="street"
                                        id="street"
                                        value="{{ $values->street }}"
                                        labelText="{{ __('label.street') }}"
                                    />
                                </div>
                                <div class="form-group">
                                    <x-global::forms.text-input
                                        inputType="text"
                                        name="zip"
                                        id="zip"
                                        value="{{ $values->zip }}"
                                        labelText="{{ __('label.zip') }}"
                                    />
                                </div>
                                <div class="form-group">
                                    <x-global::forms.text-input
                                        inputType="text"
                                        name="city"
                                        id="city"
                                        value="{{ $values->city }}"
                                        labelText="{{ __('label.city') }}"
                                    />
                                </div>
                                <div class="form-group">
                                    <x-global::forms.text-input
                                        inputType="text"
                                        name="state"
                                        id="state"
                                        value="{{ $values->state }}"
                                        labelText="{{ __('label.state') }}"
                                    />
                                </div>
                                <div class="form-group">
                                    <x-global::forms.text-input
                                        inputType="text"
                                        name="country"
                                        id="country"
                                        value="{{ $values->country }}"
                                        labelText="{{ __('label.country') }}"
                                    />
                                </div>
                                <div class="form-group">
                                    <x-global::forms.text-input
                                        inputType="text"
                                        name="phone"
                                        id="phone"
                                        value="{{ $values->phone }}"
                                        labelText="{{ __('label.phone') }}"
                                    />
                                </div>
                            </div>
                                                    <div class="col-md-6">
                                <h4 class="widgettitle title-light"><span class="fa fa-users"></span> {{ __("subtitles.users_assigned_to_this_client") }}</h4>
                                <a href="#/users/newUser?preSelectedClient={{ $values->id }}" class="btn btn-primary"><i class='fa fa-plus'></i> <?=$tpl->__('buttons.add_user') ?> </a>
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
                                            <?php printf($tpl->escape($user['name'])); ?>
                                            </td>
                                            {{-- <td><a href='mailto:{{ $user['username'] }}'> {{ $user['username'] }}</a></td> --}}
                                            {{-- <td>{{$user['phone']}}</td> --}}
                                        </tr>
                                    <?php endforeach; ?>
                                            <?php if (count($tpl->get('userClients')) == 0) {
                                                echo "<tr><td colspan='3'>" . $tpl->__('text.no_users_assigned_to_this_client') . '</td></tr>';
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <x-global::forms.button type="submit" name="save" value="true" id="save">
                                    {{ __('buttons.save') }}
                                </x-global::forms.button>
                                <x-global::forms.button
                                    tag="a"
                                    href="/clients/showAll"
                                    content-role="tertiary"
                                >
                                    {{ __('buttons.back') }}
                                </x-global::forms.button>
                            </div>
                            <div class="col-md-6 align-right">
                                <a href="#/clients/delClient/{{ $values->id }}" class="delete"><i class="fa fa-trash"></i> {{ __("links.delete") }}</a>
                            </div>
                        </div>
                        </form>
                </x-global::content.tabs.content>

                <x-global::content.tabs.content name="comment" ariaLabel="Comment" classExtra="p-sm">
                    <form method="post" value="true" action="{{ BASE_URL }}/clients/showClient/{{$values->id}}#comment">
                        <input type="hidden" name="comment" value="1" />
                        <x-comments::list :module="'client'" :statusUpdates="'true'" moduleId="{{$values->id}}" />
                    </form> 
                </x-global::content.tabs.content>

                <x-global::content.tabs.content name="files" ariaLabel="Files" classExtra="p-sm">
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
                            <x-global::forms.button type="submit" name="upload" content-role="primary" >
                                {{ __('buttons.upload') }}
                            </x-global::forms.button>
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
                                        <x-global::actions.dropdown.item variant="header">
                                            {{ __('subtitles.file') }}
                                        </x-global::actions.dropdown.item>
                            <!-- Download Link -->
                            <x-global::actions.dropdown.item variant="link"
                                href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}">
                                {{ __('links.download') }}
                            </x-global::actions.dropdown.item>
                            <!-- Delete Link (Only for Admins) -->
                            @if ($login::userIsAtLeast($roles::$admin))
                                <x-global::actions.dropdown.item variant="link"
                                    href="{{ BASE_URL }}/clients/showClient/{{ $values->id }}?delFile={{ $file['id'] }}"
                                    class="delete">
                                    <i class="fa fa-trash"></i> {{ __('links.delete') }}
                                </x-global::actions.dropdown.item>
                            @endif
                            </x-slot:menu>
                            </x-global::content.context-menu>
                            <a class="cboxElement"
                                href="{{ BASE_URL }}/files/get?module={{$file['module']}}&encName={{$file['encName']}}&ext={{$file['extension']}}&realName={{$file['realName']}}">
                                <?php if (in_array(strtolower($file['extension']), $tpl->get('imgExtensions'))) :  ?>
                                <img style='max-height: 50px; max-width: 70px;'
                                    src="{{ BASE_URL }}/files/get?module={{$file['module']}}&encName={{$file['encName']}}&ext={{$file['extension']}}&realName={{$file['realName']}}"
                                    alt="" />
                                <?php else : ?>
                                <img style='max-height: 50px; max-width: 70px;'
                                    src='{{ BASE_URL }}/dist/images/thumbs/doc.png' />
                                <?php endif; ?>
                                <span class="filename">{{$file['realName']}}</span>
                            </a>
                            </li>
                            <?php endforeach; ?>
                            <br class="clearall" />
                        </ul>
                    </div><!--mediamgr_content-->
                    <div style='clear:both'>&nbsp;</div>
                </x-global::content.tabs.content>
            </x-slot:contents>
        </x-global::content.tabs>
    </div>

<script type="module">

    import "@mix('/js/Domain/Clients/Js/clientsController.js')"

    jQuery(document).ready(function($) {
        clientsController.initClientTabs();
    });

</script>
