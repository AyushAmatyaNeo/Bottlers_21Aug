
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
ADD IS_CEO CHAR (1 BYTE) DEFAULT 'N' CHECK (IS_CEO IN ('Y','N'));

ALTER TABLE HRIS_EMPLOYEES
ADD IS_HR CHAR (1 BYTE) DEFAULT 'N' CHECK (IS_HR IN ('Y','N'));


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
    'EMP',
    325,
    'Edit My Profile',
    53,
    NULL,
    'employee',
    'E',
    to_date('13-JUL-17','DD-MON-RR'),
    NULL,
    'fa fa-wrench',
    'edit',
    44,
    NULL,
    NULL,
    'N'
  );
  

  
  
INSERT INTO HRIS_ROLE_PERMISSIONS
  (MENU_ID,ROLE_ID,STATUS
  )
SELECT (325),ROLE_ID, ('E') FROM HRIS_ROLES;


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
    328,
    'Attendance Report',
    5,
    NULL,
    'managerReport',
    'E',
    TRUNC(SYSDATE),
    NULL,
    'fa fa-pencil',
    'index',
    19,
    NULL,
    NULL,
    'Y'
  );

ALTER TABLE HRIS_HOLIDAY_MASTER_SETUP 
ADD ASSIGN_ON_EMPLOYEE_SETUP CHAR(1 BYTE) DEFAULT 'Y'NOT NULL CHECK (ASSIGN_ON_EMPLOYEE_SETUP IN ('Y','N'));



ALTER TABLE HRIS_TRAINING_MASTER_SETUP ADD IS_WITHIN_COMPANY CHAR( 1 BYTE) DEFAULT 'Y' NOT NULL CHECK (IS_WITHIN_COMPANY IN ('Y','N'));

ALTER TABLE HRIS_EMPLOYEES ADD ADDR_PERM_DISTRICT_ID NUMBER(7,0) ;
ALTER TABLE HRIS_EMPLOYEES ADD CONSTRAINT FK_EMP_DISTRICT_ID_1 FOREIGN KEY(ADDR_PERM_DISTRICT_ID) REFERENCES HRIS_DISTRICTS(DISTRICT_ID);
ALTER TABLE HRIS_EMPLOYEES ADD ADDR_PERM_ZONE_ID NUMBER(7,0);
ALTER TABLE HRIS_EMPLOYEES ADD CONSTRAINT FK_EMP_ZONE_ID FOREIGN KEY(ADDR_PERM_ZONE_ID) REFERENCES HRIS_ZONES(ZONE_ID);
ALTER TABLE HRIS_EMPLOYEES ADD ADDR_TEMP_DISTRICT_ID NUMBER(7,0);
ALTER TABLE HRIS_EMPLOYEES ADD CONSTRAINT FK_EMP_DISTRICT_ID_2 FOREIGN KEY (ADDR_TEMP_DISTRICT_ID) REFERENCES HRIS_DISTRICTS(DISTRICT_ID);
ALTER TABLE HRIS_EMPLOYEES ADD ADDR_TEMP_ZONE_ID NUMBER(7,0);
ALTER TABLE HRIS_EMPLOYEES ADD CONSTRAINT FK_EMP_ZONE_ID_2 FOREIGN KEY (ADDR_TEMP_ZONE_ID) REFERENCES HRIS_ZONES(ZONE_ID);


CREATE TABLE HRIS_PAY_EMPLOYEE_SETUP
  (
    PAY_ID      NUMBER(7,0) NOT NULL,
    EMPLOYEE_ID NUMBER(7,0) NOT NULL,
    CONSTRAINT FK_PAY_EMP_PAY_ID FOREIGN KEY(PAY_ID) REFERENCES HRIS_PAY_SETUP(PAY_ID),
    CONSTRAINT FK_PAY_EMP_EMP_ID FOREIGN KEY(EMPLOYEE_ID) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID)
  );
  DROP TABLE HRIS_PAY_POSITION_SETUP;

ALTER TABLE HRIS_EMPLOYEES ADD PERMANENT_DATE DATE;

ALTER TABLE HRIS_FLAT_VALUE_DETAIL DROP COLUMN MONTH_ID;

ALTER TABLE HRIS_JOB_HISTORY ADD FROM_SALARY NUMBER(11,2);

ALTER TABLE HRIS_JOB_HISTORY ADD TO_SALARY NUMBER(11,2);


ALTER TABLE HRIS_USERS
MODIFY PASSWORD VARCHAR2(64);

ALTER TABLE HRIS_USERS
ADD IS_LOCKED CHAR(1 BYTE) DEFAULT ('N') NOT NULL;