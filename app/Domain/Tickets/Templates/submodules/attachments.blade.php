@php
    $ticket = $tpl->get('ticket');
@endphp

<div class="mediamgr_category">
    <form action='{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}#files' method='POST' enctype="multipart/form-data" class="formModal">
        <div class="par f-left" style="margin-right: 15px;">
            <input type="hidden" name="upload" value="1" />
            <div class='fileupload fileupload-new' data-provides='fileupload'>
                <input type="hidden" />
                <div class="input-append">
                    <div class="uneditable-input span3">
                        <i class="fa-file fileupload-exists"></i><span class="fileupload-preview"></span>
                    </div>
                    <span class="btn btn-file">
                        <span class="fileupload-new">{{ __('buttons.select_file') }}</span>
                        <span class='fileupload-exists'>{{ __('buttons.change') }}</span>
                        <x-globals::forms.file :bare="true" name="file" />
                    </span>
                    <x-globals::forms.button link="#" type="secondary" data-dismiss="fileupload">{{ __('buttons.remove') }}</x-globals::forms.button>
                </div>
            </div>
        </div>

        <x-globals::forms.button submit type="primary" name="upload">{{ __('buttons.upload') }}</x-globals::forms.button>
    </form>

    <div class="clear"></div>
</div>

<div class="mediamgr_content">
    <ul id='medialist' class='listfile'>
        @foreach($tpl->get('files') as $file)
            <li class="{{ $file['moduleId'] }}">
                <x-globals::elements.dropdown style="float:right;">
                    <li class="nav-header border">{{ __('subtitles.file') }}</li>
                    <li><a href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}" target="_blank">{{ __('links.download') }}</a></li>

                    @if($login::userIsAtLeast($roles::$editor))
                        <li><a href="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}?delFile={{ $file['id'] }}" class="delete"><x-global::elements.icon name="delete" /> {{ __('links.delete') }}</a></li>
                    @endif
                </x-globals::elements.dropdown>

                <a class="cboxElement" href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}" target="_blank">
                    @if(in_array(strtolower($file['extension']), $tpl->get('imgExtensions')))
                        <img style='max-height: 50px; max-width: 70px;' src="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}" alt="" />
                    @else
                        <div style="font-size:50px; margin-bottom:10px;">
                            <x-global::elements.icon name="description" />
                        </div>
                    @endif
                    <span class="filename">{{ $file['realName'] }}</span>
                </a>
            </li>
        @endforeach
        <br class="clearall" />
    </ul>
</div>

@if(count($tpl->get('files')) == 0)
    <x-globals::elements.empty-state headline="{{ __('text.no_files') }}">
        <x-slot:icon>
            {!! file_get_contents(ROOT . '/dist/images/svg/undraw_image__folder_re_hgp7.svg') !!}
        </x-slot:icon>
    </x-globals::elements.empty-state>
@endif

<div style='clear:both'>&nbsp;</div>

<script type='text/javascript'>
    leantime.replaceSVGColors();
</script>
