(function ($, app) {
    'use strict';
    $(document).ready(function () {
        $("select").select2();
        app.startEndDatePickerWithNepali('nepaliFromDate', 'fromDate', 'nepaliToDate', 'toDate');
    });
})(window.jQuery, window.app);

angular.module('hris', [])
        .controller("holidayWorkStatusListController", function ($scope, $http) {

            $scope.view = function () {
                var employeeId = angular.element(document.getElementById('employeeId')).val();
                var branchId = angular.element(document.getElementById('branchId')).val();
                var departmentId = angular.element(document.getElementById('departmentId')).val();
                var designationId = angular.element(document.getElementById('designationId')).val();
                var positionId = angular.element(document.getElementById('positionId')).val();
                var serviceTypeId = angular.element(document.getElementById('serviceTypeId')).val();
                var serviceEventTypeId = angular.element(document.getElementById('serviceEventTypeId')).val();
                var holidayId = angular.element(document.getElementById('holidayId')).val();
                var requestStatusId = angular.element(document.getElementById('requestStatusId')).val();
                var fromDate = angular.element(document.getElementById('fromDate')).val();
                var toDate = angular.element(document.getElementById('toDate')).val();
                var recomApproveId = angular.element(document.getElementById('recomApproveId')).val();

                window.app.pullDataById(document.url, {
                    action: 'pullHoliayWorkRequestStatusList',
                    data: {
                        'employeeId': employeeId,
                        'branchId': branchId,
                        'departmentId': departmentId,
                        'designationId': designationId,
                        'positionId': positionId,
                        'serviceTypeId': serviceTypeId,
                        'serviceEventTypeId': serviceEventTypeId,
                        'holidayId': holidayId,
                        'requestStatusId': requestStatusId,
                        'fromDate': fromDate,
                        'toDate': toDate,
                        'recomApproveId': recomApproveId
                    }
                }).then(function (success) {
                    console.log(success.recomApproveId);
                    $scope.initializekendoGrid(success.data);
                }, function (failure) {
                    console.log(failure);
                });
            }
            $scope.initializekendoGrid = function (holidayWorkRequestStatus) {
                console.log(holidayWorkRequestStatus);
                $("#holidayWorkRequestStatusTable").kendoGrid({
                    excel: {
                        fileName: "HolidayWorkRequestList.xlsx",
                        filterable: true,
                        allPages: true
                    },
                    dataSource: {
                        data: holidayWorkRequestStatus,
                        pageSize: 20
                    },
                    height: 450,
                    scrollable: true,
                    sortable: true,
                    filterable: true,
                    pageable: {
                        input: true,
                        numeric: false
                    },
                    dataBound: gridDataBound,
                    rowTemplate: kendo.template($("#rowTemplate").html()),
                    columns: [
                        {field: "FIRST_NAME", title: "Employee Name", width: 200},
                        {field: "HOLIDAY_ENAME", title: "Holiday Name", width: 120},
                        {field: "REQUESTED_DATE", title: "Requested Date", width: 150},
                        {field: "FROM_DATE", title: "From Date", width: 100},
                        {field: "TO_DATE", title: "To Date", width: 100},
                        {field: "DURATION", title: "Duration", width: 150},
                        {field: "YOUR_ROLE", title: "Your Role", width: 150},
                        {field: "STATUS", title: "Status", width: 90},
                        {title: "Action", width: 80}
                    ]
                });
                function gridDataBound(e) {
                    var grid = e.sender;
                    if (grid.dataSource.total() == 0) {
                        var colCount = grid.columns.length;
                        $(e.sender.wrapper)
                                .find('tbody')
                                .append('<tr class="kendo-data-row"><td colspan="' + colCount + '" class="no-data">There is no data to show in the grid.</td></tr>');
                    }
                }
                ;

                $("#export").click(function (e) {
                    var rows = [{
                            cells: [
                                {value: "Employee Name"},
                                {value: "Holiday Name"},
                                {value: "Requested Date"},
                                {value: "From Date"},
                                {value: "To Date"},
                                {value: "Your Role"},
                                {value: "Duration"},
                                {value: "Status"},
                                {value: "Remarks"},
                                {value: "Remarks By Recommender"},
                                {value: "Recommended Date"},
                                {value: "Remarks By Approver"},
                                {value: "Approved Date"}
                            ]
                        }];
                    var dataSource = $("#holidayWorkRequestStatusTable").data("kendoGrid").dataSource;
                    var filteredDataSource = new kendo.data.DataSource({
                        data: dataSource.data(),
                        filter: dataSource.filter()
                    });

                    filteredDataSource.read();
                    var data = filteredDataSource.view();

                    for (var i = 0; i < data.length; i++) {
                        var dataItem = data[i];
                        var middleName = dataItem.MIDDLE_NAME != null ? " " + dataItem.MIDDLE_NAME + " " : " ";

                        rows.push({
                            cells: [
                                {value: dataItem.FIRST_NAME + middleName + dataItem.LAST_NAME},
                                {value: dataItem.HOLIDAY_ENAME},
                                {value: dataItem.REQUESTED_DATE},
                                {value: dataItem.FROM_DATE},
                                {value: dataItem.TO_DATE},
                                {value: dataItem.YOUR_ROLE},
                                {value: dataItem.DURATION},
                                {value: dataItem.STATUS},
                                {value: dataItem.REMARKS},
                                {value: dataItem.RECOMMENDED_REMARKS},
                                {value: dataItem.RECOMMENDED_DATE},
                                {value: dataItem.APPROVED_REMARKS},
                                {value: dataItem.APPROVED_DATE}
                            ]
                        });
                    }
                    excelExport(rows);
                    e.preventDefault();
                });

                function excelExport(rows) {
                    var workbook = new kendo.ooxml.Workbook({
                        sheets: [
                            {
                                columns: [
                                    {autoWidth: true},
                                    {autoWidth: true},
                                    {autoWidth: true},
                                    {autoWidth: true},
                                    {autoWidth: true},
                                    {autoWidth: true},
                                    {autoWidth: true},
                                    {autoWidth: true},
                                    {autoWidth: true},
                                    {autoWidth: true},
                                    {autoWidth: true},
                                    {autoWidth: true},
                                    {autoWidth: true}
                                ],
                                title: "Work on Holiday Request List",
                                rows: rows
                            }
                        ]
                    });
                    kendo.saveAs({dataURI: workbook.toDataURL(), fileName: "HolidayWorkRequestList.xlsx"});
                }
            };
        });