@extends($layout)

@section('content')
    <?php
    // use Leantime\Core\Controller\Frontcontroller;
    // use Leantime\Core\Fileupload;
    
    // $module = 'project';
    // $action = Frontcontroller::getActionName('');
    // $maxSize = Fileupload::getMaximumFileUploadSize();
    $moduleId = session('currentProject');
    ?>

    {{-- <div class="pageheader">
        <div class="pageicon"><span class="fa fa-fw fa-file"></span></div>
        <div class="pagetitle">
            <h5>{{ session('currentProjectName') }}</h5>
            <h1>{!! __('headlines.files') !!}</h1>
        </div>
    </div> --}}

    <x-files::file-manager :module="'project'" :moduleId="$moduleId" :fetch="true" />
@endsection
