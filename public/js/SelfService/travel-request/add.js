(function ($, app) {
    'use strict';
    $(document).ready(function () {
        $('select#form-transportType').select2();
//        app.startEndDatePicker('fromDate', 'toDate');
        app.startEndDatePickerWithNepali('nepaliStartDate1', 'form-fromDate', 'nepaliEndDate1', 'form-toDate')
        /* prevent past event post */
        $('#form-fromDate').datepicker("setStartDate", new Date());
        $('#form-toDate').datepicker("setStartDate", new Date());
        /* end of  prevent past event post */
        
        var inputFieldId = "form-travelCode";
        var formId = "travelRequest-form";
        var tableName =  "HRIS_EMPLOYEE_TRAVEL_REQUEST";
        var columnName = "TRAVEL_CODE";
        var checkColumnName = "TRAVEL_ID";
        var selfId = $("#travelId").val();
        if (typeof(selfId) == "undefined"){
            selfId='R';
        }
        window.app.checkUniqueConstraints(inputFieldId,formId,tableName,columnName,checkColumnName,selfId);
    });
})(window.jQuery, window.app);
