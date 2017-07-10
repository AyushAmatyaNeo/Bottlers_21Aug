ALTER TABLE HRIS_ATTENDANCE_DETAIL
DROP COLUMN LATE_STATUS;
-- 
ALTER TABLE HRIS_ATTENDANCE_DETAIL ADD LATE_STATUS CHAR(1 BYTE) CHECK (LATE_STATUS IN ('L','E','B','N','X','Y'));

ALTER TABLE HRIS_ATTENDANCE ADD REMARKS VARCHAR(255 BYTE);

ALTER TABLE HRIS_APPRAISAL_STATUS
ADD (
REVIEW_PERIOD     VARCHAR2(255 BYTE),
PREVIOUS_REVIEW_PERIOD    VARCHAR2(255 BYTE),
PREVIOUS_RATING         VARCHAR2(255 BYTE)
);

ALTER TABLE HRIS_APPRAISAL_KPI MODIFY SUCCESS_CRITERIA VARCHAR2(4000 BYTE) NULL;

ALTER TABLE HRIS_LEAVE_MASTER_SETUP ADD ALLOW_GRACE_LEAVE CHAR(1 BYTE) DEFAULT 'N' NOT NULL CHECK(ALLOW_GRACE_LEAVE IN ('Y','N'));
ALTER TABLE HRIS_LEAVE_MASTER_SETUP ADD IS_MONTHLY CHAR(1 BYTE) DEFAULT 'N' NOT NULL CHECK(IS_MONTHLY IN ('Y','N'));

ALTER TABLE HRIS_EMPLOYEE_LEAVE_REQUEST ADD GRACE_PERIOD CHAR(1 BYTE) DEFAULT NULL CHECK(GRACE_PERIOD IN ('E','L'));

ALTER TABLE HRIS_TASK
DROP CONSTRAINT FK_TASK_BRA_BRA_ID;
ALTER TABLE HRIS_TASK
DROP CONSTRAINT FK_TASK_COMP_COMP_ID;

ALTER TABLE HRIS_APPRAISAL_SETUP 
ADD HR_FEEDBACK_ENABLE CHAR(1 BYTE) CHECK (HR_FEEDBACK_ENABLE IN ('Y','N'));

ALTER TABLE HRIS_APPRAISAL_STATUS
ADD HR_FEEDBACK VARCHAR2(255 BYTE)

ALTER TABLE HRIS_APPRAISAL_ASSIGN
ADD SUPER_REVIEWER_ID NUMBER(7,0);

ALTER TABLE HRIS_APPRAISAL_ASSIGN ADD CONSTRAINT FK_APP_ASN_EMP_EMP_ID FOREIGN KEY(SUPER_REVIEWER_ID) REFERENCES
HRIS_EMPLOYEES(EMPLOYEE_ID);

INSERT
INTO HRIS_MENUS
  (
    MENU_CODE,
    MENU_ID,
    MENU_NAME,
    PARENT_MENU,
    MENU_DESCRIPTION,
    ROUTE,
    STATUS,
    CREATED_DT,
    MODIFIED_DT,
    ICON_CLASS,
    ACTION,
    MENU_INDEX,
    CREATED_BY,
    MODIFIED_BY,
    IS_VISIBLE
  )
  VALUES
  (
  NULL,
    323,
    'Appraisal Final Review',
    5,
    NULL,
    'appraisal-final-review',
    'E',
      TRUNC(SYSDATE),
    NULL,
    'fa fa-pencil',
    'index',
    18,
    NULL,
    NULL,
    'Y'
    );
    
    
    INSERT
INTO HRIS_MENUS
  (
    MENU_CODE,
    MENU_ID,
    MENU_NAME,
    PARENT_MENU,
    MENU_DESCRIPTION,
    ROUTE,
    STATUS,
    CREATED_DT,
    MODIFIED_DT,
    ICON_CLASS,
    ACTION,
    MENU_INDEX,
    CREATED_BY,
    MODIFIED_BY,
    IS_VISIBLE
  )
  VALUES
  (
  NULL,
    324,
    'view',
    323,
    NULL,
    'appraisal-final-review',
    'E',
      TRUNC(SYSDATE),
    NULL,
    'fa fa-pencil',
    'view',
    1,
    NULL,
    NULL,
    'N'
    );

ALTER TABLE HRIS_APPRAISAL_STATUS 
ADD SUPER_REVIEWER_AGREE CHAR(1 BYTE) CHECK (SUPER_REVIEWER_AGREE IN ('Y','N'));

ALTER TABLE HRIS_APPRAISAL_STATUS
ADD SUPER_REVIEWER_FEEDBACK VARCHAR2(255 BYTE)




---trigger------
create or replace TRIGGER DEVICE_ATTENDANCE_TRIGGER AFTER
  INSERT ON HRIS_ATTENDANCE FOR EACH ROW DECLARE temp_in_time TIMESTAMP (6) := NULL;
  BEGIN
    BEGIN
      SELECT IN_TIME
      INTO temp_in_time
      FROM HRIS_ATTENDANCE_DETAIL
      WHERE ATTENDANCE_DT = TO_DATE (:new.ATTENDANCE_DT, 'DD-MON-YY')
      AND EMPLOYEE_ID     = :new.EMPLOYEE_ID;
    EXCEPTION
    WHEN NO_DATA_FOUND THEN
      DBMS_OUTPUT.PUT_LINE ('Attendance Job for '||:new.ATTENDANCE_DT||' not excecuted');
    END;
    IF (temp_in_time IS NULL) THEN
      UPDATE HRIS_ATTENDANCE_DETAIL
      SET IN_TIME         = TO_DATE ( TO_CHAR (:new.ATTENDANCE_TIME, 'DD-MON-YY HH:MI AM'), 'DD-MON-YY HH:MI AM'),
      IN_REMARKS = :new.REMARKS
      WHERE ATTENDANCE_DT = TO_DATE (:new.ATTENDANCE_DT, 'DD-MON-YY')
      AND EMPLOYEE_ID     = :new.EMPLOYEE_ID;
    ELSE
      UPDATE HRIS_ATTENDANCE_DETAIL
      SET OUT_TIME        = TO_DATE ( TO_CHAR (:new.ATTENDANCE_TIME, 'DD-MON-YY HH:MI AM'), 'DD-MON-YY HH:MI AM'),
      OUT_REMARKS = :new.REMARKS
      WHERE ATTENDANCE_DT = TO_DATE (:new.ATTENDANCE_DT, 'DD-MON-YY')
      AND EMPLOYEE_ID     = :new.EMPLOYEE_ID;
    END IF;
  END;
----trigger---


ALTER TABLE HRIS_FISCAL_YEARS ADD FISCAL_YEAR_NAME VARCHAR2(10 BYTE);

DECLARE
  FISCAL_YEAR_ID NUMBER;
  START_DATE     DATE;
  END_DATE       DATE;
  CURSOR YEARS
  IS
    SELECT FISCAL_YEAR_ID,START_DATE,END_DATE FROM HRIS_FISCAL_YEARS;
BEGIN
  OPEN YEARS;
  LOOP
    FETCH YEARS INTO FISCAL_YEAR_ID,START_DATE,END_DATE;
    EXIT
  WHEN YEARS%NOTFOUND;
    UPDATE HRIS_FISCAL_YEARS
    SET FISCAL_YEAR_NAME = CONCAT(TO_CHAR(START_DATE,'YYYY')||'/',TO_CHAR(END_DATE,'YYYY'));
  END LOOP;
  CLOSE YEARS;
END;

ALTER TABLE HRIS_LEAVE_MASTER_SETUP ADD IS_SUBSTITUTE_MANDATORY CHAR(1 BYTE) DEFAULT 'Y'NOT NULL CHECK (IS_SUBSTITUTE_MANDATORY IN ('Y','N'));

CREATE TABLE HRIS_TRVL_RECOMMENDER_APPROVER AS (SELECT * FROM HRIS_RECOMMENDER_APPROVER);

ALTER TABLE HRIS_TRVL_RECOMMENDER_APPROVER ADD CONSTRAINT FK_RECM_APRV_TRVL_EMP_EMP_ID FOREIGN KEY(EMPLOYEE_ID) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);
ALTER TABLE HRIS_TRVL_RECOMMENDER_APPROVER ADD CONSTRAINT FK_RECM_APRV_TRVL_EMP_EMP_ID1 FOREIGN KEY(CREATED_BY) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);
ALTER TABLE HRIS_TRVL_RECOMMENDER_APPROVER ADD CONSTRAINT FK_RECM_APRV_TRVL_EMP_EMP_ID2 FOREIGN KEY(MODIFIED_BY) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);
ALTER TABLE HRIS_TRVL_RECOMMENDER_APPROVER ADD CONSTRAINT FK_RECM_APRV_TRVL_EMP_EMP_ID3 FOREIGN KEY(RECOMMEND_BY) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);
ALTER TABLE HRIS_TRVL_RECOMMENDER_APPROVER ADD CONSTRAINT FK_RECM_APRV_TRVL_EMP_EMP_ID4 FOREIGN KEY(APPROVED_BY) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);

INSERT
INTO HRIS_MENUS
  (
    MENU_CODE,
    MENU_ID,
    MENU_NAME,
    PARENT_MENU,
    MENU_DESCRIPTION,
    ROUTE,
    STATUS,
    CREATED_DT,
    MODIFIED_DT,
    ICON_CLASS,
    ACTION,
    MENU_INDEX,
    CREATED_BY,
    MODIFIED_BY,
    IS_VISIBLE
  )
  VALUES
  (
    NULL,
    325,
    'Travel Approval Level',
    301,
    NULL,
    'recommenderApprover',
    'E',
    TRUNC(SYSDATE),
    NULL,
    'fa fa-pencil',
    'index',
    6,
    NULL,
    NULL,
    'Y'
  ) ; 
ALTER TABLE HRIS_EMPLOYEES
ADD IS_CEO CHAR (1 BYTE) CHECK (IS_CEO IN ('Y','N'));

ALTER TABLE HRIS_EMPLOYEES
ADD IS_HR CHAR (1 BYTE) CHECK (IS_HR IN ('Y','N'));

ALTER TABLE HRIS_LEAVE_MASTER_SETUP ADD ASSIGN_ON_EMPLOYEE_SETUP CHAR(1 BYTE) DEFAULT 'Y'NOT NULL CHECK (ASSIGN_ON_EMPLOYEE_SETUP IN ('Y','N'));
ALTER TABLE HRIS_LEAVE_MASTER_SETUP ADD IS_PRODATA_BASIS CHAR(1 BYTE) DEFAULT 'Y'NOT NULL CHECK (IS_PRODATA_BASIS IN ('Y','N'));