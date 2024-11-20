<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:300px' class='svgContainer'>
            <?php echo file_get_contents(ROOT.'/dist/images/svg/undraw_design_data_khdb.svg');
echo '</div>'; ?>
            <br />
            <h1>{{ __("headlines.$canvasName.welcome_to_board") }}</h1><br />
            {{ __("text.$canvasName.helper_content") }}
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()">{{ __("links.close") }}</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('{{ $canvasName }}Canvas')">
                {{ __("links.close_dont_show_again") }}</a>
        </div>
    </div>


</div>
