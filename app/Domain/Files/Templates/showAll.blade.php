<div id="fileManager">
    <div>

        {!! $tpl->displayNotification() !!}

        <div class="mediamgr">
            <div class="mediamgr_left">
                <div class="mediamgr_category">

                    <form action="{{ BASE_URL }}/files/showAll{{ isset($_GET['modalPopUp']) ? '?modalPopUp=true' : '' }}" method="post" enctype="multipart/form-data" class="fileModal">
                        <div class="par f-left" style="margin-right: 15px;">

                            <div class="fileupload fileupload-new" data-provides="fileupload">
                                <input type="hidden" />
                                <div class="input-append">
                                    <div class="uneditable-input span3">
                                        <i class="fa-file fileupload-exists"></i><span class="fileupload-preview"></span>
                                    </div>
                                    <span class="btn btn-file">
                                    <span class="fileupload-new">Select file</span>
                                    <span class="fileupload-exists">Change</span>
                                    <x-global::forms.file :bare="true" name="file" />
                                </span>

                                    <x-global::button link="#" type="secondary" class="fileupload-exists" data-dismiss="fileupload">Remove</x-global::button>
                                </div>
                            </div>
                        </div>

                        <x-global::button submit type="primary" name="upload">{{ __('UPLOAD') }}</x-global::button>

                    </form>

                </div>

                <div class="mediamgr_content">

                    <ul id="medialist" class="listfile">
                        @foreach($tpl->get('files') as $file)
                            <li class="{{ $file['moduleId'] }}">
                                <x-global::elements.dropdown style="float:right;">
                                    <li class="nav-header border">{{ __('subtitles.file') }}</li>
                                    <li><a target="_blank" href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}">{{ __('links.download') }}</a></li>

                                    @if($login::userIsAtLeast($roles::$editor))
                                        <li><a href="{{ BASE_URL }}/files/showAll?delFile={{ $file['id'] }}" class="delete deleteFile"><i class="fa fa-trash"></i> {{ __('links.delete') }}</a></li>
                                    @endif
                                </x-global::elements.dropdown>
                                <a class="imageLink" href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}">
                                    @if(in_array(strtolower($file['extension']), $tpl->get('imgExtensions')))
                                        <img style="max-height: 50px; max-width: 70px;" src="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}" alt="" />
                                    @else
                                        <img style="max-height: 50px; max-width: 70px;" src="{{ BASE_URL }}/dist/images/thumbs/doc.png" />
                                    @endif
                                    <span class="filename">{{ substr($file['realName'], 0, 10) . '(...).' . $file['extension'] }}</span>
                                </a>
                            </li>
                        @endforeach
                        <br class="clearall" />
                    </ul>
                    <br class="clearall" />

                </div><!--mediamgr_content-->

            </div><!--mediamgr_left -->


            <br class="clearall" />
        </div><!--mediamgr-->


    </div>
</div>



<script type="text/javascript">
    jQuery(document).ready(function(){

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
