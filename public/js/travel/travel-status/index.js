(function ($, app) {
    'use strict';
    $(document).ready(function () {
        $("select").select2();
        app.startEndDatePickerWithNepali('nepaliFromDate', 'fromDate', 'nepaliToDate', 'toDate', null, true);
        var $table = $('#table');
        var $search = $('#search');
        var $status = $('#status');
        var $fromDate = $('#fromDate');
        var $toDate = $('#toDate');
        var $bulkActionDiv = $('#bulkActionDiv');
        var $bulkBtns = $(".btnApproveReject");
        var action = `
            <div class="clearfix">
                #if(REQUESTED_TYPE=='ad'){#
                <a class="btn btn-icon-only green" href="${document.viewLink}/#:TRAVEL_ID#" style="height:17px;" title="View Detail">
                    <i class="fa fa-search"></i>
                </a>
                #}else{#
                <a class="btn btn-icon-only green" href="${document.expenseDetailLink}/#:TRAVEL_ID#" style="height:17px;" title="View Detail">
                    <i class="fa fa-search"></i>
                </a>
                #}#
            </div>
        `;
        var columns = [
            {field: "EMPLOYEE_NAME", title: "Employee"},
            {title: "Start Date",
                columns: [{
                        field: "FROM_DATE_AD",
                        title: "English",
                    },
                    {
                        field: "FROM_DATE_BS",
                        title: "Nepali",
                    }]},
            {title: "To Date",
                columns: [{
                        field: "TO_DATE_AD",
                        title: "English",
                    },
                    {field: "TO_DATE_BS",
                        title: "Nepali",
                    }]},
            {title: "Applied Date",
                columns: [{
                        field: "REQUESTED_DATE_AD",
                        title: "English",
                    },
                    {field: "REQUESTED_DATE_BS",
                        title: "Nepali",
                    }]},
            {field: "DESTINATION", title: "Destination"},
            {field: "REQUESTED_AMOUNT", title: "Request Amt."},
            {field: "REQUESTED_TYPE_DETAIL", title: "Request For"},
            {field: "TRANSPORT_TYPE_DETAIL", title: "Transport"},
            {field: "STATUS_DETAIL", title: "Status"},
            {field: ["TRAVEL_ID", "REQUESTED_TYPE"], title: "Action", template: action}
        ];
        var pk = 'TRAVEL_ID';
        var grid = app.initializeKendoGrid($table, columns, null, {id: pk, atLast: false, fn: function (selected) {
                if (selected) {
                    $bulkActionDiv.show();
                } else {
                    $bulkActionDiv.hide();
                }
            }});
        $search.on('click', function () {
            var search = document.searchManager.getSearchValues();
            search['status'] = $status.val();
            search['fromDate'] = $fromDate.val();
            search['toDate'] = $toDate.val();
            app.serverRequest('', search).then(function (response) {
                if (response.success) {
                    app.renderKendoGrid($table, response.data);
                } else {
                    app.showMessage(response.error, 'error');
                }
            }, function (error) {
                app.showMessage(error, 'error');
            });
        });
        app.searchTable($table, ['EMPLOYEE_NAME']);
        var exportMap = {
            'EMPLOYEE_NAME': 'Employee Name',
            'REQUESTED_DATE_AD': 'Request Date(AD)',
            'REQUESTED_DATE_BS': 'Request Date(BS)',
            'FROM_DATE_AD': 'From Date(AD)',
            'FROM_DATE_BS': 'From Date(BS)',
            'TO_DATE_AD': 'To Date(AD)',
            'TO_DATE_BS': 'To Date(BS)',
            'DESTINATION': 'Destination',
            'REQUESTED_AMOUNT': 'Request Amt',
            'REQUESTED_TYPE_DETAIL': 'Request Type',
            'TRANSPORT_TYPE_DETAIL': 'Transport',
            'STATUS_DETAIL': 'Status',
            'PURPOSE': 'Purpose',
            'REMARKS': 'Remarks',
            'RECOMMENDER_NAME': 'Recommender',
            'APPROVER_NAME': 'Approver',
            'RECOMMENDED_BY_NAME': 'Recommended By',
            'APPROVED_BY_NAME': 'Approved By',
            'RECOMMENDED_REMARKS': 'Recommended Remarks',
            'RECOMMENDED_DATE': 'Recommended Date',
            'APPROVED_REMARKS': 'Approved Remarks',
            'APPROVED_DATE': 'Approved Date'
        };
        $('#excelExport').on('click', function () {
            app.excelExport($table, exportMap, 'Travel Request List.xlsx');
        });
        $('#pdfExport').on('click', function () {
            app.exportToPDF($table, exportMap, 'Travel Request List.pdf');
        });

        $bulkBtns.bind("click", function () {
            var list = grid.getSelected();
            var action = $(this).attr('action');

            var selectedValues = [];
            for (var i in list) {
                selectedValues.push({id: list[i][pk], action: action});
            }
            app.bulkServerRequest(document.bulkLink, selectedValues, function () {
                $search.trigger('click');
            }, function (data, error) {

            });
        });
    });
})(window.jQuery, window.app);
