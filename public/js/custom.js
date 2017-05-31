window.app = (function ($, toastr, App) {
    "use strict";
    $(document).ready(function () {
        App.setAssetsPath(document.basePath + '/assets/');
    });

    var format = "dd-M-yyyy";
    window.toastr.options = {"positionClass": "toast-bottom-right"};


    var pullDataById = function (url, data) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: url,
                data: data,
                type: 'POST',
                error: function (error) {
                    reject(error);
                },
                success: function (data) {
                    resolve(data);
                }

            });
        });
    };

    var populateSelectElement = function (element, data) {
        element.html('');
        for (var key in data) {
            element.append($('<option>', {value: key, text: data[key]}));
        }
    }

    var fetchAndPopulate = function (url, id, element, callback) {
        pullDataById(url, {id: id}).then(function (data) {
            populateSelectElement(element, data);
            if (typeof callback !== 'undefined') {
                callback();
            }
        }, function (error) {
            console.log("Error fetching Districts", error);
        });
    }


    var addDatePicker = function () {
        for (var x in arguments) {
            arguments[x].datepicker({
                format: format,
                todayHighlight: true,
                autoclose: true,
                setDate: new Date()
            });

        }
    };
    var addComboTimePicker = function () {
        for (var x in arguments) {
            arguments[x].combodate({
                minuteStep: 1
            });
        }
    }

    var startEndDatePicker = function (fromDate, toDate, fn) {
        if (typeof fromDate === 'undefined' || fromDate == null || typeof toDate === 'undefined' || toDate == null) {
            return;
        }
        var $fromDate = $("#" + fromDate);
        var $toDate = $("#" + toDate);
        $fromDate.datepicker({
            format: format,
            todayHighlight: true,
            autoclose: true,
        }).on('changeDate', function (selected) {
            var minDate = new Date(selected.date.valueOf());
            $toDate.datepicker('setStartDate', minDate);
            if (typeof fn !== "undefined" && fn != null && typeof $fromDate !== "undefined" &&
                    $fromDate.val() != "" && typeof $toDate !== "undefined" && $toDate.val() != "") {
                fn(getDate($fromDate.val()), getDate($toDate.val()));
            }
        });

        $toDate.datepicker({
            format: format,
            todayHighlight: true,
            autoclose: true
        }).on('changeDate', function (selected) {
            var maxDate = new Date(selected.date.valueOf());
            $fromDate.datepicker('setEndDate', maxDate);
            if (typeof fn !== "undefined" && fn != null && typeof $fromDate !== "undefined" &&
                    $fromDate.val() != "" && typeof $toDate !== "undefined" && $toDate.val() != "") {
                fn(getDate($fromDate.val()), getDate($toDate.val()));
            }
        });
    };

    var startEndDatePickerWithNepali = function (fromNepali, fromEnglish, toNepali, toEnglish, fn, setToDate) {

        var $fromNepaliDate = $('#' + fromNepali);
        var $fromEnglishDate = $('#' + fromEnglish);
        var $toNepaliDate = $('#' + toNepali);
        var $toEnglishDate = $('#' + toEnglish);

        var oldFromNepali = null;
        var oldtoNepali = null;

        $fromNepaliDate.nepaliDatePicker({
            npdMonth: true,
            npdYear: true,
            onChange: function () {
                var toVal = $toNepaliDate.val();
                if (toVal === 'undefined' || toVal == '') {
                    var temp = nepaliDatePickerExt.fromNepaliToEnglish($fromNepaliDate.val());
                    $fromEnglishDate.val(temp);
                    $toEnglishDate.datepicker('setStartDate', nepaliDatePickerExt.getDate(temp));
                    oldFromNepali = $fromNepaliDate.val();

                    //to set value of to date from value of from date
                    if (typeof setToDate !== "undefined" && setToDate != null && setToDate != false) {
                        $toEnglishDate.val(temp);
                        $toNepaliDate.val(oldFromNepali);
                    }
                } else {
                    var fromDate = nepaliDatePickerExt.fromNepaliToEnglish($fromNepaliDate.val());
                    var toDate = nepaliDatePickerExt.fromNepaliToEnglish($toNepaliDate.val());
                    try {
                        var fromEnglishStartDate = $fromEnglishDate.datepicker('getStartDate');
//                        if (fromEnglishStartDate !== -Infinity && (fromEnglishStartDate.getTime() > nepaliDatePickerExt.getDate(fromDate).getTime())) {
                        if (fromEnglishStartDate !== -Infinity && daysBetween(nepaliDatePickerExt.getDate(fromDate), fromEnglishStartDate) > 0) {
                            throw {message: 'The Selected Date cannot be less than ' + fromEnglishStartDate};
                        }
                        var fromEnglishEndDate = $fromEnglishDate.datepicker('getEndDate');
//                        if (fromEnglishEndDate !== Infinity && (fromEnglishEndDate.getTime() < nepaliDatePickerExt.getDate(fromDate).getTime())) {
                        if (fromEnglishEndDate !== Infinity && daysBetween(fromEnglishEndDate, nepaliDatePickerExt.getDate(fromDate)) > 0) {
                            throw {message: 'The Selected Date cannot be more than ' + fromEnglishEndDate};
                        }

//                        if (nepaliDatePickerExt.getDate(toDate).getTime() > nepaliDatePickerExt.getDate(fromDate).getTime()) {
                        if (daysBetween(nepaliDatePickerExt.getDate(fromDate), nepaliDatePickerExt.getDate(toDate)) >= 0) {
                            var temp = nepaliDatePickerExt.fromNepaliToEnglish($fromNepaliDate.val());
                            $fromEnglishDate.val(temp);
                            $toEnglishDate.datepicker('setStartDate', nepaliDatePickerExt.getDate(temp));
                            oldFromNepali = $fromNepaliDate.val();

                            if (typeof fn !== "undefined" && fn != null && typeof $fromEnglishDate !== "undefined" &&
                                    $fromEnglishDate.val() != "" && typeof $toEnglishDate !== "undefined" && $toEnglishDate.val() != "") {
                                fn(getDate($fromEnglishDate.val()), getDate($toEnglishDate.val()));
                            }

                        } else {
                            throw {message: "Selected Date should not exceed more than " + toVal};
                        }

                    } catch (e) {
                        errorMessage(e.message);
                        $fromNepaliDate.focus();
                        $fromNepaliDate.val(oldFromNepali);
                    }
                }
            }
        });

        $fromEnglishDate.datepicker({
            format: 'dd-M-yyyy',
            todayHighlight: true,
            autoclose: true
        }).on('changeDate', function () {
            oldFromNepali = nepaliDatePickerExt.fromEnglishToNepali($(this).val());
            $fromNepaliDate.val(oldFromNepali);
            var minDate = nepaliDatePickerExt.getDate($(this).val());
            $toEnglishDate.datepicker('setStartDate', minDate);

            //to set value of to date from value of from date
            if (typeof setToDate !== "undefined" && setToDate != null && setToDate != false) {
                $toEnglishDate.datepicker('update', $(this).val());
                oldtoNepali = nepaliDatePickerExt.fromEnglishToNepali($(this).val())
                $toNepaliDate.val(oldtoNepali);
            }

            if (typeof fn !== "undefined" && fn != null && typeof $fromEnglishDate !== "undefined" &&
                    $fromEnglishDate.val() != "" && typeof $toEnglishDate !== "undefined" && $toEnglishDate.val() != "") {
                fn(getDate($fromEnglishDate.val()), getDate($toEnglishDate.val()));
            }

        });

        $toNepaliDate.nepaliDatePicker({
            npdMonth: true,
            npdYear: true,
            onChange: function () {
                var fromVal = $fromNepaliDate.val();
                if (fromVal === 'undefined' || fromVal == '') {
                    var temp = nepaliDatePickerExt.fromNepaliToEnglish($toNepaliDate.val());
                    $toEnglishDate.val(temp);
                    $fromEnglishDate.datepicker('setEndDate', nepaliDatePickerExt.getDate(temp));
                    oldtoNepali = $toNepaliDate.val();
                } else {
                    var fromDate = nepaliDatePickerExt.fromNepaliToEnglish($fromNepaliDate.val());
                    var toDate = nepaliDatePickerExt.fromNepaliToEnglish($toNepaliDate.val());

                    try {
                        var toEnglishStartDate = $toEnglishDate.datepicker('getStartDate');
//                        if ((toEnglishStartDate !== -Infinity) && (toEnglishStartDate.getTime() > nepaliDatePickerExt.getDate(toDate).getTime())) {
                        if ((toEnglishStartDate !== -Infinity) && daysBetween(nepaliDatePickerExt.getDate(toDate), toEnglishStartDate) > 0) {
                            throw {message: 'The Selected Date cannot be less than ' + toEnglishStartDate};
                        }
                        var toEnglishEndDate = $toEnglishDate.datepicker('getEndDate');
//                        if (toEnglishEndDate !== Infinity && (toEnglishEndDate.getTime() < nepaliDatePickerExt.getDate(toDate).getTime())) {
                        if (toEnglishEndDate !== Infinity && daysBetween(toEnglishEndDate, nepaliDatePickerExt.getDate(toDate)) > 0) {
                            throw {message: 'The Selected Date cannot be more than ' + toEnglishEndDate};
                        }

//                        if (nepaliDatePickerExt.getDate(toDate).getTime() > nepaliDatePickerExt.getDate(fromDate).getTime()) {
                        if (daysBetween(nepaliDatePickerExt.getDate(fromDate), nepaliDatePickerExt.getDate(toDate)) >= 0) {
                            var temp = nepaliDatePickerExt.fromNepaliToEnglish($toNepaliDate.val());
                            $toEnglishDate.val(temp);
                            $fromEnglishDate.datepicker('setEndDate', nepaliDatePickerExt.getDate(temp));
                            oldtoNepali = $toNepaliDate.val();

                            if (typeof fn !== "undefined" && fn != null && typeof $fromEnglishDate !== "undefined" &&
                                    $fromEnglishDate.val() != "" && typeof $toEnglishDate !== "undefined" && $toEnglishDate.val() != "") {
                                fn(getDate($fromEnglishDate.val()), getDate($toEnglishDate.val()));
                            }

                        } else {
                            throw {message: "Selected Date should not preceed more than " + fromVal};
                        }

                    } catch (e) {
                        errorMessage(e.message);
                        $toNepaliDate.focus();
                        $toNepaliDate.val(oldtoNepali);
                    }


                }
            }
        });

        $toEnglishDate.datepicker({
            format: 'dd-M-yyyy',
            todayHighlight: true,
            autoclose: true
        }).on('changeDate', function () {
            oldtoNepali = nepaliDatePickerExt.fromEnglishToNepali($(this).val())
            $toNepaliDate.val(oldtoNepali);
            var maxDate = nepaliDatePickerExt.getDate($(this).val());
            $fromEnglishDate.datepicker('setEndDate', maxDate);

            if (typeof fn !== "undefined" && fn != null && typeof $fromEnglishDate !== "undefined" &&
                    $fromEnglishDate.val() != "" && typeof $toEnglishDate !== "undefined" && $toEnglishDate.val() != "") {
                fn(getDate($fromEnglishDate.val()), getDate($toEnglishDate.val()));
            }
        });

        $fromNepaliDate.on('input', function () {
            console.log('changed', this);
        });

        /*
         * 
         * check for fromEnglish input and toEnglish input is set or not and setting nepalidate.
         */
        var fromEnglishDateValue = $fromEnglishDate.val();
        var toEnglishDateValue = $toEnglishDate.val();
        if (typeof fromEnglishDateValue !== 'undefined' && fromEnglishDateValue !== null && fromEnglishDateValue !== '') {
            $fromNepaliDate.val(nepaliDatePickerExt.fromEnglishToNepali(fromEnglishDateValue));
        }
        if (typeof toEnglishDateValue !== 'undefined' && toEnglishDateValue !== null && toEnglishDateValue !== '') {
            $toNepaliDate.val(nepaliDatePickerExt.fromEnglishToNepali(toEnglishDateValue));
        }
        /*
         * 
         */

    };

    var datePickerWithNepali = function (englishDate, nepaliDate) {
        var $nepaliDate = $('#' + nepaliDate);
        var $englishDate = $('#' + englishDate);
        var oldNepali = null;

        $nepaliDate.nepaliDatePicker({
            npdMonth: true,
            npdYear: true,
            onChange: function () {
                var temp = nepaliDatePickerExt.fromNepaliToEnglish($nepaliDate.val());
                var englishStartDate = $englishDate.datepicker('getStartDate');
                var englishEndDate = $englishDate.datepicker('getEndDate');
                try {
                    if (englishStartDate !== -Infinity && englishStartDate.getTime() >= nepaliDatePickerExt.getDate(temp).getTime()) {
                        throw {message: 'The Selected Date cannot be less than ' + englishStartDate};
                    }
                    console.log('englishEndDate', englishEndDate);
                    if (englishEndDate !== Infinity && englishEndDate.getTime() <= nepaliDatePickerExt.getDate(temp).getTime()) {
                        throw {message: 'The Selected Date cannot be more than ' + englishEndDate};
                    }

                    $englishDate.val(temp);
                    oldNepali = $nepaliDate.val();

                } catch (e) {
                    errorMessage(e.message);
                    $nepaliDate.focus();
                    $nepaliDate.val(oldNepali);
                }


            }
        });

        $englishDate.datepicker({
            format: 'dd-M-yyyy',
            todayHighlight: true,
            autoclose: true
        }).on('changeDate', function () {
            $nepaliDate.val(nepaliDatePickerExt.fromEnglishToNepali($(this).val()));
        });

        var englishDateValue = $englishDate.val();
        if (typeof englishDateValue !== 'undefined' && englishDateValue !== null && englishDateValue !== '') {
            $nepaliDate.val(nepaliDatePickerExt.fromEnglishToNepali(englishDateValue));
        }

    };

    var addTimePicker = function () {
        for (var x in arguments) {
            arguments[x].timepicker({
                minuteStep: 1
            });
        }
    }

    var angularDatePicker = function () {
        $(this).datepicker({
            format: format,
            todayHighlight: true,
            autoclose: true,
            setDate: new Date()
        });
    };

    var successMessage = function (message, title) {
        if (typeof title === 'undefined') {
            title = "Notifications";
        }
        if (message && (message.length > 0)) {
            window.toastr.success(message[0], title);
        }
    };

    successMessage(document.messages);

    var errorMessage = function (message, title) {
        if (message) {
            window.toastr.error(message, title);
        }
    }
    var showMessage = function (message, type, title) {
        try {
            if (typeof message === 'undefined') {
                throw {message: 'No message provided.'};
            }
            if (typeof type === 'undefined') {
                type = 'info';
            } else if ($.inArray(type, ['info', 'success', 'error', 'warning']) === -1) {
                throw {message: 'Type defined must be info,success,error or warning.'};
            }
            if (typeof title === 'undefined') {
                title = "System Information";
            }

            window.toastr[type](message, title);

        } catch (e) {
            console.log('showMessage()=>', e.message);
        }
    };

    var floatingProfile = {
        minStatus: false,
        obj: document.querySelector('#floating-profile'),
        view: {
            name: $('#floating-profile  #name'),
            mobileNo: $('#floating-profile #mobileNo'),
            appDate: $('#floating-profile #appDate'),
            appBranch: $('#floating-profile #appBranch'),
            appDepartment: $('#floating-profile #appDepartment'),
            appDesignation: $('#floating-profile #appDesignation'),
            appPosition: $('#floating-profile #appPosition'),
            appServiceType: $('#floating-profile #appServiceType'),
            appServiceEventType: $('#floating-profile #appServiceEventType'),
            branch: $('#floating-profile #branch'),
            department: $('#floating-profile #department'),
            designation: $('#floating-profile #designation'),
            position: $('#floating-profile #position'),
            serviceType: $('#floating-profile #serviceType'),
            serviceEventType: $('#floating-profile #serviceEventType'),
            image: $('#floating-profile #profile-image'),
            header: $('#floating-profile #profile-header'),
            body: $('#floating-profile #profile-body'),
            minMaxBtn: $('#floating-profile #min-max-btn')
        },
        data: {
            firstName: null,
            middleName: null,
            lastName: null,
            apptDate: null,
            appBranch: null,
            appDepartment: null,
            appDesignation: null,
            appPosition: null,
            appServiceType: null,
            appServiceEventType: null,
            branch: null,
            department: null,
            designation: null,
            position: null,
            serviceType: null,
            serviceEventType: null,
            mobileNo: null,
            imageFilePath: null
        },
        makeDraggable: function () {
            $(this.obj).draggable();
        },
        show: function () {
            $(this.obj).show();
        },
        hide: function () {
            $(this.obj).hide();
        },
        setDataFromRemote: function (empId) {
            if (typeof empId === "undefined" || empId == null || empId < 0) {
                console.log("Unknown Employee Id");
                return;
            }
            var tempData = this.data;
            pullDataById(document.restfulUrl, {
                action: 'pullEmployeeDetailById',
                data: {employeeId: empId}
            }).then(function (success) {
                console.log("profile detail response", success);
                if (typeof success.data === "undefined" || success.data == null) {
                    return;
                }
                this.data.firstName = success.data['FIRST_NAME'];
                this.data.middleName = (success.data['MIDDLE_NAME'] == null) ? "" : success.data['MIDDLE_NAME'];
                this.data.lastName = success.data['LAST_NAME'];
                this.data.appDate = success.data['JOIN_DATE'];

                this.data.appBranch = success.data['APP_BRANCH'];
                this.data.appDepartment = success.data['APP_DEPARTMENT'];
                this.data.appDesignation = success.data['APP_DESIGNATION'];
                this.data.appPosition = (success.data['APP_POSITION'] == null) ? "" : success.data['APP_POSITION'];
                this.data.appServiceType = (success.data['APP_SERVICE_TYPE'] == null) ? "" : success.data['APP_SERVICE_TYPE'];
                this.data.appServiceEventType = (success.data['APP_SERVICE_EVENT_TYPE'] == null) ? "" : success.data['APP_SERVICE_EVENT_TYPE'];

                this.data.branch = (success.data['BRANCH'] == null) ? "" : success.data['BRANCH'];
                this.data.department = success.data['DEPARTMENT'];
                this.data.designation = success.data['DESIGNATION'];
                this.data.position = (success.data['POSITION'] == null) ? "" : success.data['POSITION'];
                this.data.serviceType = (success.data['SERVICE_TYPE'] == null) ? "" : success.data['SERVICE_TYPE'];
                this.data.serviceEventType = (success.data['SERVICE_EVENT_TYPE'] == null) ? "" : success.data['SERVICE_EVENT_TYPE'];

                this.data.mobileNo = (success.data['MOBILE_NO'] == null) ? "" : success.data['MOBILE_NO'];
                this.data.imageFilePath = (success.data['FILE_NAME'] == null) ? "" : success.data['FILE_NAME'];

                this.refreshView();
                this.show();
            }.bind(this), function (failure) {
                console.log(failure);
            });
        },
        setData: function (emp) {
            this.data = emp;
        },
        refreshView: function () {
            this.view.name.text(this.data.firstName + " " + this.data.middleName + " " + this.data.lastName);
            //this.view.gender.text(this.data.genderId == 1 ? "Male" : this.data.genderId == 2 ? "Female" : "Other");

            this.view.appDate.text(this.data.appDate);

            this.view.appBranch.text(this.data.appBranch);
            this.view.appDepartment.text(this.data.appDepartment);
            this.view.appDesignation.text(this.data.appDesignation);
            this.view.appPosition.text(this.data.appPosition);
            this.view.appServiceType.text(this.data.appServiceType);
            this.view.appServiceEventType.text(this.data.appServiceEventType);

            this.view.branch.text(this.data.branch);
            this.view.department.text(this.data.department);
            this.view.designation.text(this.data.designation);
            this.view.position.text(this.data.position);
            this.view.serviceType.text(this.data.serviceType);
            this.view.serviceEventType.text(this.data.serviceEventType);

            this.view.mobileNo.text(this.data.mobileNo);
            if (this.data.imageFilePath != null && (typeof this.data.imageFilePath !== "undefined") && this.data.imageFilePath.length >= 4) {
                this.view.image.attr('src', document.basePath + "/uploads/" + this.data.imageFilePath);
            } else {
                this.view.image.attr('src', document.basePath + "/img/profile_empty.jpg");
            }
        },
        minimize: function () {
            this.view.body.hide();
            this.view.minMaxBtn.removeClass("fa-minus");
            this.view.minMaxBtn.addClass("fa-plus");
//            $(this.obj).css("height", 20);
            this.view.body.hide();
            this.minStatus = true;
        },
        maximize: function () {
            this.view.body.show();
            this.view.minMaxBtn.removeClass("fa-plus");
            this.view.minMaxBtn.addClass("fa-minus");
//            $(this.obj).css("height", 320);
            this.view.body.show();
            this.minStatus = false;
        },
        initialize: function () {
//            this.makeDraggable();
            this.view.minMaxBtn.on("click", function () {
                if (this.minStatus) {
                    this.maximize();
                } else {
                    this.minimize();
                }
            }.bind(this));
        }
    };
    floatingProfile.initialize();

    var checkUniqueConstraints = function (inputFieldId, formId, tableName, columnName, checkColumnName, selfId, onSubmitFormSuccessfully) {
        $('#' + inputFieldId).on("blur", function () {
            var id = $(this);
            var nameValue = id.val();
            var parentId = id.parent(".form-group");
            var childId = parentId.children(".errorMsg");
            var columnsWidValues = {};
            columnsWidValues[columnName] = nameValue;
            var displayErrorMessage = function (formGroup, check, message, id) {
                var flag = formGroup.find('span.errorMsg').length > 0;
                if (flag) {
                    var errorMsgSpan = formGroup.find('span.errorMsg');
                    errorMsgSpan.each(function () {
                        if (check > 0) {
                            $(this).html(message);
                            id.focus();
                        } else {
                            $(this).remove();
                        }
                    });
                } else {
                    if (check > 0) {
                        var errorMsgSpan = $('<span />', {
                            "class": 'errorMsg',
                            text: message
                        });
                        formGroup.append(errorMsgSpan);
                        id.focus();
                    }
                }
            };

            window.app.pullDataById(document.restfulUrl, {
                action: 'checkUniqueConstraint',
                data: {
                    tableName: tableName,
                    selfId: selfId,
                    checkColumnName: checkColumnName,
                    columnsWidValues: columnsWidValues
                }
            }).then(function (success) {
                console.log("checkUniqueConstraint res", success);
                displayErrorMessage(parentId, parseInt(success.data), success.msg, id);
            }, function (failure) {
                console.log("checkUniqueConstraint failure", failure);
            });
        });

        $('#' + formId).submit(function (e) {
            var err = [];
            $(".errorMsg").each(function () {
                var erroMsg = $.trim($(this).html());
                if (erroMsg !== "") {
                    err.push("error");
                }
            });
            if (err.length > 0)
            {
                return false;
            }
            if (typeof onSubmitFormSuccessfully !== 'undefined') {
                var returnVal = onSubmitFormSuccessfully();
                if (typeof returnVal !== 'undefined') {
                    return returnVal;
                }
            }

        });
    };
    var setLoadingOnSubmit = function (formId, callback) {
        $('#' + formId).submit(function (e) {

            if (typeof callback !== "undefined") {
                var returnBool = callback();
                if (!returnBool) {
                    return false;
                }
            }

            App.blockUI({target: "#hris-page-content"});
        });
    }
    var checkErrorSpan = function (formId) {
        $('#' + formId).submit(function (e) {
            var err = [];
            $(".errorMsg").each(function () {
                var erroMsg = $.trim($(this).html());
                if (erroMsg !== "") {
                    err.push("error");
                }
            });
            if (err.length > 0)
            {
                return false;
            }
        });
    }
    var removeByAttr = function (arr, attr, value) {
        var i = arr.length;
        while (i--) {
            if (arr[i]
                    && arr[i].hasOwnProperty(attr)
                    && (arguments.length > 2 && arr[i][attr] === value)) {

                arr.splice(i, 1);

            }
        }
        return arr;
    };


    var UIConfirmations = function () {
        $(".confirmation").each(function () {
            var confirmationBtnId = $(this).attr("id");
            var id = confirmationBtnId.split("_").pop(-1);
            var href = $(this).attr("href");
            $(this).on("click", function (e) {
                e.preventDefault();
                $("#" + confirmationBtnId).confirmation('show');
            });

            $("#" + confirmationBtnId).on("confirmed.bs.confirmation", function () {
                //console.log(href);

//                window.app.pullDataById(document.deleteURL, {
//                    action: 'deleteContent',
//                    data: {
//                        'tableName': tableName,
//                        'columnName': columnName,
//                        'id': id
//                    }
//                }).then(function (success) {
//                    removeByAttr(listData, columnName, id);
//
//                    $("#" + kendoGridId).data('kendoGrid').dataSource.read();
//                    $("#" + kendoGridId).data('kendoGrid').refresh();
//                    window.toastr.success(success.msg, "Notifications");  
//                    window.app.UIConfirmations(tableName, columnName, kendoGridId, listData);
//                    
//                }, function (failure) {
//                    console.log(failure);
//                });
            }),
                    $("#" + confirmationBtnId).on("canceled.bs.confirmation", function () {
            });
        });
    };

    var displayErrorMessage = function (formGroup, check, message) {
        var flag = formGroup.find('span.errorMsg').length > 0;
        if (flag) {
            var errorMsgSpan = formGroup.find('span.errorMsg');
            errorMsgSpan.each(function () {
                if (check > 0) {
                    $(this).html(message);
                } else {
                    $(this).remove();
                }
            });
        } else {
            if (check > 0) {
                var errorMsgSpan = $('<span />', {
                    "class": 'errorMsg',
                    text: message
                });
                formGroup.append(errorMsgSpan);
            }
        }
    };

    function getDate(formattedDate) {
        var monthsInStringFormat = {
            1: 'Jan',
            2: 'Feb',
            3: 'Mar',
            4: 'Apr',
            5: 'May',
            6: 'Jun',
            7: 'Jul',
            8: 'Aug',
            9: 'Sep',
            10: 'Oct',
            11: 'Nov',
            12: 'Dec'
        };
        monthsInStringFormat.getKeyByValue = function (value) {
            for (var prop in this) {
                if (this.hasOwnProperty(prop)) {
                    if (this[ prop ].toUpperCase() === value.toUpperCase())
                        return prop;
                }
            }
        };
        var splittedDate = formattedDate.split("-");
//        return new Date(splittedDate[2], monthsInStringFormat.getKeyByValue(splittedDate[1]) - 1, parseInt(splittedDate[0]) + 1);
        return new Date(splittedDate[2], monthsInStringFormat.getKeyByValue(splittedDate[1]) - 1, parseInt(splittedDate[0]));
    }
    /* functionality not implemented */
    var $sidebarSearch = $('#sidebar-search');
    $sidebarSearch.on('submit', function (e) {
        errorMessage("Functionality not implemented!", "Notification");
        return false;
    });
    /* end of functionality not implemented */

    var getServerDate = function () {
        if (typeof document.restfulUrl === 'undefined') {
            console.log("Need to define restfulUrl first");
            return null;
        } else {
            var action = "getServerDate";
            return pullDataById(document.restfulUrl, {
                action: action
            });
        }
    };
    var scrollTo = function (id) {
        id = id.replace("link", "");
        $('html,body').animate({
            scrollTop: $("#" + id).offset().top - 50},
                500);
    };

    var daysBetween = function (first, second) {

        // Copy date parts of the timestamps, discarding the time parts.
        var one = new Date(first.getFullYear(), first.getMonth(), first.getDate());
        var two = new Date(second.getFullYear(), second.getMonth(), second.getDate());

        // Do the math.
        var millisecondsPerDay = 1000 * 60 * 60 * 24;
        var millisBetween = two.getTime() - one.getTime();
        var days = millisBetween / millisecondsPerDay;

        // Round down.
        return Math.floor(days);
    }

    var searchTable = function (kendoId, searchFields, Hidden) {
        var $searchHtml = $("<div class='row search' id='searchFieldDiv'>"
                + "<div class='col-sm-12'>"
                + "<input class='form-group pull-right' placeholder='search here' type='text' id='kendoSearchField' style='width:136px;padding:2px;font-size:12px;'/>"
                + "</div>"
                + "</div>");


        $searchHtml.insertBefore("#" + kendoId);

        if (typeof Hidden !== "undefined") {
            $("#searchFieldDiv").hide();
        }
        $("#kendoSearchField").keyup(function () {
            var val = $(this).val();
            var filters = [];
            for (var i = 0; i < searchFields.length; i++) {
                filters.push({
                    field: searchFields[i],
                    operator: "contains",
                    value: val
                });
            }

            $("#" + kendoId).data("kendoGrid").dataSource.filter({
                logic: "or",
                filters: filters
            });
        });


    }


    var pdfExport = function (kendoId, col) {

        // to create div for pdf table export
//        var $pdfExportdiv = $("<div id='pdfExportTable'></div>");
//        $pdfExportdiv.insertAfter("#" + kendoId);
//        document.body.appendChild($pdfExportdiv);

//             to create export pdf button
        var $pdfExportButton = $("<li>"
                + "<a href='javascript:;' id='exportPdf'>"
                + "<i class='fa fa-file-pdf-o' ></i> Export to PDF</a>"
                + "</li>");


        $pdfExportButton.insertAfter($("#export").parent());

        //to create template for export pdf

        var pdfkendoTemplate = "<script id='rowTemplatePDF' type='text/x-kendo-tmpl'><tr>";
        $.each(col, function (key, value) {
            if (key != 'MIDDLE_NAME' && key != 'LAST_NAME') {
                pdfkendoTemplate += "<td>";
                if (key == 'FIRST_NAME') {
                    pdfkendoTemplate += "#: (" + key + "== null) ? ' ' :" + key + "#";
                    pdfkendoTemplate += "#: (MIDDLE_NAME == null) ? ' ' : ' '+MIDDLE_NAME+' ' #";
                    pdfkendoTemplate += "#: (LAST_NAME == null) ? ' ' : LAST_NAME #";
                } else {
                    pdfkendoTemplate += " #: (" + key + "== null) ? ' ' :" + key + "#";
                }
                pdfkendoTemplate += "</td>";
            }
        });

        pdfkendoTemplate += "</tr></script>";
        $(pdfkendoTemplate).insertAfter("#rowTemplate");


        $("#exportPdf").click(function () {
            $("#pdfExportTable").show();

            var dataSource = $("#" + kendoId).data("kendoGrid").dataSource;
            var filteredDataSource = new kendo.data.DataSource({
                data: dataSource.data(),
                filter: dataSource.filter()
            });

            filteredDataSource.read();
            var data = filteredDataSource.view();

            var exportData = [];
            for (var i = 0; i < data.length; i++) {
                var tempData = {};
                $.each(col, function (key, value) {
                    tempData[key] = data[i][key];
                });
                exportData.push(tempData);

            }
//            console.log(exportData);
            var columns = [];
            $.each(col, function (key, value) {
//                var widthVal=100;
//                if(typeof(colWidth) != 'undefined'){ widthVal=colWidth[key]; }
                if (key != 'MIDDLE_NAME' && key != 'LAST_NAME') {
                    columns.push({field: key, title: value});
                }
            });

            $("#pdfExportTable").kendoGrid({
                dataSource: exportData,
                rowTemplate: $("#rowTemplatePDF").html(),
                columns: columns
            });

            kendo.drawing
                    .drawDOM("#pdfExportTable")
                    .then(function (group) {
                        kendo.drawing.pdf.saveAs(group, kendoId + ".pdf")
                        $("#pdfExportTable").hide();
                    });


        });


    }

    var populateSelect = function ($element, list, id, value, defaultMessage, selectedId) {
        $element.html('');
        $element.append($("<option></option>").val(-1).text(defaultMessage));
        var concatArray = function (keyList, list, concatWith) {
            var temp = '';
            if (typeof concatWith === 'undefined') {
                concatWith = ' ';
            }
            for (var i in keyList) {
                var listValue = list[keyList[i]];
                if (i == (keyList.length - 1)) {
                    temp = temp + ((listValue === null) ? '' : listValue);
                    continue;
                }
                temp = temp + ((listValue === null) ? '' : listValue) + concatWith;
            }

            return temp;
        };
        for (var i in list) {
            var text = null;
            if (typeof value === 'object') {
                text = concatArray(value, list[i], ' ');
            } else {
                text = list[i][value];
            }
            if (typeof selectedId !== 'undefined' && selectedId != null && selectedId == list[i][id]) {
                $element.append($("<option selected='selected'></option>").val(list[i][id]).text(text));
            } else {
                $element.append($("<option></option>").val(list[i][id]).text(text));
            }
        }
    };

    return {
        format: format,
        pullDataById: pullDataById,
        populateSelectElement: populateSelectElement,
        addDatePicker: addDatePicker,
        addTimePicker: addTimePicker,
        fetchAndPopulate: fetchAndPopulate,
        successMessage: successMessage,
        checkErrorSpan: checkErrorSpan,
        errorMessage: errorMessage,
        floatingProfile: floatingProfile,
        checkUniqueConstraints: checkUniqueConstraints,
        displayErrorMessage: displayErrorMessage,
        UIConfirmations: UIConfirmations,
        startEndDatePicker: startEndDatePicker,
        startEndDatePickerWithNepali: startEndDatePickerWithNepali,
        datePickerWithNepali: datePickerWithNepali,
        getSystemDate: getDate,
        addComboTimePicker: addComboTimePicker,
        getServerDate: getServerDate,
        setLoadingOnSubmit: setLoadingOnSubmit,
        scrollTo: scrollTo,
        showMessage: showMessage,
        daysBetween: daysBetween,
        searchTable: searchTable,
        pdfExport: pdfExport,
        populateSelect: populateSelect
    };
})(window.jQuery, window.toastr, window.App);

