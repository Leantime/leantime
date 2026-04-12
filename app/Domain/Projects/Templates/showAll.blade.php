@extends($layout)

@section('content')

@php
    $showClosedProjects = $tpl->get('showClosedProjects');
@endphp

<div class="pageheader">

    <div class="pageicon"><span class="fa fa-suitcase"></span></div>
    <div class="pagetitle">
        <h5>{!! __('label.administration') !!}</h5>
        <h1>{!! __('headline.all_projects') !!}</h1>
    </div>

</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="pull-right">
            <form action="" method="post">
                <input type="hidden" name="hideClosedProjects" value="1" />
                <input type="checkbox" name="showClosedProjects" onclick="form.submit();" id="showClosed" @if ($showClosedProjects) checked='checked' @endif />&nbsp;<label for="showClosed" class="pull-right">Show Closed Projects</label>
            </form>
        </div>

        <a class="btn btn-primary" href="{{ BASE_URL }}/projects/newProject"><i class='fa fa-plus'></i> {!! __('link.new_project') !!}</a>
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

    </div>
</div>

@once @push('scripts')
<script type="text/javascript">
    jQuery(document).ready(function() {

            leantime.projectsController.initProjectTable();

        }
    );

</script>
@endpush @endonce

@endsection
