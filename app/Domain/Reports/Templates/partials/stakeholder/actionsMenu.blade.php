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

<style>
/*
 * Scoped fixes for the actions dropdown — the app's global .dropdown-menu
 * styles size items for the sidebar / nav, not for a compact utility menu.
 * Constrain width, left-align text, tighten padding, uniform icon spacing.
 */
.rd-actions-menu{width:220px !important;min-width:220px !important;padding:5px 0 !important;text-align:left !important;letter-spacing:0 !important;}
.rd-actions-menu li{text-align:left !important;}
/* Explicit hex — the `--main-titles-color` variable resolves to white inside
   the pageheader (dark-bg context), so items inherit white on the white menu
   background and read as invisible. Same reason for hover color. */
.rd-actions-menu li > a{display:flex !important;align-items:center !important;gap:9px !important;padding:7px 14px !important;font-size:13px !important;font-weight:500 !important;color:#182831 !important;line-height:1.3 !important;text-transform:none !important;letter-spacing:0 !important;text-align:left !important;text-decoration:none !important;}
.rd-actions-menu li > a:hover{background:rgba(0,0,0,.04) !important;color:#004766 !important;text-decoration:none !important;}
.rd-actions-menu li > a i{font-size:12px !important;width:14px !important;text-align:center !important;flex:none !important;}
.rd-actions-menu .dropdown-header{padding:8px 14px 6px !important;font-size:10px !important;font-weight:700 !important;letter-spacing:.5px !important;color:#7a8791 !important;text-align:left !important;text-transform:uppercase !important;}
.rd-actions-menu .border{border-top:1px solid #eef1f3 !important;margin:5px 0 !important;height:1px !important;}
</style>

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
