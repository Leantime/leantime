import i18n from 'i18n';
import DataTable from 'datatables.net';
import 'datatables.net-buttons';
// import 'datatables.net-rowgroup';
// import 'datatables.net-rowreorder';
import 'datatables.net-colreorder';
import 'datatables.net-responsive';

const generateTableSettings = () => ({
    language: {
        decimal: i18n.__("datatables.decimal"),
        emptyTable:     i18n.__("datatables.emptyTable"),
        info:           i18n.__("datatables.info"),
        infoEmpty:      i18n.__("datatables.infoEmpty"),
        infoFiltered:   i18n.__("datatables.infoFiltered"),
        infoPostFix:    i18n.__("datatables.infoPostFix"),
        thousands:      i18n.__("datatables.thousands"),
        lengthMenu:     i18n.__("datatables.lengthMenu"),
        loadingRecords: i18n.__("datatables.loadingRecords"),
        processing:     i18n.__("datatables.processing"),
        search:         i18n.__("datatables.search"),
        zeroRecords:    i18n.__("datatables.zeroRecords"),
        paginate: {
            first:      i18n.__("datatables.first"),
            last:       i18n.__("datatables.last"),
            next:       i18n.__("datatables.next"),
            previous:   i18n.__("datatables.previous"),
        },
        aria: {
            sortAscending:  i18n.__("datatables.sortAscending"),
            sortDescending:i18n.__("datatables.sortDescending"),
        },
        buttons: {
            colvis: i18n.__("datatables.buttons.colvis"),
            csv: i18n.__("datatables.buttons.download")
        }
    },
    // dom: '<"top">brt<"bottom"><"clear">',
    // dom: 'Bfrtip',
    // searching: false,
    // stateSave: true,
    // displayLength:100,
    // order: [],
    // columnDefs: [
    //     {
    //         visible: false,
    //         targets: 7
    //     },
    //     {
    //         visible: false,
    //         targets: 8
    //     },
    //     {
    //         target: "no-sort",
    //         orderable: false
    //     },
    // ],
    colReorder: true,
    responsive: true,
    saveState: false,
    // buttons: true,
    // buttons: [
    //     'columnVisibility',
    //     'collection',
    //     'colvis',
    //     'print',
    //     'colvisGroup',
    //     'colvisRestore',
    // ],
    // layout: {
    //     topStart: {
    //         buttons: [
    //             'collection',
    //             'columnVisibility',
    //             'colvis',
    //             'print',
    //             'colvisGroup',
    //             'colvisRestore',
    //         ],
    //     },
    // },
});

const initDataTable = (element) => {
    const table = new DataTable(element, generateTableSettings());
    new DataTable.Buttons(table, {
        buttons: [
            'copy', 'excel', 'pdf', 'colvis', 'collection'
        ]
    });
    console.log(table);
    return table;
};

export default {
    initDataTable: initDataTable,
};
