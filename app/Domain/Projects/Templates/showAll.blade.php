@extends($layout)

@section('content')

<?php

use Leantime\Domain\Menu\Repositories\Menu;

$project = $tpl->get('project');
$menuTypes = $tpl->get('menuTypes');
$showClosedProjects = $tpl->get('showClosedProjects');

?>

<div class="pageheader">

    <div class="pageicon"><span class="fa fa-suitcase"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('label.administration');
        $tpl->__("") ?></h5>
        <h1>{{ __("headline.all_projects") }}</h1>
    </div>

</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        @displayNotification()

        <div class="pull-right">
            <form action="" method="post">
                <input type="hidden" name="hideClosedProjects" value="1" />
                <x-global::forms.checkbox
                    name="showClosedProjects"
                    id="showClosed"
                    :checked="$showClosedProjects"
                    onclick="form.submit();"
                    labelText="Show Closed Projects"
                    labelPosition="right"
                />
            </form>
        </div>
        
        <x-global::forms.button tag="a" content-role="primary" href="{{ BASE_URL }}/projects/newProject">
            <i class='fa fa-plus'></i> {{ __('link.new_project') }}
        </x-global::forms.button>
        
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
                    <th class="head0">{{ __("label.project_name") }}</th>
                    <th class="head1">{{ __("label.client_product") }}</th>
                    <th class="head1">{{ __("label.project_type") }}</th>
                    <th class="head0">{{ __("label.project_state") }}</th>
                    <th class="head0">{{ __("label.hourly_budget") }}</th>
                    <th class="head1">{{ __("label.budget_cost") }}</th>
                </tr>
            </thead>

            <tbody>

             <?php foreach ($tpl->get('allProjects') as $row) : ?>
                <tr class='gradeA'>

                    <td style="padding:6px;">
                        <a class="link link-hover" href="{{ BASE_URL }}/projects/showProject/{{ $row['id'] }}">{!! $row['name'] !!}</a>
                    </td>
                    <td>
                        <a class="link link-hover" href="{{ BASE_URL }}/clients/showClient/{{ $row['clientId'] }}">{!! $row['clientName'] !!}</a>
                    </td>

                    <td> {{ $row['type'] }} </td>

                    <td><?php if ($row['state'] == -1) {
                        echo $tpl->__('label.closed');
                        } else {
                            echo $tpl->__('label.open');
                        } ?>
                    </td>
                    <td class="center">{{ $row['hourBudget'] }}</td>
                    <td class="center">{{ $row['dollarBudget'] }}</td>
                </tr>
             <?php endforeach; ?>

            </tbody>
        </table>

    </div>
</div>



<script type="text/javascript">
    jQuery(document).ready(function() {



            leantime.projectsController.initProjectTable();

        }
    );

</script>

@endsection
