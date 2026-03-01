
@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pageicon"><x-global::elements.icon name="contact_page" /></div>
    <div class="pagetitle">
        <h5>{{ __('label.administration') }}</h5>
        <h1>{{ __('headline.all_clients') }}</h1>
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div>
@dispatchEvent('afterPageHeaderClose')

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="tw:flex tw:items-center tw:flex-wrap tw:gap-2 tw:mb-4">
            @if($login::userIsAtLeast('manager'))
                <x-globals::forms.button link="{{ BASE_URL }}/clients/newClient" type="primary" icon="add">{{ __('link.new_client') }}</x-globals::forms.button>
            @endif
        </div>

        <table class="table table-bordered" cellpadding="0" cellspacing="0" border="0" id="allClientsTable">
            <colgroup>
                <col class='con0' />
                <col class='con1' />
                <col class='con0' />
                <col class='con1' />
            </colgroup>
            <thead>
                <tr>
                    <th class='head0'>{{ __('label.client_id') }}</th>
                    <th class='head1'>{{ __('label.client_name') }}</th>
                    <th class='head0'>{{ __('label.url') }}</th>
                    <th class='head1'>{{ __('label.number_of_projects') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tpl->get('allClients') as $row)
                    <tr>
                        <td>{{ $row['id'] }}</td>
                        <td>
                            <a href="{{ BASE_URL }}/clients/showClient/{{ $row['id'] }}">{{ e($row['name']) }}</a>
                        </td>
                        <td><a href="{{ e($row['internet']) }}" target="_blank">{{ e($row['internet']) }}</a></td>
                        <td>{{ $row['numberOfProjects'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>

<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')

    jQuery(document).ready(function() {
        leantime.clientsController.initClientTable();
    });

    @dispatchEvent('scripts.beforeClose')

</script>
