<h4 class="widgettitle title-light">
    <i class="fa-solid fa-mountain"></i>
    {{ empty($bigRock["title"]) ? __('label.create_bigrock') : __('label.bigrock') }} {{  $bigRock["title"] }}</h4>

{!!  $tpl->displayNotification();  !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/goalcanvas/bigRock/{{ !empty($bigRock['id']) ? $bigRock['id'] : '' }}">

    <p>{!! __('text.what_are_bigrocks') !!}</p>
    <br />
    <label>{{ __('label.big_rock_description') }}</label>
    <input type="text" name="title" id="wikiTitle" value="{{ $bigRock['title']}}" style="width:100%;"/><br />

    <br />
    <div class="row">
        <div class="col-md-6">
            <input type="submit" value="{{ __('buttons.save') }}" id="saveBtn"/>
        </div>
        <div class="col-md-6 align-right padding-top-sm">

        </div>
    </div>

</form>

<script>
    jQuery(document).ready(function(){

        @if (isset($_GET['closeModal']))
            jQuery.nmTop().close();
        @endif

       if(jQuery("#wikiTitle").val().length >= 2) {
           jQuery("#saveBtn").removeAttr("disabled");
       }else{
           jQuery("#saveBtn").attr("disabled", "disabled");
       }

        jQuery("#wikiTitle").keypress(function(){

            if(jQuery("#wikiTitle").val().length >= 2) {
                jQuery("#saveBtn").removeAttr("disabled");
            }else{
                jQuery("#saveBtn").attr("disabled", "disabled");
            }
        })
    });
</script>

