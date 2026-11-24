@extends($layout)

@section('content')

@php
    $maxSize = \Leantime\Core\Files\FileManager::getMaximumFileUploadSize();
@endphp

<div id="fileManager">

    {!! $tpl->displayNotification() !!}

    <h2>{{ __('headlines.upload_csv_file') }}</h2>
    <p>{!! sprintf(__('text.csv_upload_intro'), \Leantime\Domain\CsvImport\Services\CsvImport::DOCS_URL) !!}</p>
    <br /><br/>
    <div class="uploadWrapper" style="width:100%">

        <form id="upload-form">

        <div class="extra" style="margin-top:5px;"></div>
        <div class="fileUploadDrop">
            <p><i>{!! __('text.drop_files') !!}</i></p>
            <div class="file-upload-input" style="margin:auto;  display:inline-block"></div>
        </div>

        <!-- Progress bar #1 -->
        <div class="input-progress"></div>

        <div class="input-error"></div>

        </form>

    </div>

</div>

@once
<script>

    if (typeof uppy === 'undefined') {


        const uppy = new Uppy.Uppy({
            debug: false,
            autoProceed: true,
            restrictions: {
                maxFileSize: {{ $maxSize }},
                // The reader goes by the file's content; this only keeps the picker to files it can read.
                allowedFileTypes: ['.csv', '.tsv', '.txt', '.xlsx']
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

            jQuery(".input-error").html("<span class='label-important'>"+error+"</span>");
            return false
        });

        uppy.on('upload-success', (file, response) => {

            jQuery(".input-error").text('');

            // Show what was found in the file before anything is mapped.
            window.location.href = "{{ BASE_URL }}/csvImport/file?integrationId="+response.body.id;

        });


        uppy.on('upload-error', (file, error, response) => {

            const message = response && response.body && response.body.error ? response.body.error : error.message;
            jQuery(".input-error").html(jQuery("<span class='label-important'></span>").text(message));



            return false
        });


    }

</script>
@endonce

@endsection
