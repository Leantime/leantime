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
                style="width: 37%"
            ><span class="sr-only">50%</span></div>
        </div>


        <div class="step complete" style="left: 12%;">
            <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                <span class="innerCircle"></span>
                <span class="title">
                    <i class="fa-regular fa-circle-check"></i> Step 1
                </span>
            </a>
        </div>

        <div class="step current" style="left: 37%;">
            <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                <span class="innerCircle"></span>
                <span class="title">
                    <i class="fa-regular fa-circle"></i> Step 2
                </span>
            </a>
        </div>

        <div class="step " style="left: 62%;">
            <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                <span class="innerCircle"></span>
                <span class="title">
                    <i class="fa-regular fa-circle"></i> Step 3
                </span>
            </a>
        </div>

        <div class="step " style="left: 88%;">
            <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                <span class="innerCircle"></span>
                <span class="title">
                    <i class="fa-regular fa-circle"></i> Step 4
                </span>
            </a>
        </div>

    </div>
</div>
<br /><br /><br />


<h2>{{ __("text.point_to_where_it_hurts") }}</h2>


<div class="regcontent">

    <form id="resetPassword" action="" method="post">
        <input type="hidden" name="step" value="2" />

        {{  $tpl->displayInlineNotification() }}

        <p>{{ __("text.challenges_brings_you_here") }}<br /><br /></p>


        <x-global::forms.select-button :selected="false" :id="''" :name="'challenge'" :value="'organization'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji">🤯</span> I have too many things to manage and organize
        </x-global::forms.select-button>

        <x-global::forms.select-button :selected="false" :id="''" :name="'challenge'" :value="'progress'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji">📉</span> I don't feel like I'm making progress
        </x-global::forms.select-button>

        <x-global::forms.select-button :selected="false" :id="''" :name="'challenge'" :value="'adoption'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji"> 👥</span> I need a tool that my team will actually use
        </x-global::forms.select-button>

        <x-global::forms.select-button :selected="false" :id="''" :name="'challenge'" :value="'price'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji">💰</span> My current tool is too expensive
        </x-global::forms.select-button>

        <x-global::forms.select-button :selected="false" :id="''" :name="'challenge'" :value="'brain'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji"> 🧠</span> The other tools don't organize the way my brain does
        </x-global::forms.select-button>

        <x-global::forms.select-button :selected="false" :id="''" :name="'challenge'" :value="'collaboration'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji"> 🥸</span> I was just invited to collaborate
        </x-global::forms.select-button>
        <br /> <br />
        <input type="submit" name="createAccount" value="<?php echo $tpl->language->__("buttons.next"); ?>" />


    </form>

</div>

@endsection
