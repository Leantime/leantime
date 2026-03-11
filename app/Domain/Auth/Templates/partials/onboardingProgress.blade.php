<div class="projectSteps">
    <div class="progressWrapper">
        <x-globals::feedback.progress :value="$percentComplete" :max="100" id="progressChecklistBar" />
        <div class="step @if($current=='account') current @endif @if(in_array("account", $completed)) complete @endif tw:left-[12%]">
            <a href="javascript:void(0)" class="dropdown-toggle">
                <span class="innerCircle">
                    @if(in_array("account", $completed))
                        <x-globals::elements.icon name="check" style="color:var(--main-action-color); padding-left:3px;" />
                    @endif
                </span>
                <span class="title">
                    Account
                </span>
            </a>
        </div>

        <div class="step @if($current=='theme') current @endif @if(in_array("theme", $completed)) complete @endif tw:left-[37%]">
            <a href="javascript:void(0)" class="dropdown-toggle">
                <span class="innerCircle">
                    @if(in_array("theme", $completed))
                        <x-globals::elements.icon name="check" style="color:var(--main-action-color); padding-left:3px;" />
                    @endif
                </span>
                <span class="title">
                    Theme
                </span>
            </a>
        </div>

        <div class="step @if($current=='personalization') current @endif @if(in_array("personalization", $completed)) complete @endif tw:left-[62%]">
            <a href="javascript:void(0)" class="dropdown-toggle">
                <span class="innerCircle">
                    @if(in_array("personalization", $completed))
                        <x-globals::elements.icon name="check" style="color:var(--main-action-color); padding-left:3px;" />
                    @endif
                </span>
                <span class="title">
                    Personalization
                </span>
            </a>
        </div>

        <div class="step @if($current=='time') current @endif @if(in_array("time", $completed)) complete @endif tw:left-[88%]">
            <a href="javascript:void(0)" class="dropdown-toggle">
                <span class="innerCircle">
                    @if(in_array("time", $completed))
                        <x-globals::elements.icon name="check" style="color:var(--main-action-color); padding-left:3px;" />
                    @endif
                </span>
                <span class="title">
                    Routine
                </span>
            </a>
        </div>

    </div>
</div>
<br /><br /><br />
