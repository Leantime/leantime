@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentUrlPath = BASE_URL . '/' . str_replace('.', '/', Frontcontroller::getCurrentRoute());
@endphp

<x-global::layout.page-header icon="fa fa-briefcase">
    {!! __('headlines.my_projects') !!}
</x-global::layout.page-header>
