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
                    style="width: 88%"
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

            <div class="step current" style="left: 88%;">
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


<h2>🥷 Tell us about your powers</h2>

<div class="regcontent">

    <form id="resetPassword" action="" method="post">

        <input type="hidden" name="step" value="4"/>

        {{  $tpl->displayInlineNotification() }}

        <p>What unique qualities do you have that help you reach your goals?<br /><br /></p>

        <x-global::forms.select-button :selected="false" :id="''" :name="'function'" :value="'focused'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji">🤓</span> My intense focus and meticulous attention to detail
        </x-global::forms.select-button>

        <x-global::forms.select-button :selected="false" :id="''" :name="'function'" :value="'teamplayer'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji">🧙</span> My ability to bring my team's diverse strength together
        </x-global::forms.select-button>

        <x-global::forms.select-button :selected="false" :id="''" :name="'function'" :value="'innovator'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji"> 💡</span> My innovative thinking that powers creative solutions
        </x-global::forms.select-button>

        <x-global::forms.select-button :selected="false" :id="''" :name="'function'" :value="'strategist'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji">🧭</span> My broad perspective and strategic vision that guide my decisions
        </x-global::forms.select-button>

        <x-global::forms.select-button :selected="false" :id="''" :name="'function'" :value="'emotional'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji">💖</span> My empathy and EQ that help me deeply understand my team
        </x-global::forms.select-button>


        <x-global::forms.select-button :selected="false" :id="''" :name="'function'" :value="'no_answer'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji"> 🤷</span> Heck, if I knew
        </x-global::forms.select-button>
        <br /> <br />
        <input type="submit" name="createAccount"  value="<?php echo $tpl->language->__("buttons.next"); ?>" />


    </form>

</div>

@endsection
