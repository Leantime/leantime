@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentUrlPath = BASE_URL . '/' . str_replace('.', '/', Frontcontroller::getCurrentRoute());
@endphp

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pageicon">
        <span class="fa fa fa-briefcase"></span>
    </div>
    <div class="pagetitle">

        <h1>{!! __('headlines.my_projects') !!}



        </h1>

    </div>
    @dispatchEvent('beforePageHeaderClose')
</div><!--pageheader-->
@dispatchEvent('afterPageHeaderClose')
