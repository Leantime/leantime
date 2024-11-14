@extends($layout)

@section('content')

    @include("auth::partials.onboardingProgress", ['percentComplete' => 100, 'current' => '', 'completed' => ['account', 'theme', 'personalization', 'time']])

<h2>🎉 Your Leantime journey is about to begin</h2>

<div class="regcontent">

    <form id="resetPassword" action="" method="post">

        <input type="hidden" name="step" value="5"/>
        <input type="hidden" name="complete" value="1"/>

        {{  $tpl->displayInlineNotification() }}



        <div class="row">
            <div class="col-md-6">
                <div class="ticketBox tw-p-[20px]">
                    <span class="fancyLink">Did you know?</span><br />
                    <span style="font-size:16px;">Setting Intentions has been shown to <strong>more than double the success rate</strong> of completing a task.</span>
                </div>
            </div>
            <div class="col-md-6">
                <x-global::undrawSvg image="undraw_adventure_map_hnin.svg" maxWidth="60%" maxHeight="300px"></x-global::undrawSvg>
            </div>
        </div>

        <p><br />From here, we'll help you turn your task list into a project and goals.
            Then we'll work<br /> together to identify your most important tasks so you can create some
            intentions<br />to get the work done.</p> <br />

        <br />
        <input type="submit" name="createAccount" value="Complete Sign up" />


    </form>

</div>

@endsection
