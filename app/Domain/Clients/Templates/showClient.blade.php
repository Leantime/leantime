@php
    $values = $tpl->get('client');
    $users = $tpl->get('users');
@endphp

<x-globals::layout.page-header icon="contact_page" subtitle="{{ __('label.administration') }}" headline="{{ e($values['name']) }}" />

<div class="maincontent">
    <div class="maincontentinner">
        {!! $tpl->displayNotification() !!}

        <x-globals::navigations.tabs persist="url">
            <x-slot:headings>
                <x-globals::navigations.tabs.heading name="clientDetails">{{ __('label.client_details') }}</x-globals::navigations.tabs.heading>
                <x-globals::navigations.tabs.heading name="comment">{{ sprintf(__('tabs.discussion_with_count'), count($tpl->get('comments'))) }}</x-globals::navigations.tabs.heading>
                <x-globals::navigations.tabs.heading name="files">{{ sprintf(__('tabs.files_with_count'), count($tpl->get('files'))) }}</x-globals::navigations.tabs.heading>
            </x-slot:headings>
            <x-slot:contents>

                <x-globals::navigations.tabs.content name="clientDetails">
                    <form action="" method="post">

                        <div class="row row-fluid">
                            <div class="col-md-6">
                                <x-globals::elements.section-title icon="eco">{{ __('subtitle.details') }}</x-globals::elements.section-title>

                                <div class="form-group">
                                    <label class=" control-label">{{ __('label.client_id') }}</label>
                                    <div class="">
                                        <x-globals::forms.text-input name="id" id="id" value="{{ e($values['id']) }}" :readonly="true" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class=" control-label">{{ __('label.name') }}</label>
                                    <div class="">
                                        <x-globals::forms.text-input name="name" id="name" value="{{ e($values['name']) }}" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class=" control-label">{{ __('label.email') }}</label>
                                    <div class="">
                                        <x-globals::forms.text-input name="email" id="email" value="{{ e($values['email']) }}" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class=" control-label">{{ __('label.url') }}</label>
                                    <div class="">
                                        <x-globals::forms.text-input name="internet" id="internet" value="{{ e($values['internet']) }}" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class=" control-label">{{ __('label.street') }}</label>
                                    <div class="">
                                        <x-globals::forms.text-input name="street" id="street" value="{{ e($values['street']) }}" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class=" control-label">{{ __('label.zip') }}</label>
                                    <div class="">
                                        <x-globals::forms.text-input name="zip" id="zip" value="{{ e($values['zip']) }}" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class=" control-label">{{ __('label.city') }}</label>
                                    <div class="">
                                        <x-globals::forms.text-input name="city" id="city" value="{{ e($values['city']) }}" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class=" control-label">{{ __('label.state') }}</label>
                                    <div class="">
                                        <x-globals::forms.text-input name="state" id="state" value="{{ e($values['state']) }}" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class=" control-label">{{ __('label.country') }}</label>
                                    <div class="">
                                        <x-globals::forms.text-input name="country" id="country" value="{{ e($values['country']) }}" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class=" control-label">{{ __('label.phone') }}</label>
                                    <div class="">
                                        <x-globals::forms.text-input name="phone" id="phone" value="{{ e($values['phone']) }}" />
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-6">
                                <x-globals::elements.section-title icon="group">{{ __('subtitles.users_assigned_to_this_client') }}</x-globals::elements.section-title>
                                <x-globals::forms.button link="#/users/newUser?preSelectedClient={{ $values['id'] }}" type="primary" icon="add">{{ __('buttons.add_user') }}</x-globals::forms.button>
                                <x-globals::elements.table>
                                    <x-slot:head>
                                        <colgroup>
                                            <col class="con1" />
                                            <col class="con0"/>
                                            <col class="con1" />
                                        </colgroup>
                                        <tr>
                                            <th>{{ __('label.name') }}</th>
                                            <th>{{ __('label.email') }}</th>
                                            <th>{{ __('label.phone') }}</th>
                                            <th>{{ __('label.actions') }}</th>
                                        </tr>
                                    </x-slot:head>

                                    @foreach($tpl->get('userClients') as $user)
                                        <tr>
                                            <td>
                                                {{ sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) }}
                                            </td>
                                            <td><a href='mailto:{{ e($user['username']) }}'>{{ e($user['username']) }}</a></td>
                                            <td>{{ e($user['phone']) }}</td>
                                            <td>
                                                <a href="{{ BASE_URL }}/users/editUser/{{ $user['id'] }}" title="{{ __('buttons.edit') }}">
                                                    <x-globals::elements.icon name="edit" />
                                                </a>
                                                <a href="{{ BASE_URL }}/clients/removeUser/{{ $values['id'] }}/{{ $user['id'] }}"
                                                   class="delete"
                                                   title="{{ __('buttons.remove') }}"
                                                   onclick="return confirm('{{ __('text.confirm_remove_user_from_client') }}')">
                                                    <x-globals::elements.icon name="delete" />
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if(count($tpl->get('userClients')) == 0)
                                        <tr><td colspan='4'>{{ __('text.no_users_assigned_to_this_client') }}</td></tr>
                                    @endif
                                </x-globals::elements.table>

                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <x-globals::forms.button submit type="primary" name="save" id="save">{{ __('buttons.save') }}</x-globals::forms.button>
                            </div>
                            <div class="col-md-6 align-right">
                                <a href="{{ BASE_URL }}/clients/delClient/{{ e($_GET['id']) }}" class="delete"><x-globals::elements.icon name="delete" /> {{ __('links.delete') }}</a>
                            </div>
                        </div>

                    </form>
                </x-globals::navigations.tabs.content>

                <x-globals::navigations.tabs.content name="comment">
                    <form method="post" action="{{ BASE_URL }}/clients/showClient/{{ e($_GET['id']) }}#comment">
                        <input type="hidden" name="comment" value="1" />
                        @php
                            $tpl->assign('formUrl', BASE_URL . '/clients/showClient/' . e($_GET['id']));
                            $tpl->displaySubmodule('comments-generalComment');
                        @endphp
                    </form>
                </x-globals::navigations.tabs.content>

                <x-globals::navigations.tabs.content name="files">
                    <div class="mediamgr_category">
                        <form action='#files' method='POST' enctype="multipart/form-data">
                            <div class="par tw:inline-flex tw:mr-4">
                                <div class='fileupload fileupload-new' data-provides='fileupload'>
                                    <input type="hidden" />
                                    <div class="input-append">
                                        <div class="uneditable-input span3">
                                            <i class="fa-file fileupload-exists" aria-hidden="true"></i><span class="fileupload-preview"></span>
                                        </div>
                                        <span class="btn btn-file">
                                            <span class="fileupload-new">{{ __('label.select_file') }}</span>
                                            <span class='fileupload-exists'>{{ __('label.change') }}</span>
                                            <x-globals::forms.file :bare="true" name="file" />
                                        </span>
                                        <x-globals::forms.button link="#" type="secondary" class="fileupload-exists" data-dismiss="fileupload">{{ __('buttons.remove') }}</x-globals::forms.button>
                                    </div>
                                </div>
                            </div>
                            <x-globals::forms.button submit type="primary" name="upload">{{ __('buttons.upload') }}</x-globals::forms.button>
                        </form>
                    </div>

                    <div class="mediamgr_content">
                        <ul id='medialist' class='listfile'>
                            @foreach($tpl->get('files') as $file)
                                <li class="{{ $file['moduleId'] }}">
                                    <x-globals::actions.dropdown-menu class="tw:float-right">
                                        <li class="nav-header border">{{ __('subtitles.file') }}</li>
                                        <li><a href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}">{{ __('links.download') }}</a></li>
                                        @if($login::userIsAtLeast($roles::$admin))
                                            <li><a href="{{ BASE_URL }}/clients/showClient/{{ e($_GET['id']) }}?delFile={{ $file['id'] }}" class="delete"><x-globals::elements.icon name="delete" /> {{ __('links.delete') }}</a></li>
                                        @endif
                                    </x-globals::actions.dropdown-menu>
                                    <a class="cboxElement" href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ e($file['extension']) }}&realName={{ e($file['realName']) }}">
                                        @if(in_array(strtolower($file['extension']), $tpl->get('imgExtensions')))
                                            <img class="tw:max-h-12 tw:max-w-[70px]" src="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ e($file['extension']) }}&realName={{ e($file['realName']) }}" alt="" />
                                        @else
                                            <img class="tw:max-h-12 tw:max-w-[70px]" src='{{ BASE_URL }}/dist/images/thumbs/doc.png' />
                                        @endif
                                        <span class="filename">{{ e($file['realName']) }}</span>
                                    </a>
                                </li>
                            @endforeach
                            <br class="clearall" />
                        </ul>
                    </div>
                    <div class="tw:clear-both">&nbsp;</div>

                </x-globals::navigations.tabs.content>

            </x-slot:contents>
        </x-globals::navigations.tabs>
    </div>
</div>

<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')

    jQuery(document).ready(function($) {
    });

    @dispatchEvent('scripts.beforeClose')

</script>
