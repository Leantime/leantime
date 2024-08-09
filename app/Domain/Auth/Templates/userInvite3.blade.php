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
                style="width: 62%"
            ><span class="sr-only">62%</span></div>
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

        <div class="step current" style="left: 62%;">
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


<h2>Managing work is about more than long To Do lists,<br />
    it's about reaching goals and making impact</h2>

<div class="regcontent">

    <form id="resetPassword" action="" method="post">
        <input type="hidden" name="step" value="3" />

        {{  $tpl->displayInlineNotification() }}

        <p>
            How will your life change if you solve your current challenges?<br /><br /></p>


        <x-global::forms.select-button :selected="false" :id="''" :name="'impact'" :value="'accomplish'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji">🚀</span> I feel like I get to accomplish something
        </x-global::forms.select-button>

        <x-global::forms.select-button :selected="false" :id="''" :name="'impact'" :value="'manageable'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji">💪</span> Things will feel manageable
        </x-global::forms.select-button>

        <x-global::forms.select-button :selected="false" :id="''" :name="'impact'" :value="'teamWork'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji"> 👥</span>  My team will work better together
        </x-global::forms.select-button>

        <x-global::forms.select-button :selected="false" :id="''" :name="'impact'" :value="'safeMoney'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji">💰</span> I will safe money
        </x-global::forms.select-button>

        <x-global::forms.select-button :selected="false" :id="''" :name="'impact'" :value="'relateTasks'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji">🎯</span> I will be able to relate my tasks to the goals I have
        </x-global::forms.select-button>

        <x-global::forms.select-button :selected="false" :id="''" :name="'impact'" :value="'justWork'" :label="''" class="tw-w-full tw-text-left">
            <span class="emoji"> 🥸</span> I just work here
        </x-global::forms.select-button>
        <br /> <br />
        <input type="submit" name="createAccount" value="<?php echo $tpl->language->__("buttons.next"); ?>" />


    </form>

</div>

@endsection
