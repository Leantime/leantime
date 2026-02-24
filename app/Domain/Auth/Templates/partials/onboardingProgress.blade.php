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
                style="width: {{ $percentComplete }}%"
            ><span class="sr-only">{{ $percentComplete }}%</span></div>
        </div>
        <div class="step @if($current=='account') current @endif @if(in_array("account", $completed)) complete @endif" style="left: 12%;">
            <a href="javascript:void(0)" class="dropdown-toggle">
                <span class="innerCircle">
                    @if(in_array("account", $completed))
                        <i class="fa-solid fa-check" style="color:var(--main-action-color); padding-left:3px;"></i>
                    @endif
                </span>
                <span class="title">
                    Account
                </span>
            </a>
        </div>

        <div class="step @if($current=='theme') current @endif @if(in_array("theme", $completed)) complete @endif" style="left: 37%;">
            <a href="javascript:void(0)" class="dropdown-toggle">
                <span class="innerCircle">
                    @if(in_array("theme", $completed))
                        <i class="fa-solid fa-check" style="color:var(--main-action-color); padding-left:3px;"></i>
                    @endif
                </span>
                <span class="title">
                    Theme
                </span>
            </a>
        </div>

        <div class="step @if($current=='personalization') current @endif @if(in_array("personalization", $completed)) complete @endif" style="left: 62%;">
            <a href="javascript:void(0)" class="dropdown-toggle">
                <span class="innerCircle">
                    @if(in_array("personalization", $completed))
                        <i class="fa-solid fa-check" style="color:var(--main-action-color); padding-left:3px;"></i>
                    @endif
                </span>
                <span class="title">
                    Personalization
                </span>
            </a>
        </div>

        <div class="step @if($current=='time') current @endif @if(in_array("time", $completed)) complete @endif" style="left: 88%;">
            <a href="javascript:void(0)" class="dropdown-toggle">
                <span class="innerCircle">
                    @if(in_array("time", $completed))
                        <i class="fa-solid fa-check" style="color:var(--main-action-color); padding-left:3px;"></i>
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
