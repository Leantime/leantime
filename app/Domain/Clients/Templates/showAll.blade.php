
@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pageicon"><span class="fa fa-address-book"></span></div>
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

        @if($login::userIsAtLeast('manager'))
             <x-global::button link="{{ BASE_URL }}/clients/newClient" type="primary" icon="fa fa-plus">{{ __('link.new_client') }}</x-global::button>
        @endif

        <table class="table table-bordered" cellpadding="0" cellspacing="0" border="0" id="allClientsTable">
            <colgroup>
                <col class='con0' />
                <col class='con1' />
                <col class='con0' />
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
                            <a class="" href="{{ BASE_URL }}/clients/showClient/{{ $row['id'] }}"><i class='fa fa-plus'></i> {{ e($row['name']) }}</a>
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
