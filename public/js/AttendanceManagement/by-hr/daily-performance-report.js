(function ($, app) {
    'use strict';
    $(document).ready(function () {
        var $fromDate = $('#fromDate');
        var $toDate = $('#toDate'); 
        var $presentStatusId = $("#presentStatusId");
        var $status = $('#statusId');
        var $table = $('#table');
        var $search = $('#search');

        $('select').select2();
        
        $.each(document.searchManager.getIds(), function (key, value) {
            $('#' + value).select2();
        });
        $presentStatusId.select2();
        $status.select2();
        app.startEndDatePickerWithNepali('nepaliFromDate', 'fromDate', 'nepaliToDate', 'toDate', null, false);
        app.getServerDate().then(function (response) {
            $fromDate.val(response.data.serverDate);
            $('#nepaliFromDate').val(nepaliDatePickerExt.fromEnglishToNepali(response.data.serverDate));
        });

        app.initializeKendoGrid($table, [
            {field: "EMPLOYEE_CODE", title: "Code", width: '75px'},
            {field: "FULL_NAME", title: "Employee"},
            {field: "DEPARTMENT_NAME", title: "Department"},
            {field: "ATTENDANCE_DT", title: "Date"},
            {title: "Shift Time",
                columns: [
                    {
                        field: "SHIFT_START_TIME",
                        title: "In",
                        template: "<span>#: (SHIFT_START_TIME == null) ? '-' : SHIFT_START_TIME # </span>"
                    },
                    {
                        field: "SHIFT_END_TIME",
                        title: "Out",
                        template: "<span>#: (SHIFT_END_TIME == null) ? '-' : SHIFT_START_TIME # </span>"
                    },
                    {
                        field: "TOTAL_WORKING_HR",
                        title: "Working Hour"
                    }
                ]
            },
            {title: "Time",
                columns: [
                    {
                        field: "IN_TIME",
                        title: "In",
                        template: "<span>#: (IN_TIME == null) ? '-' : IN_TIME # </span>"
                    }
                ]
            },
            {title: "Break",
                columns: [
                    {
                        field: "LUNCH_IN_TIME",
                        title: "In",
                        template: "<span>#: (LUNCH_IN_TIME == null) ? '-' : LUNCH_IN_TIME # </span>"
                    },
                    {
                        field: "LUNCH_OUT_TIME",
                        title: "Out",
                        template: "<span>#: (LUNCH_OUT_TIME == null) ? '-' : LUNCH_OUT_TIME # </span>"
                    }
                ]
            },
            {title: "Time",
                columns: [
                    {
                        field: "OUT_TIME",
                        title: "Out",
                        template: "<span>#: (OUT_TIME == null) ? '-' : OUT_TIME # </span>"
                    }
                ]
            },
            {field: "ACTUAL_WORKING_HR", title: "Actual Working"},
            {field: "OT", title: "OT Hours"}
            // {field: "LATE_IN", title: "Late In"},
            // {field: "LATE_OUT", title: "Late Out"},
            // {field: "EARLY_IN", title: "Early Out"},
            // {field: "EARLY_OUT", title: "Early Out"},
            // {field: "REMARKS", title: "Remarks"},
        ]);

        $search.on('click', function () {
            var q = document.searchManager.getSearchValues();
            q['fromDate'] = $fromDate.val();
            q['toDate'] = $toDate.val();
            q['status'] = $status.val();
            q['presentStatus'] = $presentStatusId.val();
            app.serverRequest(document.pullAttendanceWS, q).then(function (response) {
                if (response.success) {
                    app.renderKendoGrid($table, response.data);
                } else {
                    app.showMessage(response.error, 'error');
                }
            }, function (error) {
                app.showMessage(error, 'error');
            });
        });

        app.searchTable($table, ['EMPLOYEE_NAME', 'EMPLOYEE_CODE']);
        var exportMap = {
            'COMPANY_NAME': ' Company',
            'DEPARTMENT_NAME': ' Department',
            'EMPLOYEE_CODE': 'Code',
            'EMPLOYEE_NAME': ' Name',
            'ATTENDANCE_DT': 'Attendance Date(AD)',
            'ATTENDANCE_DT_N': 'Attendance Date(BS)',
            'IN_TIME': 'In Time',
            'OUT_TIME': 'Out Time',
            'IN_REMARKS': 'In Remarks',
            'OUT_REMARKS': 'Out Remarks',
            'TOTAL_HOUR': 'Total Hour',
//            'SYSTEM_OVERTIME': 'System OT',
//            'MANUAL_OVERTIME': 'Manual OT',
            'STATUS': 'Status',
            'SHIFT_ENAME': 'Shift Name',
            'START_TIME': 'Start Time',
            'END_TIME': 'End Time',
            'FUNCTIONAL_TYPE_EDESC': 'Functional Type'
        };
        $('#excelExport').on('click', function () {
            app.excelExport($table, exportMap, "AttendanceList.xlsx");
        });
        $('#pdfExport').on('click', function () {
            app.exportToPDF($table, exportMap, "AttendanceList.pdf");

        });

        var selectItems = {};
        var $bulkBtnContainer = $('#acceptRejectDiv');
    });
})(window.jQuery, window.app);
