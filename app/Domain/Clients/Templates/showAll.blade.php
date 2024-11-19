@extends($layout)

@section('content')

<?php
?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5>{{ __("label.administration") }}</h5>
        <h1>{{ __("headline.all_clients") }}</h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">

        @displayNotification()

        <?php
        if ($login::userIsAtLeast('manager')) { ?>
             <a class="btn btn-primary" href="{{ BASE_URL }}/clients/newClient"><i class='fa fa-plus'></i> <?=$tpl->__('link.new_client') ?></a>
        <?php } ?>

        <table class="table table-bordered" cellpadding="0" cellspacing="0" border="0" id="allClientsTable">
            <colgroup>
                <col class='con0' />
                <col class='con1' />
                <col class='con0' />
            </colgroup>
            <thead>
                <tr>
                    <th class='head0'>{{ __("label.client_id") }}</th>
                    <th class='head1'>{{ __("label.client_name") }}</th>
                    <th class='head0'>{{ __("label.url") }}</th>
                    <th class='head1'>{{ __("label.number_of_projects") }}</th>
                </tr>
            </thead>
            <tbody>

            <?php foreach ($tpl->get('allClients') as $row) { ?>
                <tr>
                    <td>{{ $row->id }}</td>
                    <td>
                        <a class="" href="{{ BASE_URL }}/clients/showClient/{{ $row->id }}"><i class='fa fa-plus'></i> {{ $row->name }}</a>
                    </td>
                    <td><a href="{{ $row->internet }}" target="_blank">{{ $row->internet }}</a></td>
                    <td>{{ $row->numberOfProjects }}</td>
                </tr>
            <?php } ?>

            </tbody>
        </table>

    </div>
</div>

<script type="text/javascript">

    <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>

    jQuery(document).ready(function() {

        leantime.clientsController.initClientTable();


    });

    <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>

</script>

@endsection