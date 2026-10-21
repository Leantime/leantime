@extends($layout)

@section('content')

@php
    $showClosedProjects = $showClosedProjects ?? false;
@endphp

<x-global::layout.page-header icon="fa fa-suitcase" :eyebrow="__('label.administration')">
    {!! __('headline.all_projects') !!}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

        <div class="pull-right">
            <form action="" method="post">
                <input type="hidden" name="hideClosedProjects" value="1" />
                <input type="checkbox" name="showClosedProjects" onclick="form.submit();" id="showClosed" @if ($showClosedProjects) checked='checked' @endif />&nbsp;<label for="showClosed" class="pull-right">{{ __('label.showClosedProjects', 'Show Closed Projects') }}</label>
            </form>
        </div>

        <x-global::forms.button tag="a" link="{{ BASE_URL }}/projects/newProject" contentRole="primary"><i class='fa fa-plus'></i> {!! __('link.new_project') !!}</x-global::forms.button>
        <div class="clearall"></div>
        <table class="table table-bordered" cellpadding="0" cellspacing="0" border="0" id="allProjectsTable">

            <colgroup>
                <col class="con1"/>
                <col class="con0" />
                <col class="con1"/>
                <col class="con0" />
                <col class="con1"/>
                <col class="con0"/>
            </colgroup>
            <thead>
                <tr>
                    <th class="head0">{!! __('label.project_name') !!}</th>
                    <th class="head1">{!! __('label.client_product') !!}</th>
                    <th class="head1">{!! __('label.project_type') !!}</th>
                    <th class="head0">{!! __('label.project_state') !!}</th>
                    <th class="head0">{!! __('label.hourly_budget') !!}</th>
                    <th class="head1">{!! __('label.budget_cost') !!}</th>
                </tr>
            </thead>

            <tbody>

             @foreach ($allProjects as $row)
                <tr class='gradeA'>

                    <td style="padding:6px;">
                        <a class="" href="{{ BASE_URL }}/projects/showProject/{{ $row['id'] }}">{{ $row['name'] }}</a>
                    </td>
                    <td>
                        <a class="" href="{{ BASE_URL }}/clients/showClient/{{ $row['clientId'] }}">{{ $row['clientName'] }}</a>
                    </td>

                    <td> {{ $row['type'] }} </td>

                    <td>
                        @if ($row['state'] == -1)
                            {!! __('label.closed') !!}
                        @else
                            {!! __('label.open') !!}
                        @endif
                    </td>
                    <td class="center">{{ $row['hourBudget'] }}</td>
                    <td class="center">{{ $row['dollarBudget'] }}</td>
                </tr>
             @endforeach

            </tbody>
        </table>

    </x-global::layout.content-box>
</div>

@once @push('scripts')
<script type="text/javascript">
    jQuery(document).ready(function() {

            leantime.projectsController.initProjectTable();

    });

</script>
@endpush @endonce

@endsection
