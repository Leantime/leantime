@extends($layout)

@section('content')

@php
    $maxSize = \Leantime\Core\Files\FileManager::getMaximumFileUploadSize();
@endphp

<x-global::layout.page-header icon="fa-solid fa-circle-nodes">
    {{ __('headlines.integrations') }} / {{ $provider->name }}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>
        @include('connector::submodules.importProgress', ['currentStep' => 'connect'])

        {!! $tpl->displayNotification() !!}

        <x-global::elements.section-header as="h4">{{ __('headlines.import_upload') }}</x-global::elements.section-header>
        <p class="lt-lead">{{ __('text.import_upload_intro') }}</p>

        {{-- The whole box takes a dropped file; the button opens the file picker. --}}
        <form id="upload-form">
            <div class="fileUploadDrop fileUploadDrop--large" id="fileManager">
                <i class="fa fa-file-arrow-up fileUploadDrop-icon" aria-hidden="true"></i>
                <p class="fileUploadDrop-title">{{ __('text.import_drop_file') }}</p>
                <p class="fileUploadDrop-or">{{ __('text.import_drop_or') }}</p>
                <div class="file-upload-input"></div>
                <p class="fileUploadDrop-hint">{{ __('text.import_file_types') }}</p>
            </div>
            <div class="input-progress"></div>
            <div class="input-error" role="alert"></div>
        </form>

        <p class="lt-cell-note lt-import-help">{{ __('text.import_export_help') }} <a href="{{ \Leantime\Domain\CsvImport\Services\CsvImport::DOCS_URL }}" target="_blank" rel="noopener">{{ __('links.import_export_help') }}</a></p>
    </x-global::layout.content-box>
</div>

@once
<script>
    (function () {
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
            locale: { strings: { chooseFiles: {!! json_encode(' '.__('buttons.import_choose_file')) !!} } }
        });
        uppy.use(Uppy.XHRUpload, {
            endpoint: '{{ BASE_URL }}/csvImport/upload',
            formData: true,
            fieldName: 'file'
        });
        uppy.use(Uppy.StatusBar, { target: '.input-progress', hideUploadButton: false, hideAfterFinish: false });
        uppy.use(Uppy.Form, { target: '#upload-form' });

        uppy.on('restriction-failed', (file, error) => {
            jQuery('.input-error').html(jQuery("<span class='label-important'></span>").text(error.message));
        });

        // Next: what was found in the file, before anything is matched.
        uppy.on('upload-success', (file, response) => {
            jQuery('.input-error').text('');
            window.location.href = '{{ BASE_URL }}/csvImport/preview?integrationId=' + response.body.id;
        });

        uppy.on('upload-error', (file, error, response) => {
            const message = response && response.body && response.body.error ? response.body.error : error.message;
            jQuery('.input-error').html(jQuery("<span class='label-important'></span>").text(message));
            uppy.cancelAll();
        });
    })();
</script>
@endonce

@endsection
