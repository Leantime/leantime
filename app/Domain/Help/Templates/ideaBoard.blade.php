<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_new_ideas_jdea.svg') !!}
            </div>
            <h3 class="primaryColor">{!! __('headlines.welcome_to_idea_board') !!}</h3><br />
            <p>{!! __('text.idea_board_helper_content') !!}<br /></p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <x-global::forms.button tag="a" link="javascript:void(0);"  onclick="jQuery.nmTop().close()" contentRole="secondary">{!! __('links.close') !!}</x-global::forms.button><br />
        </div>
    </div>


</div>
