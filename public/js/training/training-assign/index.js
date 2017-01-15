angular.module('hris', [])
        .controller('trainingAssignController', function ($scope, $http) {
            $('select').select2();
            $scope.employeeList = [];
            $scope.all = false;
            $scope.assignShowHide = false;
            var l;

            $scope.checkAll = function (item) {
                for (var i = 0; i < $scope.employeeList.length; i++) {
                    $scope.employeeList[i].checked = item;
                }
                $scope.assignShowHide = item && ($scope.employeeList.length > 0);
                if ($scope.assignShowHide) {
                    l = Ladda.create(document.querySelector('#assignBtn'));
                }
            };
            $scope.checkUnit = function (item) {
                for (var i = 0; i < $scope.employeeList.length; i++) {
                    if ($scope.employeeList[i].checked) {
                        $scope.assignShowHide = true;
                        l = Ladda.create(document.querySelector('#assignBtn'));
                        break;
                    }
                    $scope.assignShowHide = false;
                }
            };
            $scope.view = function () {
                $scope.all = false;
                $scope.assignShowHide = false;
                var branchId = angular.element(document.getElementById('branchId')).val();
                var departmentId = angular.element(document.getElementById('departmentId')).val();
                var designationId = angular.element(document.getElementById('designationId')).val();
                var employeeId = angular.element(document.getElementById('employeeId')).val();
                var positionId = angular.element(document.getElementById('positionId')).val();
                var serviceTypeId = angular.element(document.getElementById('serviceTypeId')).val();
                var trainingId = angular.element(document.getElementById('trainingId')).val();

                window.app.pullDataById(document.url, {
                    action: 'pullEmployeeForTrainingAssign',
                    data: {
                        branchId: branchId,
                        departmentId: departmentId,
                        designationId: designationId,
                        employeeId: employeeId,
                        positionId: positionId,
                        serviceTypeId: serviceTypeId,
                        trainingId:trainingId
                    }
                }).then(function (success) {
                    //console.log("Employee list for assign", success);
                    $scope.$apply(function () {
                        $scope.employeeList = success.data;
                        //console.log(success.data);
                        for (var i = 0; i < $scope.employeeList.length; i++) {
                            $scope.employeeList[i].checked = false;
                        }

                    });
                }, function (failure) {
                    console.log("Employee Get All", failure);
                });
            };
            $scope.assign = function () {
                l.start();
                l.setProgress(0.5);
                var trainingId = angular.element(document.getElementById('trainingId')).val();

                var promises = [];
                for (var index in $scope.employeeList) {
                   // console.log($scope.employeeList[index]);
                    if ($scope.employeeList[index].checked) {
                        promises.push(window.app.pullDataById(document.url, {
                            action: 'assignEmployeeTraining',
                            data: {
                                employeeId: $scope.employeeList[index].EMPLOYEE_ID,
                                trainingId: trainingId,
                                oldTrainingId: $scope.employeeList[index].TRAINING_ID
                            }
                        }));
                    }
                }
                Promise.all(promises).then(function (success) {
                    console.log(success);
                    l.stop();
                    $scope.$apply(function () {
                        $scope.view();
                    });
                    window.toastr.success("Training assigned successfully!", "Notification");
                });
            };
            
            $scope.cancel = function(){
                l.start();
                l.setProgress(0.5);
                var trainingId = angular.element(document.getElementById('trainingId')).val();

                var promises = [];
                for (var index in $scope.employeeList) {
                   // console.log($scope.employeeList[index]);
                    if ($scope.employeeList[index].checked) {
                        promises.push(window.app.pullDataById(document.url, {
                            action: 'cancelEmployeeTraining',
                            data: {
                                employeeId: $scope.employeeList[index].EMPLOYEE_ID,
                                trainingId: trainingId,
                            }
                        }));
                    }
                }
                Promise.all(promises).then(function (success) {
                    console.log(success);
                    l.stop();
                    $scope.$apply(function () {
                        $scope.view();
                    });
                    window.toastr.success("Training assign cancelled successfully!", "Notification");
                });
            };
            
            $scope.viewTrainingAssignList = function () {
                var branchId = angular.element(document.getElementById('branchId')).val();
                var departmentId = angular.element(document.getElementById('departmentId')).val();
                var designationId = angular.element(document.getElementById('designationId')).val();
                var employeeId = angular.element(document.getElementById('employeeId')).val();
                var positionId = angular.element(document.getElementById('positionId')).val();
                var serviceTypeId = angular.element(document.getElementById('serviceTypeId')).val();
                var trainingId = angular.element(document.getElementById('trainingId')).val();
                var serviceEventTypeId = angular.element(document.getElementById('serviceEventTypeId')).val();

                window.app.pullDataById(document.url, {
                    action: 'pullTrainingAssignList',
                    data: {
                        branchId: branchId,
                        departmentId: departmentId,
                        designationId: designationId,
                        employeeId: employeeId,
                        positionId: positionId,
                        serviceTypeId: serviceTypeId,
                        trainingId:trainingId,
                        serviceEventTypeId:serviceEventTypeId
                    }
                }).then(function (success) {
                    console.log("Training Assign List", success);
                    $scope.$apply(function () {
                        $scope.initializekendoGrid(success.data);
                    });
                }, function (failure) {
                    console.log("Employee Get All", failure);
                });
            };
            $scope.initializekendoGrid = function (trainingAssignList) {
                $("#trainingAssignListTable").kendoGrid({
                    excel: {
                        fileName: "TrainingAssignList.xlsx",
                        filterable: true,
                        allPages: true
                    },
                    dataSource: {
                        data: trainingAssignList,
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
                        {field: "FIRST_NAME", title: "Employee Name", width: 150},
                        {field: "TRAINING_NAME", title: "Training Name", width: 120},
                        {field: "START_DATE", title: "Start Date", width: 80},
                        {field: "END_DATE", title: "End Date", width: 80},
                        {field: "DURATION", title: "Duration(in hour)", width: 100},
                        {field: "INSTITUTE_NAME", title: "Institute Name", width: 100},
                        {field: "TRAINING_TYPE", title: "Training Type", width: 100},
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
                                {value: "Employee Code"},
                                {value: "Employee Name"},
                                {value: "Training Name"},
                                {value: "Start Date"},
                                {value: "End Date"},
                                {value: "Duration"},
                                {value: "Institute Name"},
                                {value: "Location Detail"},
                                {value: "Instructor Name"},
                                {value: "Training Type"},
                                {value: "Remarks"},
                            ]
                        }];
                    var dataSource = $("#trainingAssignListTable").data("kendoGrid").dataSource;
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
                                {value: dataItem.EMPLOYEE_CODE},
                                {value: dataItem.FIRST_NAME + middleName + dataItem.LAST_NAME},
                                {value: dataItem.TRAINING_NAME},
                                {value: dataItem.START_DATE},
                                {value: dataItem.END_DATE},
                                {value: dataItem.DURATION},
                                {value: dataItem.INSTITUTE_NAME},
                                {value: dataItem.LOCATION},
                                {value: dataItem.INSTRUCTOR_NAME},
                                {value: dataItem.TRAINING_TYPE},
                                {value: dataItem.REMARKS},
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
                                    {autoWidth: true}
                                ],
                                title: "Training Assign List",
                                rows: rows
                            }
                        ]
                    });
                    kendo.saveAs({dataURI: workbook.toDataURL(), fileName: "TrainingAssignList.xlsx"});
                }
            }
        });