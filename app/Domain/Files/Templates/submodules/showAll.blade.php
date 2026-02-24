@php
    use Leantime\Core\Controller\Frontcontroller;

    $module = Frontcontroller::getModuleName('');
    $action = Frontcontroller::getActionName('');
    $maxSize = \Leantime\Core\Files\FileManager::getMaximumFileUploadSize();
    $moduleId = $_GET['id'] ?? '';
@endphp

<div id="fileManager">

    {!! $tpl->displayNotification() !!}

    <div class="uploadWrapper">

        <x-globals::forms.button link="javascript:void(0);" type="secondary" id="cancelLink" style="display:none;">{{ __('links.cancel') }}</x-globals::forms.button>
        <div class="extra" style="margin-top:5px;"></div>
        <div class="fileUploadDrop">
            <p><i>{{ __('text.drop_files') }}</i></p>
            <div class="file-upload-input" style="margin:auto;  display:inline-block"></div>
            <a href="javascript:void(0);" id="webcamClick">{!! __('label.webcam') !!}</a>
            <a href="javascript:void(0);" id="screencaptureLink">{!! __('label.screen_recording') !!}</a>
        </div>

        <!-- Progress bar #1 -->
        <div class="input-progress"></div>

        <div class="input-error"></div>

        <form id="upload-form"></form>

    </div>

    <div class="mediamgr">

        <div class="mediamgr_content">

            <ul id="medialist" class="listfile">
                @foreach($tpl->get('files') as $file)
                    <li class="file-module-{{ $file['moduleId'] }}">
                        <x-globals::elements.dropdown style="float:right;">
                            <li class="nav-header">{{ __('subtitles.file') }}</li>
                            <li><a target="_blank" href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}">{{ __('links.download') }}</a></li>

                            @if($login::userIsAtLeast($roles::$editor))
                                <li><a href="{{ BASE_URL }}/files/showAll?delFile={{ $file['id'] }}" class="delete deleteFile"><i class="fa fa-trash"></i> {{ __('links.delete') }}</a></li>
                            @endif
                        </x-globals::elements.dropdown>
                        <a class="imageLink" data-ext="{{ $file['extension'] }}" href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}">
                            @if(in_array(strtolower($file['extension']), $tpl->get('imgExtensions') ?? []))
                                <img style="max-height: 50px; max-width: 70px;" src="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}" alt="" />
                            @else
                                <img style="max-height: 50px; max-width: 70px;" src="{{ BASE_URL }}/dist/images/doc.png" />
                            @endif
                            <span class="filename">{{ substr($file['realName'], 0, 10) . '(...).' . $file['extension'] }}</span>
                        </a>
                    </li>
                @endforeach

            <br class="clearall" />
            </ul>

            <br class="clearall" />

        </div><!--mediamgr_content-->

        <br class="clearall" />
    </div><!--mediamgr-->
</div>



<script type="text/javascript">
    jQuery(document).ready(function(){

        jQuery('#widgetAction').click(function(){
            jQuery('.widgetList').toggle();
        });
        jQuery('#widgetAction2').click(function(){
            jQuery('.widgetList2').toggle();
        });
    });
</script>


    <script type="text/javascript">
        jQuery(document).ready(function(){

            let modalTypes = ["jpg", "jpeg", "png", "gif", "apng", "webp", "avif"];

            jQuery(".imageLink").each(function(i) {
                let ext = jQuery(this).attr("data-ext");
                if(modalTypes.includes(ext)) {
                    jQuery(this).nyroModal();
                }
            });

            //Replaces data-rel attribute to rel.
            //We use data-rel because of w3c validation issue
            jQuery('a[data-rel]').each(function() {
                jQuery(this).attr('rel', jQuery(this).data('rel'));
            });

            @if(isset($_GET['modalPopUp']))
                jQuery('#medialist a.imageLink').on("click", function(event){

                    event.preventDefault();
                    event.stopImmediatePropagation();

                    var url = jQuery(this).attr("href");

                    //File picker upload callback from tinymce
                    window.filePickerCallback(url, {text: "file"});

                    jQuery.nmTop().close();
                });

            @endif

            // Media Filter
            jQuery('#mediafilter a').on("click", function(){

                var filter = (jQuery(this).attr('href') != 'all')? '.'+jQuery(this).attr('href') : '*';
                jQuery('#medialist').isotope({ filter: filter });

                jQuery('#mediafilter li').removeClass('current');
                jQuery(this).parent().addClass('current');

                return false;
            });

            jQuery(".deleteFile").nyroModal();


        });
    </script>


<script>
jQuery(document).ready(function(){

    if (typeof uppy === 'undefined') {


    const uppy = new Uppy.Uppy({
            debug: false,
            autoProceed: true,
            restrictions: {
                maxFileSize: {{ $maxSize }}
            }
    });

    uppy.use(Uppy.DropTarget, { target: '#fileManager' });

    uppy.use(Uppy.FileInput, {
            target: '.file-upload-input',
        pretty: true,
        locale: {
                strings: {
                    chooseFiles: ' Browse',
                }
        }
    });
    uppy.use(Uppy.XHRUpload, {
        endpoint: '{{ BASE_URL }}/api/files?module={{ $module }}&moduleId={{ $moduleId }}',
        formData: true,
    });

    uppy.use(Uppy.StatusBar, {
        target: '.input-progress',
        hideUploadButton: true,
        hideAfterFinish: false,
    });

    uppy.use(Uppy.Form, { target: '#upload-form' });
    uppy.use(Uppy.Compressor);

    // Upload
    uppy.on("restriction-failed", (file, error) => {

        jQuery(".input-error").html("<span class='label-important'>"+error+"</span>");
        return false
    });

    uppy.on('upload-success', (file, response) => {

        jQuery(".input-error").text('');

        response = response.body;

        if(response.hasOwnProperty("moduleId")){

            let html = '<li class="file-module-'+response.moduleId+'">' +
                            '<div class="dropdown" style="float:right;">' +
                                '<a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle ticketDropDown">' +
                                    '<i class="fa fa-ellipsis-v" aria-hidden="true"></i>' +
                                '</a>' +
                                '<ul class="dropdown-menu">' +
                                    '<li class="nav-header">{{ __("subtitles.file") }}</li>' +
                                    '<li><a target="_blank" href="{{ BASE_URL }}/files/get?module='+ response.module +'&encName='+ response.encName +'&ext='+ response.extension +'&realName='+ response.realName +'">{{ str_replace("'", '"', __("links.download")) }}</a></li>'+
                                    @if($login::userIsAtLeast($roles::$editor))
                                        '<li><a href="{{ BASE_URL }}/files/showAll?delFile='+ response.fileId +'" class="delete deleteFile"><i class="fa fa-trash"></i> {{ str_replace("'", '"', __("links.delete")) }}</a></li>'+
                                    @endif
                                '</ul>'+
                            '</div>'+
                            '<a class="imageLink" href="{{ BASE_URL }}/files/get?module='+ response.module +'&encName='+ response.encName +'&ext='+ response.extension +'&realName='+ response.realName +'">'+
                                '<img style="max-height: 50px; max-width: 70px;" src="{{ BASE_URL }}/files/get?module='+ response.module +'&encName='+ response.encName +'&ext='+ response.extension +'&realName='+ response.realName +'" alt="" />'+

                                '<span class="filename">'+response.realName+'.</span>'+
                            '</a>'+
                        '</li>';

                 jQuery("#medialist").append(html);
        }

    });

    jQuery("#webcamClick").click(function(){
        jQuery(".uploadWrapper .extra").css("display", "flex");
        uppy.use(Uppy.Webcam, { target: '.extra' });
        jQuery("#cancelLink").show();
    });

    jQuery("#screencaptureLink").click(function(){
        jQuery(".uploadWrapper .extra").css("display", "flex");
        uppy.use(Uppy.ScreenCapture,
            {
                displayMediaConstraints: {
                    video: {
                        width: 1280,
                        height: 720,
                        frameRate: {
                            ideal: 3,
                            max: 5,
                        },
                        cursor: 'motion',
                        displaySurface: 'window',
                    },
                },
                target: '.extra'
            });
        jQuery("#cancelLink").show();
    });



    jQuery("#cancelLink").click(function(){
        const instance = uppy.getPlugin('Webcam');
        if(instance) {
            uppy.removePlugin(instance);
        }

        const instance2 = uppy.getPlugin('ScreenCapture');
        if(instance2) {
            uppy.removePlugin(instance2);
        }

        jQuery("#cancelLink").hide();

        jQuery(".uploadWrapper .extra").css("display", "none");


    });

    }

});
</script>
