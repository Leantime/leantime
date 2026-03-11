
<x-globals::layout.page-header icon="contact_page" subtitle="{{ __('label.administration') }}" headline="{{ __('headline.all_clients') }}" />

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="tw:flex tw:items-center tw:flex-wrap tw:gap-2 tw:mb-4">
            @if($login::userIsAtLeast('manager'))
                <x-globals::forms.button link="{{ BASE_URL }}/clients/newClient" type="primary" icon="add">{{ __('link.new_client') }}</x-globals::forms.button>
            @endif
        </div>

        <x-globals::elements.table :hover="true" id="allClientsTable">
            <x-slot:head>
                <colgroup>
                    <col class='con0' />
                    <col class='con1' />
                    <col class='con0' />
                    <col class='con1' />
                </colgroup>
                <tr>
                    <th class='head0'>{{ __('label.client_id') }}</th>
                    <th class='head1'>{{ __('label.client_name') }}</th>
                    <th class='head0'>{{ __('label.url') }}</th>
                    <th class='head1'>{{ __('label.number_of_projects') }}</th>
                </tr>
            </x-slot:head>

            @foreach($tpl->get('allClients') as $row)
                <tr>
                    <td>{{ $row['id'] }}</td>
                    <td>
                        <a href="{{ BASE_URL }}/clients/showClient/{{ $row['id'] }}">{{ $row['name'] }}</a>
                    </td>
                    <td><a href="{{ $row['internet'] }}" target="_blank">{{ $row['internet'] }}</a></td>
                    <td>{{ $row['numberOfProjects'] }}</td>
                </tr>
            @endforeach
        </x-globals::elements.table>

    </div>
</div>

<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')

    jQuery(document).ready(function() {
        leantime.clientsController.initClientTable();
    });

    @dispatchEvent('scripts.beforeClose')

</script>
