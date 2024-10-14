<x-global::content.modal.modal-buttons/>

    <div id="fileManager">
        <div>

        @displayNotification()

            <div class='mediamgr'>
                <div class='mediamgr_left'>
                    <div class="mediamgr_category">

                    <x-global::content.modal.form action="{{ BASE_URL }}/files/showAll{{ isset($_GET['modalPopUp']) ? '?modalPopUp=true' : '' }}" method='post' enctype="multipart/form-data" class="fileModal" >
                        <div class="par f-left" style="margin-right: 15px;">

                                <div class='fileupload fileupload-new' data-provides='fileupload'>
                                    <input type="hidden" />
                                    <div class="input-append">
                                        <div class="uneditable-input span3">
                                            <i class="fa-file fileupload-exists"></i><span
                                                class="fileupload-preview"></span>
                                        </div>
                                        <span class="btn btn-file">
                                            <span class="fileupload-new">Select file</span>
                                            <span class='fileupload-exists'>Change</span>
                                            <input type='file' name='file' />
                                        </span>

                                        <a href='#' class='btn fileupload-exists' data-dismiss='fileupload'>Remove</a>
                                    </div>
                                </div>
                            </div>

                        <input type="submit" name="upload" class="button" value="{{ __("UPLOAD") }}" />

                    </x-global::content.modal.form>

                    </div>

                    <div class="mediamgr_content">

                        <ul id='medialist' class='listfile'>
                            <?php foreach ($tpl->get('files') as $file) : ?>
                            <li class="<?php echo $file['moduleId']; ?>">
                                <x-global::content.context-menu
                                    label-text="<i class='fa fa-ellipsis-v' aria-hidden='true'></i>" contentRole="link"
                                    position="right" align="start" class="inlineDropDownContainer dropright"
                                    style="float:right;">

                                    <!-- Menu Header -->
                            <li class="nav-header">{{ __('subtitles.file') }}</li>

                            <!-- Download File Menu Item -->
                            <x-global::actions.dropdown.item variant="link"
                                href="{{ BASE_URL }}/files/get?module={{ $file['module'] }}&encName={{ $file['encName'] }}&ext={{ $file['extension'] }}&realName={{ $file['realName'] }}"
                                target="_blank">
                                {{ __('links.download') }}
                            </x-global::actions.dropdown.item>

                            <!-- Conditional Delete File Menu Item -->
                            @if ($login::userIsAtLeast($roles::$editor))
                                <x-global::actions.dropdown.item variant="link"
                                    href="{{ BASE_URL }}/files/showAll?delFile={{ $file['id'] }}"
                                    class="delete deleteFile">
                                    <i class="fa fa-trash"></i> {{ __('links.delete') }}
                                </x-global::actions.dropdown.item>
                            @endif

                            </x-global::content.context-menu>

                            <a class="imageLink"
                                href="<?= BASE_URL ?>/files/get?module=<?php echo $file['module']; ?>&encName=<?php echo $file['encName']; ?>&ext=<?php echo $file['extension']; ?>&realName=<?php echo $file['realName']; ?>">
                                <?php if (in_array(strtolower($file['extension']), $tpl->get('imgExtensions'))) :  ?>
                                <img style='max-height: 50px; max-width: 70px;'
                                    src="<?= BASE_URL ?>/files/get?module=<?php echo $file['module']; ?>&encName=<?php echo $file['encName']; ?>&ext=<?php echo $file['extension']; ?>&realName=<?php echo $file['realName']; ?>"
                                    alt="" />
                                <?php else : ?>
                                <img style='max-height: 50px; max-width: 70px;'
                                    src='<?= BASE_URL ?>/dist/images/thumbs/doc.png' />
                                <?php endif; ?>
                                <span class="filename"><?php echo substr($file['realName'], 0, 10) . '(...).' . $file['extension']; ?></span>
                            </a>
                            </li>
                            <?php endforeach; ?>
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
        jQuery(document).ready(function() {

            //Replaces data-rel attribute to rel.
            //We use data-rel because of w3c validation issue
            jQuery('a[data-rel]').each(function() {
                jQuery(this).attr('rel', jQuery(this).data('rel'));
            });

            //jQuery("#medialist a").colorbox();

            <?php if (isset($_GET['modalPopUp'])) { ?>
            jQuery('#medialist a.imageLink').on("click", function(event) {

                event.preventDefault();
                event.stopImmediatePropagation();

                var url = jQuery(this).attr("href");

                //File picker upload callback from tinymce
                window.filePickerCallback(url, {
                    text: "file"
                });

                jQuery.nmTop().close();
            });

            <?php } ?>

            // Media Filter
            jQuery('#mediafilter a').on("click", function() {

                var filter = (jQuery(this).attr('href') != 'all') ? '.' + jQuery(this).attr('href') : '*';
                jQuery('#medialist').isotope({
                    filter: filter
                });

                jQuery('#mediafilter li').removeClass('current');
                jQuery(this).parent().addClass('current');

                return false;
            });



        });
    </script>
