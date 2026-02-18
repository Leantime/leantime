@php
    $values = $tpl->get('client');
    $users = $tpl->get('users');
@endphp

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5>{{ __('label.administration') }}</h5>
        <h1>{{ e($values['name']) }}</h1>
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div>
@dispatchEvent('afterPageHeaderClose')

<div class="maincontent">
    <div class="maincontentinner">
        {!! $tpl->displayNotification() !!}

        <div class="tabbedwidget tab-primary clientTabs">

            <ul>
                <li><a href="#clientDetails">{{ __('label.client_details') }}</a></li>
                <li><a href="#comment">{{ sprintf(__('tabs.discussion_with_count'), count($tpl->get('comments'))) }}</a></li>
                <li><a href="#files">{{ sprintf(__('tabs.files_with_count'), count($tpl->get('files'))) }}</a></li>
            </ul>

            <div id='clientDetails'>
                <form action="" method="post">

                    <div class="tw:grid tw:md:grid-cols-2 tw:gap-6">
                        <div>
                            <h4 class="widgettitle title-light"><span class="fa fa-leaf"></span> {{ __('subtitle.details') }}</h4>

                            <div class="form-group">
                                <label class=" control-label">{{ __('label.client_id') }}</label>
                                <div class="">
                                    <x-global::forms.input name="id" id="id" value="{{ e($values['id']) }}" :readonly="true" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{{ __('label.name') }}</label>
                                <div class="">
                                    <x-global::forms.input name="name" id="name" value="{{ e($values['name']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{{ __('label.email') }}</label>
                                <div class="">
                                    <x-global::forms.input name="email" id="email" value="{{ e($values['email']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{{ __('label.url') }}</label>
                                <div class="">
                                    <x-global::forms.input name="internet" id="internet" value="{{ e($values['internet']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{{ __('label.street') }}</label>
                                <div class="">
                                    <x-global::forms.input name="street" id="street" value="{{ e($values['street']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{{ __('label.zip') }}</label>
                                <div class="">
                                    <x-global::forms.input name="zip" id="zip" value="{{ e($values['zip']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{{ __('label.city') }}</label>
                                <div class="">
                                    <x-global::forms.input name="city" id="city" value="{{ e($values['city']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{{ __('label.state') }}</label>
                                <div class="">
                                    <x-global::forms.input name="state" id="state" value="{{ e($values['state']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{{ __('label.country') }}</label>
                                <div class="">
                                    <x-global::forms.input name="country" id="country" value="{{ e($values['country']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{{ __('label.phone') }}</label>
                                <div class="">
                                    <x-global::forms.input name="phone" id="phone" value="{{ e($values['phone']) }}" />
                                </div>
                            </div>

                        </div>

                        <div>
                            <h4 class="widgettitle title-light"><span class="fa fa-users"></span> {{ __('subtitles.users_assigned_to_this_client') }}</h4>
                            <x-global::button link="#/users/newUser?preSelectedClient={{ $values['id'] }}" type="primary" icon="fa fa-plus">{{ __('buttons.add_user') }}</x-global::button>
                            <table class='table table-bordered'>
                                <colgroup>
                                    <col class="con1" />
                                    <col class="con0"/>
                                    <col class="con1" />
                                </colgroup>
                                <thead>
                                <tr>
                                    <th>{{ __('label.name') }}</th>
                                    <th>{{ __('label.email') }}</th>
                                    <th>{{ __('label.phone') }}</th>
                                    <th>{{ __('label.actions') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($tpl->get('userClients') as $user)
                                    <tr>
                                        <td>
                                            {{ sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) }}
                                        </td>
                                        <td><a href='mailto:{{ e($user['username']) }}'>{{ e($user['username']) }}</a></td>
                                        <td>{{ e($user['phone']) }}</td>
                                        <td>
                                            <a href="{{ BASE_URL }}/users/editUser/{{ $user['id'] }}" title="{{ __('buttons.edit') }}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="{{ BASE_URL }}/clients/removeUser/{{ $values['id'] }}/{{ $user['id'] }}"
                                               class="delete"
                                               title="{{ __('buttons.remove') }}"
                                               onclick="return confirm('{{ __('text.confirm_remove_user_from_client') }}')">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach

                                @if(count($tpl->get('userClients')) == 0)
                                    <tr><td colspan='4'>{{ __('text.no_users_assigned_to_this_client') }}</td></tr>
                                @endif
                                </tbody>
                            </table>

                        </div>

                    </div>

                    <div class="tw:grid tw:md:grid-cols-2 tw:gap-6">
                        <div>
                            <x-global::button submit type="primary" name="save" id="save">{{ __('buttons.save') }}</x-global::button>
                        </div>
                        <div class="tw:text-right">
                            <a href="{{ BASE_URL }}/clients/delClient/{{ e($_GET['id']) }}" class="delete"><i class="fa fa-trash"></i> {{ __('links.delete') }}</a>
                        </div>
                    </div>

                </form>
            </div>

            <div id='comment'>
                <form method="post" action="{{ BASE_URL }}/clients/showClient/{{ e($_GET['id']) }}#comment">
                    <input type="hidden" name="comment" value="1" />
                    @php
                        $tpl->assign('formUrl', BASE_URL . '/clients/showClient/' . e($_GET['id']));
                        $tpl->displaySubmodule('comments-generalComment');
                    @endphp
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
                                        <i class="fa-file fileupload-exists"></i><span class="fileupload-preview"></span>
                                    </div>
                                    <span class="btn btn-file">
                                        <span class="fileupload-new">{{ __('label.select_file') }}</span>
                                        <span class='fileupload-exists'>{{ __('label.change') }}</span>
                                        <input type='file' name='file' />
                                    </span>
                                    <x-global::button link="#" type="secondary" class="fileupload-exists" data-dismiss="fileupload">{{ __('buttons.remove') }}</x-global::button>
                                </div>
                            </div>
                        </div>
                        <x-global::button submit type="primary" name="upload">{{ __('buttons.upload') }}</x-global::button>
                    </form>
                </div>

                <div class="mediamgr_content">
                    <ul id='medialist' class='listfile'>
                        @foreach($tpl->get('files') as $file)
                            <li class="{{ $file['moduleId'] }}">
                                <div class="inlineDropDownContainer" style="float:right;">
                                    <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="nav-header">{{ __('subtitles.file') }}</li>
                                        <li><a href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}">{{ __('links.download') }}</a></li>
                                        @if($login::userIsAtLeast($roles::$admin))
                                            <li><a href="{{ BASE_URL }}/clients/showClient/{{ e($_GET['id']) }}?delFile={{ $file['id'] }}" class="delete"><i class="fa fa-trash"></i> {{ __('links.delete') }}</a></li>
                                        @endif
                                    </ul>
                                </div>
                                <a class="cboxElement" href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ e($file['extension']) }}&realName={{ e($file['realName']) }}">
                                    @if(in_array(strtolower($file['extension']), $tpl->get('imgExtensions')))
                                        <img style='max-height: 50px; max-width: 70px;' src="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ e($file['extension']) }}&realName={{ e($file['realName']) }}" alt="" />
                                    @else
                                        <img style='max-height: 50px; max-width: 70px;' src='{{ BASE_URL }}/dist/images/thumbs/doc.png' />
                                    @endif
                                    <span class="filename">{{ e($file['realName']) }}</span>
                                </a>
                            </li>
                        @endforeach
                        <br class="clearall" />
                    </ul>
                </div>
                <div style='clear:both'>&nbsp;</div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')

    jQuery(document).ready(function($) {
        leantime.clientsController.initClientTabs();
    });

    @dispatchEvent('scripts.beforeClose')

</script>
