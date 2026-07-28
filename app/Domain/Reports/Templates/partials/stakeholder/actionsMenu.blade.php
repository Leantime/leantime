{{--
    Stakeholder Report — page-header three-dot menu.

    Verdict override (green / yellow / red / revert) + Print. Called from both
    the strategy and program report templates; POST target changes based on
    $scope.

    Vars in:
      $scope             'strategy' | 'program'
      $projectId         int — strategy or program id
      $verdictOverride   null | 'green' | 'yellow' | 'red' (drives Revert visibility)
--}}
@php
    $hxBase = BASE_URL.'/hx/'.($scope === 'strategy' ? 'strategyPro' : 'pgmPro').'/report/setVerdict';
@endphp


<span class="dropdown dropdownWrapper headerEditDropdown hideOnPrint">
    <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown" aria-label="{{ __('stakeholder.header.actions') }}"><i class="fa-solid fa-ellipsis-v"></i></a>
    <ul class="dropdown-menu editCanvasDropdown rd-actions-menu">
        <li class="dropdown-header">{{ __('stakeholder.verdict.set_label') }}</li>
        <li><a href="javascript:void(0)" hx-post="{{ $hxBase }}" hx-vals='{"verdict":"green","projectId":{{ (int) $projectId }}}' hx-swap="none">
            <i class="fa fa-circle-check" style="color:#3E937A;"></i> {{ __('stakeholder.verdict.ontrack') }}
        </a></li>
        <li><a href="javascript:void(0)" hx-post="{{ $hxBase }}" hx-vals='{"verdict":"yellow","projectId":{{ (int) $projectId }}}' hx-swap="none">
            <i class="fa fa-circle-exclamation" style="color:#C09035;"></i> {{ __('stakeholder.verdict.atrisk') }}
        </a></li>
        <li><a href="javascript:void(0)" hx-post="{{ $hxBase }}" hx-vals='{"verdict":"red","projectId":{{ (int) $projectId }}}' hx-swap="none">
            <i class="fa fa-triangle-exclamation" style="color:#C2295B;"></i> {{ __('stakeholder.verdict.off') }}
        </a></li>
        @if (($verdictOverride ?? null) !== null)
            <li><a href="javascript:void(0)" hx-post="{{ $hxBase }}" hx-vals='{"verdict":"revert","projectId":{{ (int) $projectId }}}' hx-swap="none">
                <i class="fa fa-arrow-rotate-left"></i> {{ __('stakeholder.verdict.revert') }}
            </a></li>
        @endif
        <li class="border"></li>
        <li><a href="javascript:window.print();"><i class="fa fa-print"></i> {{ __('label.print_report') }}</a></li>
    </ul>
</span>
