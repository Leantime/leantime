@extends($layout)
@section('content')

@php
    $values = $client;
@endphp

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5>{!! __('label.administration') !!}</h5>
        <h1>{{ $values['name'] }}</h1>
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div><!--pageheader-->
@dispatchEvent('afterPageHeaderClose')

<div class="maincontent">
    <div class="maincontentinner">
        {!! $tpl->displayNotification() !!}

        <div class="tabbedwidget tab-primary clientTabs">

            <ul>
                <li><a href="#clientDetails">{!! __('label.client_details') !!}</a></li>
                <li><a href="#comment">{!! sprintf(__('tabs.discussion_with_count'), count($submodules.generalComment)) !!}</a></li>
                <li><a href="#files">{!! sprintf(__('tabs.files_with_count'), count($files)) !!}</a></li>
            </ul>

            <div id='clientDetails'>
                <form action="" method="post">

                    <div class="row row-fluid">
                        <div class="col-md-6">
                            <h4 class="widgettitle title-light"><span class="fa fa-leaf"></span> {!! __('subtitle.details') !!}</h4>

                            <div class="form-group">
                                <label class=" control-label">{!! __('label.client_id') !!}</label>
                                <div class="">
                                    <input type="text" name="id" id="id" value="{{ $values['id'] }}" readonly />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{!! __('label.name') !!}</label>
                                <div class="">
                                    <input type="text" name="name" id="name" value="{{ $values['name'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{!! __('label.email') !!}</label>
                                <div class="">
                                    <input type="text" name="email" id="email" value="{{ $values['email'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{!! __('label.url') !!}</label>
                                <div class="">
                                    <input
                                            type="text" name="internet" id="internet"
                                            value="{{ $values['internet'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{!! __('label.street') !!}</label>
                                <div class="">
                                    <input
                                            type="text" name="street" id="street"
                                            value="{{ $values['street'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{!! __('label.zip') !!}</label>
                                <div class="">
                                    <input type="text"
                                    name="zip" id="zip" value="{{ $values['zip'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{!! __('label.city') !!}</label>
                                <div class="">
                                    <input type="text"
                                           name="city" id="city" value="{{ $values['city'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{!! __('label.state') !!}</label>
                                <div class="">
                                    <input
                                            type="text" name="state" id="state"
                                            value="{{ $values['state'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{!! __('label.country') !!}</label>
                                <div class="">
                                    <input
                                            type="text" name="country" id="country"
                                            value="{{ $values['country'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label">{!! __('label.phone') !!}</label>
                                <div class="">
                                    <input
                                            type="text" name="phone" id="phone"
                                            value="{{ $values['phone'] }}" />
                                </div>
                            </div>

                        </div>

                        <div class="col-md-6">
                            <h4 class="widgettitle title-light"><span class="fa fa-users"></span> {!! __('subtitles.users_assigned_to_this_client') !!}</h4>
                            <a href="#/users/newUser?preSelectedClient={{ $values['id'] }}" class="btn btn-primary"><i class='fa fa-plus'></i> {!! __('buttons.add_user') !!} </a>
                            <table class='table table-bordered'>
                                <colgroup>
                                    <col class="con1" />
                                    <col class="con0"/>
                                    <col class="con1" />
                                </colgroup>
                                <thead>
                                <tr>
                                    <th>{!! __('label.name') !!}</th>
                                    <th>{!! __('label.email') !!}</th>
                                    <th>{!! __('label.phone') !!}</th>
                                    <th>{!! __('label.actions') !!}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($userClients as $user)
                                    <tr>
                                        <td>
                                        {!! sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) !!}
                                        </td>
                                        <td><a href='mailto:{{ $user['username'] }}'>{{ $user['username'] }}</a></td>
                                        <td>{{ $user['phone'] }}</td>
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

                                @if (count($userClients) == 0)
                                    <tr><td colspan='4'>{!! __('text.no_users_assigned_to_this_client') !!}</td></tr>
                                @endif
                                </tbody>
                            </table>

                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <input type="submit" name="save" id="save"
                                   value="{{ __('buttons.save') }}" class="btn btn-primary" />
                        </div>
                        <div class="col-md-6 align-right">
                            <a href="{{ BASE_URL }}/clients/delClient/{{ $_GET['id'] }}" class="delete"><i class="fa fa-trash"></i> {!! __('links.delete') !!}</a>
                        </div>
                    </div>

                </form>
            </div>

            <div id='comment'>

                <form method="post" action="{{ BASE_URL }}/clients/showClient/{{ $_GET['id'] }}#comment">
                    <input type="hidden" name="comment" value="1" />
                    @php
                        $tpl->assign('formUrl', BASE_URL . '/clients/showClient/' . $tpl->escape($_GET['id']) . '');
                    @endphp
                    @include('comments::submodules.generalComment')
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
                                                         <span class="fileupload-new">{!! __('label.select_file') !!}</span>
                                                <span class='fileupload-exists'>{!! __('label.change') !!}</span>
                                                        <input type='file' name='file' />
                                                    </span>
                                    <a href='#' class='btn fileupload-exists' data-dismiss='fileupload'>{!! __('buttons.remove') !!}</a>
                                </div>
                            </div>
                        </div>

                        <input type="submit" name="upload" class="button" value="{{ __('buttons.upload') }}" />

                    </form>
                </div>

                <div class="mediamgr_content">

                    <ul id='medialist' class='listfile'>
                                    @foreach ($files as $file)
                                        <li class="{{ $file['moduleId'] }}">
                                            <div class="inlineDropDownContainer" style="float:right;">

                                                <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li class="nav-header">{!! __('subtitles.file') !!}</li>
                                                    <li><a href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}">{!! __('links.download') !!}</a></li>

                                                    @if ($login::userIsAtLeast($roles::$admin))
                                                        <li><a href="{{ BASE_URL }}/clients/showClient/{{ $_GET['id'] }}?delFile={{ $file['id'] }}" class="delete"><i class="fa fa-trash"></i> {!! __('links.delete') !!}</a></li>
                                                    @endif

                                                </ul>
                                            </div>
                                              <a class="cboxElement" href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}">
                                                  @if (in_array(strtolower($file['extension']), $imgExtensions))
                                                      <img style='max-height: 50px; max-width: 70px;' src="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}" alt="" />
                                                  @else
                                                      <img style='max-height: 50px; max-width: 70px;' src='{{ BASE_URL }}/dist/images/thumbs/doc.png' />
                                                  @endif
                                                <span class="filename">{{ $file['realName'] }}</span>
                                              </a>
                                           </li>
                                    @endforeach
                                    <br class="clearall" />
                                    </ul>

                </div><!--mediamgr_content-->
                <div style='clear:both'>&nbsp;</div>


            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')

    jQuery(document).ready(function($)
        {
            leantime.clientsController.initClientTabs();
        }
    );

    @dispatchEvent('scripts.beforeClose')

</script>
@endpush
@endonce

@endsection
