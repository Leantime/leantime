@php
    $maxSize = \Leantime\Core\Files\FileManager::getMaximumFileUploadSize();
@endphp

<div id="fileManager">

    {!! $tpl->displayNotification() !!}

    <h2>Upload CSV file</h2>
    <p>You can upload CSVs to import or update Tasks, Projects, Goals. <a href="https://support.leantime.io/importing-data-via-csv" target="_blank">Check our documentation</a> to learn more about the formatting and to download templates</p>
    <br /><br/>
    <div class="uploadWrapper tw:w-full">

        <form id="upload-form">

        <div class="extra tw:mt-1"></div>
        <div class="fileUploadDrop">
            <p><i>{{ $tpl->__('text.drop_files') }}</i></p>
            <div class="file-upload-input tw:mx-auto tw:inline-block"></div>
        </div>

        <!-- Progress bar #1 -->
        <div class="input-progress"></div>

        <div class="input-error"></div>

        </form>

    </div>

</div>

<script>

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
            endpoint: '{{ BASE_URL }}/csvImport/upload',
            formData: true,
            fieldName: 'file'
        });

        uppy.use(Uppy.StatusBar, {
            target: '.input-progress',
            hideUploadButton: false,
            hideAfterFinish: false,
        });

        uppy.use(Uppy.Form, { target: '#upload-form' });

        // Upload
        uppy.on("restriction-failed", (file, error) => {
            var span = jQuery("<span class='label-important'></span>").text(error);
            jQuery(".input-error").empty().append(span);
            return false
        });

        uppy.on('upload-success', (file, response) => {
            jQuery(".input-error").text('');
            window.location.href = "{{ BASE_URL }}/connector/integration?provider=csv_importer&step=entity&integrationId="+response.body.id;
        });

        uppy.on('upload-error', (file, error, response) => {
            var errorMsg = response && response.body && response.body.error ? response.body.error : 'Unknown error';
            var span = jQuery("<span class='label-important'></span>").text("There is a problem with your CSV file: " + errorMsg);
            jQuery(".input-error").empty().append(span);
            return false
        });
    }

</script>
