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
                style="width: 85%"
            ><span class="sr-only">800%</span></div>
        </div>


        <div class="step complete" style="left: 15%;">
            <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                <span class="innerCircle"></span>
                <span class="title">
                    <i class="fa-regular fa-circle-check"></i> Step 1
                </span>
            </a>
        </div>

        <div class="step complete" style="left: 50%;">
            <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                <span class="innerCircle"></span>
                <span class="title">
                    <i class="fa-regular fa-circle-check"></i> Step 2
                </span>
            </a>
        </div>

        <div class="step current" style="left: 85%;">
            <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                <span class="innerCircle"></span>
                <span class="title">
                    <i class="fa-regular fa-circle"></i> Step 3
                </span>
            </a>
        </div>

    </div>
</div>
<br /><br /><br />


<h2 style="font-size:var(--font-size-xxl);">🥷 What did you say you do here?</h2>


<div class="regcontent">

    <form id="resetPassword" action="" method="post">

        <input type="hidden" name="step" value="3"/>

        {{  $tpl->displayInlineNotification() }}

        <p>Tell us about your main challenge rightnow<br /><br /></p>


        <x-global::selectable :selected="false" :id="''" :name="'function'" :value="'ic'" :label="''" class="tw-w-full tw-text-left">
            <span class="tw-text-2xl">🤓</span> I manage only my own work
        </x-global::selectable>

        <x-global::selectable :selected="false" :id="''" :name="'function'" :value="'pm'" :label="''" class="tw-w-full tw-text-left">
            <span class="tw-text-2xl">🧙</span> I manage a project for a team and my own work
        </x-global::selectable>

        <x-global::selectable :selected="false" :id="''" :name="'function'" :value="'pgm'" :label="''" class="tw-w-full tw-text-left">
            <span class="tw-text-2xl"> 😸</span> I manage multiple projects for several teams
        </x-global::selectable>

        <x-global::selectable :selected="false" :id="''" :name="'function'" :value="'business'" :label="''" class="tw-w-full tw-text-left">
            <span class="tw-text-2xl">♟️</span> I manage a business (unit)
        </x-global::selectable>

        <x-global::selectable :selected="false" :id="''" :name="'function'" :value="'no_answer'" :label="''" class="tw-w-full tw-text-left">
            <span class="tw-text-2xl"> 🤷</span> Heck, if I knew
        </x-global::selectable>

        <input type="submit" name="createAccount" value="Complete Sign up" />


    </form>

</div>

@endsection
