@extends($layout)
@section('content')

@php
    $values = $client;
@endphp

<x-global::layout.page-header icon="fa fa-address-book" :eyebrow="__('label.administration')">
    {{ $values['name'] }}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>
        {!! $tpl->displayNotification() !!}

        <div class="tabbedwidget tab-primary clientTabs">

            <ul>
                <li><a href="#clientDetails"><span class="fa fa-building"></span> {!! __('label.client_details') !!}</a></li>
                <li><a href="#comment"><span class="fa fa-comments"></span> {!! sprintf(__('tabs.discussion_with_count'), count($comments)) !!}</a></li>
                <li><a href="#files"><span class="fa fa-file"></span> {!! sprintf(__('tabs.files_with_count'), count($files)) !!}</a></li>
            </ul>

            <div id='clientDetails'>
                <form action="" method="post">

                    <div class="row row-fluid">
                        <div class="col-md-6">
                            <x-global::elements.section-header icon="fa fa-leaf">{!! __('subtitle.details') !!}</x-global::elements.section-header>

                            <div class="form-group">
                                <x-global::forms.label class=" control-label">{!! __('label.client_id') !!}</x-global::forms.label>
                                <div class="">
                                    <x-global::forms.text-input name="id" id="id" value="{{ $values['id'] }}" readonly />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class=" control-label">{!! __('label.name') !!}</x-global::forms.label>
                                <div class="">
                                    <x-global::forms.text-input name="name" id="name" value="{{ $values['name'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class=" control-label">{!! __('label.email') !!}</x-global::forms.label>
                                <div class="">
                                    <x-global::forms.text-input name="email" id="email" value="{{ $values['email'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class=" control-label">{!! __('label.url') !!}</x-global::forms.label>
                                <div class="">
                                    <x-global::forms.text-input
                                             name="internet" id="internet"
                                            value="{{ $values['internet'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class=" control-label">{!! __('label.street') !!}</x-global::forms.label>
                                <div class="">
                                    <x-global::forms.text-input
                                             name="street" id="street"
                                            value="{{ $values['street'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class=" control-label">{!! __('label.zip') !!}</x-global::forms.label>
                                <div class="">
                                    <x-global::forms.text-input
                                    name="zip" id="zip" value="{{ $values['zip'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class=" control-label">{!! __('label.city') !!}</x-global::forms.label>
                                <div class="">
                                    <x-global::forms.text-input
                                           name="city" id="city" value="{{ $values['city'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class=" control-label">{!! __('label.state') !!}</x-global::forms.label>
                                <div class="">
                                    <x-global::forms.text-input
                                             name="state" id="state"
                                            value="{{ $values['state'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class=" control-label">{!! __('label.country') !!}</x-global::forms.label>
                                <div class="">
                                    <x-global::forms.text-input
                                             name="country" id="country"
                                            value="{{ $values['country'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class=" control-label">{!! __('label.phone') !!}</x-global::forms.label>
                                <div class="">
                                    <x-global::forms.text-input
                                             name="phone" id="phone"
                                            value="{{ $values['phone'] }}" />
                                </div>
                            </div>

                        </div>

                        <div class="col-md-6">
                            <x-global::elements.section-header icon="fa fa-users">{!! __('subtitles.users_assigned_to_this_client') !!}</x-global::elements.section-header>
                            <x-global::forms.button tag="a" link="#/users/newUser?preSelectedClient={{ $values['id'] }}" contentRole="primary"><i class='fa fa-plus'></i> {!! __('buttons.add_user') !!} </x-global::forms.button>
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
                                            <a href="{{ BASE_URL }}/users/editUser/{{ $user['id'] }}" title="{{ __('label.edit') }}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form method="post" action="{{ BASE_URL }}/clients/removeUser" style="display:inline;"
                                                  onsubmit="return confirm('{{ __('text.confirm_remove_user_from_client') }}')">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $values['id'] }}" />
                                                <input type="hidden" name="userId" value="{{ $user['id'] }}" />
                                                <x-global::forms.button inputType="submit" class="delete" contentRole="link" title="{{ __('buttons.remove') }}" style="padding:0; border:none; background:none;">
                                                    <i class="fa fa-trash"></i>
                                                </x-global::forms.button>
                                            </form>
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
                            <x-global::forms.button tag="input" inputType="submit" name="save" id="save"
                                   :labelText="__('buttons.save')" contentRole="primary" />
                        </div>
                        <div class="col-md-6 align-right">
                            <x-global::forms.button tag="a" link="{{ BASE_URL }}/clients/delClient/{{ $_GET['id'] }}" class="delete" state="danger" variant="outline"><i class="fa fa-trash"></i> {!! __('links.delete') !!}</x-global::forms.button>
                        </div>
                    </div>

                </form>
            </div>

            <div id='comment'>

                <form method="post" action="{{ BASE_URL }}/clients/showClient/{{ $_GET['id'] }}#comment">
                    <input type="hidden" name="comment" value="1" />
                    @include('comments::submodules.generalComment', ['formUrl' => BASE_URL . '/clients/showClient/' . e(request()->query('id', ''))])
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

                        <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.upload')" name="upload" />

                    </form>
                </div>

                <div class="mediamgr_content">

                    <ul id='medialist' class='listfile'>
                                    @foreach ($files as $file)
                                        <x-global::elements.card as="li" compact class="{{ $file['moduleId'] }}">
                                            <x-global::actions.dropdown style="float:right;">
                                                <li class="nav-header">{!! __('subtitles.file') !!}</li>
                                                <li><a href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}">{!! __('links.download') !!}</a></li>

                                                @can('clients.edit')
                                                    <li><a href="javascript:void(0);" data-post-url="{{ BASE_URL }}/clients/showClient/{{ (int) $_GET['id'] }}" data-post-field="delFile" data-post-value="{{ $file['id'] }}" class="delete"><i class="fa fa-trash"></i> {!! __('links.delete') !!}</a></li>
                                                @endcan


                                            </x-global::actions.dropdown>
                                              <a class="cboxElement" href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}">
                                                  @if (in_array(strtolower($file['extension']), $imgExtensions))
                                                      <img style='max-height: 50px; max-width: 70px;' src="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}" alt="" />
                                                  @else
                                                      <img style='max-height: 50px; max-width: 70px;' src='{{ BASE_URL }}/dist/images/thumbs/doc.png' />
                                                  @endif
                                                <span class="filename">{{ $file['realName'] }}</span>
                                              </a>
                                           </x-global::elements.card>
                                    @endforeach
                                    <br class="clearall" />
                                    </ul>

                </div><!--mediamgr_content-->
                <div style='clear:both'>&nbsp;</div>


            </div>
        </div>
    </x-global::layout.content-box>
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
