@php
    $project = $tpl->get('project');
    $menuTypes = $tpl->get('menuTypes');
    $showClosedProjects = $tpl->get('showClosedProjects');
@endphp

<x-globals::layout.page-header icon="luggage" subtitle="{{ __('label.administration') }}" headline="{{ __('headline.all_projects') }}" />

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="tw:flex tw:items-center tw:flex-wrap tw:gap-2 tw:mb-4">
            <x-globals::forms.button link="{{ BASE_URL }}/projects/newProject" type="primary" icon="add">{{ __('link.new_project') }}</x-globals::forms.button>

            <div class="tw:flex-1"></div>

            <form action="" method="post" class="tw:flex tw:items-center tw:gap-1">
                <input type="hidden" name="hideClosedProjects" value="1" />
                <input type="checkbox" name="showClosedProjects" onclick="form.submit();" id="showClosed" {{ $showClosedProjects ? "checked='checked'" : '' }} />
                <label for="showClosed">Show Closed Projects</label>
            </form>
        </div>

        <x-globals::elements.table :hover="true" id="allProjectsTable">
            <x-slot:head>
                <colgroup>
                    <col class="con1"/>
                    <col class="con0" />
                    <col class="con1"/>
                    <col class="con0" />
                    <col class="con1"/>
                    <col class="con0"/>
                </colgroup>
                <tr>
                    <th class="head0">{{ __('label.project_name') }}</th>
                    <th class="head1">{{ __('label.client_product') }}</th>
                    <th class="head1">{{ __('label.project_type') }}</th>
                    <th class="head0">{{ __('label.project_state') }}</th>
                    <th class="head0">{{ __('label.hourly_budget') }}</th>
                    <th class="head1">{{ __('label.budget_cost') }}</th>
                </tr>
            </x-slot:head>

            @foreach($tpl->get('allProjects') as $row)
                <tr class='gradeA'>
                    <td>
                        <a href="{{ BASE_URL }}/projects/showProject/{{ $row['id'] }}">{{ e($row['name']) }}</a>
                    </td>
                    <td>
                        <a href="{{ BASE_URL }}/clients/showClient/{{ $row['clientId'] }}">{{ e($row['clientName']) }}</a>
                    </td>
                    <td>{{ $row['type'] }}</td>
                    <td>
                        @if($row['state'] == -1)
                            {{ __('label.closed') }}
                        @else
                            {{ __('label.open') }}
                        @endif
                    </td>
                    <td class="center">{{ e($row['hourBudget']) }}</td>
                    <td class="center">{{ e($row['dollarBudget']) }}</td>
                </tr>
            @endforeach
        </x-globals::elements.table>

    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
        leantime.projectsController.initProjectTable();
    });
</script>
