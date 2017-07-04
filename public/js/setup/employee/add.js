(function ($, app) {
    'use strict';
    $(document).ready(function () {

        var addrPermZoneId = $('#addrPermZoneId');
        var addrPermDistrictId = $('#addrPermDistrictId');
        var addrPermVdcMunicipalityId = $('#addrPermVdcMunicipalityId');

        if (addrPermZoneId.val() !== null) {
            if (typeof document.address !== 'undefined' && document.address.length !== 0 && typeof document.address.addrPermZoneId !== 'undefined') {
                addrPermZoneId.val(document.address.addrPermZoneId).trigger('change');
            }
            app.fetchAndPopulate(document.urlDistrict, addrPermZoneId.val(), addrPermDistrictId, function () {
                if (addrPermDistrictId.val() !== null) {
                    if (typeof document.address !== 'undefined') {
                        addrPermDistrictId.val(document.address.addrPermDistrictId).trigger('change');
                    }
                    app.fetchAndPopulate(document.urlMunicipality, addrPermDistrictId.val(), addrPermVdcMunicipalityId, function () {

                        addrPermZoneId.on('change', function () {
                            app.fetchAndPopulate(document.urlDistrict, addrPermZoneId.val(), addrPermDistrictId, function () {
                                if (addrPermDistrictId.val() !== null) {
                                    app.pullDataById(document.urlMunicipality, {id: addrPermDistrictId.val()}).then(function (data) {
                                        var nameList = [];
                                        $.each(data, function (key, item) {
                                            nameList.push(item);
                                        });
                                        addrPermVdcMunicipalityId.val("");
                                        addrPermVdcMunicipalityId.autocomplete({
                                            source: nameList
                                        });
                                    }, function (error) {
                                        console.log("Error fetching Districts", error);
                                    });
                                }
                            });
                        });

                        addrPermDistrictId.on('change', function () {
                            app.pullDataById(document.urlMunicipality, {id: addrPermDistrictId.val()}).then(function (data) {
                                var nameList = [];
                                $.each(data, function (key, item) {
                                    nameList.push(item);
                                });
                                addrPermVdcMunicipalityId.val("");
                                addrPermVdcMunicipalityId.autocomplete({
                                    source: nameList
                                });
                            }, function (error) {
                                console.log("Error fetching Districts", error);
                            });


                        });

                    });
                }
            });
        }

        var addrTempZoneId = $('#addrTempZoneId');
        var addrTempDistrictId = $('#addrTempDistrictId');
        var addrTempVdcMunicipality = $('#addrTempVdcMunicipality');

        if (addrTempZoneId.val() !== null) {
            if (typeof document.address !== 'undefined' && document.address.length !== 0 && typeof document.address.addrTempZoneId !== 'undefined') {
                addrTempZoneId.val(document.address.addrTempZoneId).trigger('change');
            }
            app.fetchAndPopulate(document.urlDistrict, addrTempZoneId.val(), addrTempDistrictId, function () {
                if (addrTempDistrictId.val() !== null) {
                    if (typeof document.address !== 'undefined') {
                        addrTempDistrictId.val(document.address.addrTempDistrictId).trigger('change');
                    }
                    app.fetchAndPopulate(document.urlMunicipality, addrTempDistrictId.val(), addrTempVdcMunicipality, function () {

                        addrTempZoneId.on('change', function () {
                            app.fetchAndPopulate(document.urlDistrict, addrTempZoneId.val(), addrTempDistrictId, function () {
                                if (addrTempDistrictId.val() !== null) {
                                    app.pullDataById(document.urlMunicipality, {id: addrTempDistrictId.val()}).then(function (data) {
                                        var nameList = [];
                                        $.each(data, function (key, item) {
                                            nameList.push(item);
                                        });
                                        addrTempVdcMunicipality.val("");
                                        addrTempVdcMunicipality.autocomplete({
                                            source: nameList
                                        });
                                    }, function (error) {
                                        console.log("Error fetching Districts", error);
                                    });
                                }
                            });
                        });

                        addrTempDistrictId.on('change', function () {
                            app.pullDataById(document.urlMunicipality, {id: addrTempDistrictId.val()}).then(function (data) {
                                var nameList = [];
                                $.each(data, function (key, item) {
                                    nameList.push(item);
                                });
                                addrTempVdcMunicipality.val("");
                                addrTempVdcMunicipality.autocomplete({
                                    source: nameList
                                });
                            }, function (error) {
                                console.log("Error fetching Districts", error);
                            });


                        });

                    });
                }
            });
        }


        $('#finishBtn').on('click', function () {
            if (typeof document.urlEmployeeList !== 'undefined') {
                location.href = document.urlSetupComplete;
            }
        });
        if (typeof document.currentTab !== "undefined") {
            $('#rootwizard').bootstrapWizard('show', parseInt(document.currentTab) - 1);
        }


        $('#filePath').on('change', function () {
            if (this.files && this.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    var previewUpload = $('#previewUpload');
                    previewUpload.attr('src', e.target.result);
                    if (previewUpload.hasClass('hidden')) {
                        previewUpload.removeClass('hidden');
                    }

                };
                reader.readAsDataURL(this.files[0]);
            }
        });
        $('form').bind('submit', function () {
            $(this).find(':disabled').removeAttr('disabled');
        });

//        var inputFieldId = "employeeCode";
//        var formId = "form1";
//        var tableName =  "HRIS_EMPLOYEES";
//        var columnName = "EMPLOYEE_CODE";
//        var checkColumnName = "EMPLOYEE_ID";
//        var selfId = $("#employeeId").val();
//        if (typeof(selfId) == "undefined"){
//            selfId=0;
//        }
//        window.app.checkUniqueConstraints(inputFieldId,formId,tableName,columnName,checkColumnName,selfId);
//
//        

        /*
         document.editDepartmentValue = <?php echo ($departmentId->getValue() == null) ? -1 : $departmentId->getValue(); ?>;
         */

        /* commented as no company wise branch, designation, position and department is needed
         var branchChangeFun = function () {
         var selectedBranchId = $('#branchId').val();
         $('#departmentId').html('');
         if (selectedBranchId > 0) {
         window.app.pullDataById(document.url, {
         action: 'pullDepartmentAccordingToBranch',
         data: {
         'branchId': selectedBranchId
         }
         }).then(function (success) {
         $.each(success.data, function (key, dep) {
         if (document.editDepartmentValue == key) {
         $('#departmentId').append($("<option selected='selected'></option>")
         .attr("value", key)
         .text(dep));
         } else {
         $('#departmentId').append($("<option></option>")
         .attr("value", key)
         .text(dep));
         }
         });
         
         }, function (failure) {
         console.log(failure);
         });
         
         } else {
         $('#departmentId').append($("<option></option>")
         .attr("value", "")
         .text("Select Branch First"));
         }
         }
         
         $('#branchId').on('change', function () {
         branchChangeFun();
         });
         
         branchChangeFun();
         */

        if (document.employeeId == document.selfEmployeeId) {
            app.lockField(true, ['birthdate', 'firstName', 'middleName', 'lastName', 'nameNepali', 'nepaliBirthDate', 'companyId', 'idCardNo', 'idThumbId', 'idLbrf', 'tab4', 'tab5', 'tab7', 'tab8']);
        }
    });

})(window.jQuery, window.app);


