(function ($, app) {
    'use strict';
    $(document).ready(function () {
        var $tableContainer = $("#reportTable");
        var extractDetailData = function (rawData, departmentId) {
            var data = {};
            var column = {};
            var counter = 1;
            for (var i in rawData) {
                if (typeof data[rawData[i].EMPLOYEE_ID] !== 'undefined') {
                    data[rawData[i].EMPLOYEE_ID].DAYS[rawData[i].FORMATTED_ATTENDANCE_DT] =
                            JSON.stringify({
                                IS_ABSENT: rawData[i].IS_ABSENT,
                                IS_PRESENT: rawData[i].IS_PRESENT,
                                ON_LEAVE: rawData[i].ON_LEAVE,
                                IS_DAYOFF: rawData[i].IS_DAYOFF
                            });
                    data[rawData[i].EMPLOYEE_ID].TOTAL.IS_ABSENT = data[rawData[i].EMPLOYEE_ID].TOTAL.IS_ABSENT + parseFloat(rawData[i].IS_ABSENT);
                    data[rawData[i].EMPLOYEE_ID].TOTAL.IS_PRESENT = data[rawData[i].EMPLOYEE_ID].TOTAL.IS_PRESENT + parseFloat(rawData[i].IS_PRESENT);
                    data[rawData[i].EMPLOYEE_ID].TOTAL.ON_LEAVE = data[rawData[i].EMPLOYEE_ID].TOTAL.ON_LEAVE + parseFloat(rawData[i].ON_LEAVE);
                    data[rawData[i].EMPLOYEE_ID].TOTAL.IS_DAYOFF = data[rawData[i].EMPLOYEE_ID].TOTAL.IS_DAYOFF + parseFloat(rawData[i].IS_DAYOFF);
                } else {
                    data[rawData[i].EMPLOYEE_ID] = {
                        EMPLOYEE_ID: rawData[i].EMPLOYEE_ID,
                        FULL_NAME: rawData[i].FULL_NAME,
                        DAYS: {},
                        TOTAL: {
                            IS_ABSENT: parseFloat(rawData[i].IS_ABSENT),
                            IS_PRESENT: parseFloat(rawData[i].IS_PRESENT),
                            ON_LEAVE: parseFloat(rawData[i].ON_LEAVE),
                            IS_DAYOFF: parseFloat(rawData[i].IS_DAYOFF)
                        }
                    };
                    data[rawData[i].EMPLOYEE_ID].DAYS[rawData[i].FORMATTED_ATTENDANCE_DT] =
                            JSON.stringify({
                                IS_ABSENT: rawData[i].IS_ABSENT,
                                IS_PRESENT: rawData[i].IS_PRESENT,
                                ON_LEAVE: rawData[i].ON_LEAVE,
                                IS_DAYOFF: rawData[i].IS_DAYOFF
                            });

                }
                if (typeof column[rawData[i].FORMATTED_ATTENDANCE_DT] === 'undefined') {
                    var temp = rawData[i].FORMATTED_ATTENDANCE_DT;
                    column[rawData[i].FORMATTED_ATTENDANCE_DT] = {
                        field: temp,
                        title: "" + counter,
                        template: '<span data="#: ' + temp + ' #" class="daily-attendance"></span>'
                    }
                    counter++;

                }
            }
            var returnData = {rows: [], cols: []};

            returnData.cols.push({
                field: 'employee',
                title: 'employees'
            });
            for (var k in column) {
                returnData.cols.push(column[k]);
            }
            returnData.cols.push({
                field: 'total',
                title: 'Total',
                template: '<div data="#: total #" class="btn-group widget-btn-list total-attendance">' +
                        '<a class="btn btn-default widget-btn custom-btn-present"></a>' +
                        '<a class="btn btn-danger widget-btn custom-btn-absent"></a>' +
                        '<a class="btn btn-info widget-btn custom-btn-leave"></a>' +
                        '</div>'});

            for (var k in data) {
                var row = data[k].DAYS;
                row['employee'] = data[k].FULL_NAME;
                returnData.rows.push(row);
                row['total'] = JSON.stringify(data[k].TOTAL);
            }
            return returnData;
        };
        var displayDataInBtnGroup = function (selector) {
            $(selector).each(function (k, group) {
                var $group = $(group);
                var data = JSON.parse($group.attr('data'));
                if (data.IS_PRESENT == 1) {
                    $group.html('P');
                    $group.parent().addClass('bg-green');
                } else {
                    if (data.IS_ABSENT == 1) {
                        $group.html('A');
                        $group.parent().addClass('bg-red');

                    } else {
                        if (data.ON_LEAVE == 1) {
                            $group.html('L');
                            $group.parent().addClass('bg-blue');

                        } else {
                            $group.html('H');
                            $group.parent().addClass('bg-white');
                        }

                    }

                }
//                $group.html((data.IS_PRESENT == 1) ? 'P' : ((data.IS_ABSENT == 1) ? 'A' : (data.ON_LEAVE == 1) ? 'L' : 'H'));
//                $group.parent().addClass('bg-red');
            });

        };
        var displayTotalInGrid = function (selector) {
            $(selector).each(function (k, group) {
                var $group = $(group);
                var data = JSON.parse($group.attr('data'));
                var $childrens = $group.children();
                var $present = $($childrens[0]);
                var $absent = $($childrens[1]);
                var $leave = $($childrens[2]);

                var presentDays = parseFloat(data['IS_PRESENT']);
                var absentDays = parseFloat(data['IS_ABSENT']);
                var leaveDays = parseFloat(data['ON_LEAVE']);

                var total = presentDays + absentDays + leaveDays;

                $present.html(Number((presentDays * 100 / total).toFixed(1)));
                $absent.html(Number((absentDays * 100 / total).toFixed(1)));
                $leave.html(Number((leaveDays * 100 / total).toFixed(1)));
            });
        };
        var initializeReport = function (monthId, departmentId) {
            $tableContainer.block();
            app.pullDataById(document.wsDepartmentWiseDailyReport, {departmentId: departmentId, monthId: monthId}).then(function (response) {
                $tableContainer.unblock();
                console.log('departmentWiseEmployeeMonthlyR', response);
                var extractedDetailData = extractDetailData(response.data, departmentId);
                console.log('extractedDetailData', extractedDetailData);
                $tableContainer.kendoGrid({
                    dataSource: {
                        data: extractedDetailData.rows,
                        pageSize: 20
                    },
                    scrollable: false,
                    sortable: true,
                    pageable: true,
                    columns: extractedDetailData.cols
                });
                displayDataInBtnGroup('.daily-attendance');
                displayTotalInGrid('.total-attendance');


            }, function (error) {
                $tableContainer.unblock();
                console.log('departmentWiseEmployeeMonthlyE', error);
            });
        };

        $('select').select2();
        var $countryList = $('#countryList');
        var $monthList = $('#monthList');
        var $branchList = $('#branchList');
        var $departmentList = $('#departmentList');
        var $generateReport = $('#generateReport');

        var populateList = function ($element, list, id, value, defaultMessage) {
            $element.html('');
            $element.append($("<option></option>").val(-1).text(defaultMessage));
            for (var i in list) {
                $element.append($("<option></option>").val(list[i][id]).text(list[i][value]));
            }
        }

        var comBraDepList = document.comBraDepList;
        var monthList = document.monthList;
        populateList($monthList, monthList, 'MONTH_ID', 'MONTH_EDESC', "Select Month");
        populateList($countryList, comBraDepList, 'COMPANY_ID', 'COMPANY_NAME', "Select Company");
        populateList($branchList, [], 'BRANCH_ID', 'BRANCH_NAME', "SELECT BRANCH");
        populateList($departmentList, [], 'DEPARTMENT_ID', 'DEPARTMENT_NAME', "SELECT DEPARTMENT");

        $countryList.on('change', function () {
            var $this = $(this);
            if ($this.val() != -1) {
                populateList($branchList, comBraDepList[$this.val()]['BRANCH_LIST'], 'BRANCH_ID', 'BRANCH_NAME', "SELECT BRANCH");
                populateList($departmentList, [], 'DEPARTMENT_ID', 'DEPARTMENT_NAME', "SELECT DEPARTMENT");
            }
        });
        $branchList.on('change', function () {
            var $this = $(this);
            if ($this.val() != -1) {
                populateList($departmentList, comBraDepList[$countryList.val()]['BRANCH_LIST'][$this.val()]['DEPARTMENT_LIST'], 'DEPARTMENT_ID', 'DEPARTMENT_NAME', "SELECT DEPARTMENT");
            }
        });

        $generateReport.on('click', function () {
            var departmentId = $departmentList.val();
            var monthId = $monthList.val();
            if (departmentId == -1 || monthId == -1) {
                app.errorMessage("No Department Selected", "Notification");
            } else {
                initializeReport(monthId, departmentId);
            }
        });

        var monthId = document.monthId;
        var departmentId = document.departmentId;
        
        if (monthId != 0 && departmentId != 0) {
            initializeReport(monthId, departmentId);
        }

    });
})(window.jQuery, window.app);