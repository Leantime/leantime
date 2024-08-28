@extends($layout)

@section('content')

    <div class="projectSteps">
        <div class="progressWrapper">
            <div class="progress">
                <div
                    id="progressChecklistBar"
                    class="progress-bar progress-bar-success tx-transition"
                    role="progressbar"
                    aria-valuenow="0"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    style="width: 100%"
                ><span class="sr-only">88%</span></div>
            </div>


            <div class="step complete" style="left: 12%;">
                <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                    <span class="innerCircle"></span>
                    <span class="title">
                    <i class="fa-regular fa-circle-check"></i> Step 1
                </span>
                </a>
            </div>

            <div class="step complete" style="left: 37%;">
                <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                    <span class="innerCircle"></span>
                    <span class="title">
                    <i class="fa-regular fa-circle-check"></i> Step 2
                </span>
                </a>
            </div>

            <div class="step complete" style="left: 62%;">
                <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                    <span class="innerCircle"></span>
                    <span class="title">
                    <i class="fa-regular fa-circle-check"></i> Step 3
                </span>
                </a>
            </div>

            <div class="step complete" style="left: 88%;">
                <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                    <span class="innerCircle"></span>
                    <span class="title">
                    <i class="fa-regular fa-circle-check"></i> Step 4
                </span>
                </a>
            </div>

        </div>
    </div>
    <br /><br /><br />



<div class="regcontent">

    <form id="resetPassword" action="" method="post">

        <input type="hidden" name="step" value="5"/>
        <input type="hidden" name="complete" value="1"/>

        {{  $tpl->displayInlineNotification() }}

        <x-global::elements.undrawSvg image="undraw_adventure_map_hnin.svg" headline="You are now one step closer to getting things done!" maxWidth="60%" maxHeight="300px"></x-global::elements.undrawSvg>
        <br /><br />
        <p>As you get ready to go into the system, start thinking about what you want to accomplish.<br />
            <br />
            Studies found that people who put down their goals were <strong>42% more likely to achieve those goals</strong> and,
            in the work place, they had higher productivity, improved focus, and were better motivated.<br />
            <br />
            We’re excited to be your partners in doing more than just “work” but in making meaningful impact. <br /><br /></p>

        <br />
        <input type="submit" name="createAccount" value="Complete Sign up" />


    </form>

</div>

@endsection
