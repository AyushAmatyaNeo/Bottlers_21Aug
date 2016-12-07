//angular.module('hris', ["kendo.directives"])
(function ($, app) {
    'use strict';
    $(document).ready(function () {
        $("select").select2();
    });
})(window.jQuery, window.app);

angular.module('hris', [])
        .controller('employeeListController', function ($scope, $http) {
//            $scope.gridData = new kendo.data.ObservableArray([
//            ]);
//            $scope.gridColumns = [
//                {field: "employeeCode", title: "Employee Code"},
//                {field: "firstName", title: "Name"},
//                {field: "birthDate", title: "Birth Date"},
//                {field: "mobileNo", title: "Mobile No"},
//                {field: "emailOfficial", title: "Email Official"},
//                {title: "Action"}
//            ];
//            $scope.kendoGridOptions = {
//                height: 550,
//                scrollable: true,
//                sortable: true,
//                filterable: true,
//                rowTemplate: kendo.template($("#rowTemplate").html()),
//                pageable: {
//                    input: true,
//                    numeric: false
//                },
//            };
            $scope.view = function () {
                var employeeId = angular.element(document.getElementById('employeeId')).val();
                var branchId = angular.element(document.getElementById('branchId')).val();
                var departmentId = angular.element(document.getElementById('departmentId')).val();
                var designationId = angular.element(document.getElementById('designationId')).val();
                var positionId = angular.element(document.getElementById('positionId')).val();
                var serviceTypeId = angular.element(document.getElementById('serviceTypeId')).val();
                var serviceEventTypeId = angular.element(document.getElementById('serviceEventTypeId')).val();

                window.app.pullDataById(document.url, {
                    action: 'pullEmployeeList',
                    data: {
                        'employeeId': employeeId,
                        'branchId': branchId,
                        'departmentId': departmentId,
                        'designationId': designationId,
                        'positionId': positionId,
                        'serviceTypeId': serviceTypeId,
                        'serviceEventTypeId': serviceEventTypeId
                    }
                }).then(function (success) {
                    $scope.initializekendoGrid(success.data);
                    $scope.$apply(function () {
//                        $scope.gridData.splice(0, $scope.gridData.length);
//                        angular.forEach(success.data, function (value, key) {
//                            $scope.gridData.push(value);
//                        });

                    });
                }, function (failure) {
                    console.log(failure);
                });
            };

            $scope.initializekendoGrid = function (employees) {
                $("#employeeTable").kendoGrid({
                    excel: {
                        fileName: "EmployeeList.xlsx",
                        filterable: true,
                        allPages: true
                    },
                    dataSource: {
                        data: employees,
                        pageSize: 20,
                    },
                    height: 450,
                    scrollable: true,
                    sortable: true,
                    filterable: true,
                    pageable: {
                        input: true,
                        numeric: false
                    },
                    rowTemplate: kendo.template($("#rowTemplate").html()),
                    columns: [
                        {field: "employeeCode", title: "Employee Code",width:130},
                        {field: "firstName", title: "Name",width:220},
                        {field: "birthDate", title: "Birth Date",width:120},
                        {field: "mobileNo", title: "Mobile No",width:130},
                        {field: "emailOfficial", title: "Email Official",width:200},
                        {title: "Action",width:120}
                    ]
                });

                $("#export").click(function (e) {
                    var grid = $("#employeeTable").data("kendoGrid");
                    grid.saveAsExcel();
                });
                window.app.UIConfirmations();               
            };

        });

