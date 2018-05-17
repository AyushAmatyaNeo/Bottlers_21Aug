DROP TABLE HRIS_MONTHLY_VALUE_DETAIL;
DROP TABLE HRIS_POSITION_MONTHLY_VALUE;
DROP TABLE HRIS_MONTHLY_VALUE_SETUP;
DROP TABLE HRIS_FLAT_VALUE_DETAIL;
DROP TABLE HRIS_POSITION_FLAT_VALUE;
DROP TABLE HRIS_FLAT_VALUE_SETUP;
DROP TABLE HRIS_SALARY_SHEET_DETAIL;
DROP TABLE HRIS_SALARY_SHEET_EMP_DETAIL;
DROP TABLE HRIS_SALARY_SHEET;
DROP TABLE HRIS_PAY_SETUP;
CREATE TABLE "HRIS_MONTHLY_VALUE_SETUP"
  (
    "MTH_ID"      NUMBER(7,0),
    "MTH_CODE"    VARCHAR2(15 BYTE),
    "MTH_EDESC"   VARCHAR2(100 BYTE),
    "STATUS"      CHAR(1 BYTE),
    "CREATED_DT"  DATE,
    "MODIFIED_DT" DATE,
    "MTH_LDESC"   VARCHAR2(100 BYTE),
    "REMARKS"     VARCHAR2(255 BYTE),
    "CREATED_BY"  NUMBER(7,0),
    "MODIFIED_BY" NUMBER(7,0),
    "ASSIGN_TYPE" CHAR(1 BYTE) DEFAULT 'P'
  ) ;
ALTER TABLE "HRIS_MONTHLY_VALUE_SETUP" MODIFY ("MTH_EDESC" NOT NULL ENABLE);
ALTER TABLE "HRIS_MONTHLY_VALUE_SETUP" MODIFY ("STATUS" NOT NULL ENABLE);
ALTER TABLE "HRIS_MONTHLY_VALUE_SETUP" MODIFY ("CREATED_DT" NOT NULL ENABLE);
ALTER TABLE "HRIS_MONTHLY_VALUE_SETUP" ADD CHECK (STATUS IN ('E','D')) ENABLE;
ALTER TABLE "HRIS_MONTHLY_VALUE_SETUP" ADD PRIMARY KEY ("MTH_ID") ENABLE;
ALTER TABLE "HRIS_MONTHLY_VALUE_SETUP" MODIFY ("ASSIGN_TYPE" NOT NULL ENABLE);
ALTER TABLE "HRIS_MONTHLY_VALUE_SETUP" ADD CHECK (ASSIGN_TYPE IN ('P','E')) ENABLE;
CREATE TABLE "HRIS_MONTHLY_VALUE_DETAIL"
  (
    "MTH_ID"         NUMBER(7,0),
    "EMPLOYEE_ID"    NUMBER(7,0),
    "MTH_VALUE"      NUMBER,
    "CREATED_DT"     DATE,
    "MODIFIED_DT"    DATE,
    "FISCAL_YEAR_ID" NUMBER(7,0),
    "MONTH_ID"       NUMBER(7,0)
  ) ;
ALTER TABLE "HRIS_MONTHLY_VALUE_DETAIL" MODIFY ("CREATED_DT" NOT NULL ENABLE);
ALTER TABLE "HRIS_MONTHLY_VALUE_DETAIL" MODIFY ("FISCAL_YEAR_ID" NOT NULL ENABLE);
ALTER TABLE "HRIS_MONTHLY_VALUE_DETAIL" MODIFY ("MONTH_ID" NOT NULL ENABLE);
ALTER TABLE "HRIS_MONTHLY_VALUE_DETAIL" ADD CONSTRAINT "EMP_MTH_VAL_ID_FK" FOREIGN KEY ("EMPLOYEE_ID") REFERENCES "HRIS_EMPLOYEES" ("EMPLOYEE_ID") DISABLE;
ALTER TABLE "HRIS_MONTHLY_VALUE_DETAIL" ADD CONSTRAINT "FK_MTH_VAL_DET_FIS_YR_ID" FOREIGN KEY ("FISCAL_YEAR_ID") REFERENCES "HRIS_FISCAL_YEARS" ("FISCAL_YEAR_ID") ENABLE;
ALTER TABLE "HRIS_MONTHLY_VALUE_DETAIL" ADD CONSTRAINT "FK_MTH_VAL_DET_MON_ID" FOREIGN KEY ("MONTH_ID") REFERENCES "HRIS_MONTH_CODE" ("MONTH_ID") ENABLE;
ALTER TABLE "HRIS_MONTHLY_VALUE_DETAIL" ADD CONSTRAINT "MTH_VAL_DET_MTH_VAL_ID_FK" FOREIGN KEY ("MTH_ID") REFERENCES "HRIS_MONTHLY_VALUE_SETUP" ("MTH_ID") DISABLE;
CREATE TABLE "HRIS_POSITION_MONTHLY_VALUE"
  (
    "MTH_ID"         NUMBER(7,0),
    "POSITION_ID"    NUMBER(7,0),
    "MONTH_ID"       NUMBER(7,0),
    "ASSIGNED_VALUE" NUMBER
  ) ;
CREATE TABLE "HRIS_FLAT_VALUE_SETUP"
  (
    "FLAT_ID"     NUMBER(7,0),
    "FLAT_CODE"   VARCHAR2(15 BYTE),
    "FLAT_EDESC"  VARCHAR2(100 BYTE),
    "FLAT_LDESC"  VARCHAR2(100 BYTE),
    "CREATED_DT"  DATE,
    "MODIFIED_DT" DATE,
    "STATUS"      CHAR(1 BYTE),
    "REMARKS"     VARCHAR2(255 BYTE),
    "ASSIGN_TYPE" CHAR(1 BYTE) DEFAULT 'P'
  ) ;
ALTER TABLE "HRIS_FLAT_VALUE_SETUP" MODIFY ("FLAT_EDESC" NOT NULL ENABLE);
ALTER TABLE "HRIS_FLAT_VALUE_SETUP" MODIFY ("CREATED_DT" NOT NULL ENABLE);
ALTER TABLE "HRIS_FLAT_VALUE_SETUP" MODIFY ("STATUS" NOT NULL ENABLE);
ALTER TABLE "HRIS_FLAT_VALUE_SETUP" ADD CHECK (STATUS IN ('E','D')) ENABLE;
ALTER TABLE "HRIS_FLAT_VALUE_SETUP" ADD PRIMARY KEY ("FLAT_ID") ENABLE;
ALTER TABLE "HRIS_FLAT_VALUE_SETUP" MODIFY ("ASSIGN_TYPE" NOT NULL ENABLE);
ALTER TABLE "HRIS_FLAT_VALUE_SETUP" ADD CHECK (ASSIGN_TYPE IN ('P','E')) ENABLE;
CREATE TABLE "HRIS_FLAT_VALUE_DETAIL"
  (
    "FLAT_ID"        NUMBER(7,0),
    "EMPLOYEE_ID"    NUMBER(7,0),
    "FLAT_VALUE"     NUMBER,
    "CREATED_DT"     DATE,
    "MODIFIED_DT"    DATE,
    "FISCAL_YEAR_ID" NUMBER(7,0)
  ) ;
ALTER TABLE "HRIS_FLAT_VALUE_DETAIL" MODIFY ("CREATED_DT" NOT NULL ENABLE);
ALTER TABLE "HRIS_FLAT_VALUE_DETAIL" MODIFY ("FISCAL_YEAR_ID" NOT NULL ENABLE);
ALTER TABLE "HRIS_FLAT_VALUE_DETAIL" ADD CONSTRAINT "EMPLOYEE_FLAT_VAL_ID_FK" FOREIGN KEY ("EMPLOYEE_ID") REFERENCES "HRIS_EMPLOYEES" ("EMPLOYEE_ID") DISABLE;
ALTER TABLE "HRIS_FLAT_VALUE_DETAIL" ADD CONSTRAINT "FK_FLAT_VAL_DET_FIS_YR_ID" FOREIGN KEY ("FISCAL_YEAR_ID") REFERENCES "HRIS_FISCAL_YEARS" ("FISCAL_YEAR_ID") ENABLE;
ALTER TABLE "HRIS_FLAT_VALUE_DETAIL" ADD CONSTRAINT "FLAT_FLAT_VAL_DET_ID_FK" FOREIGN KEY ("FLAT_ID") REFERENCES "HRIS_FLAT_VALUE_SETUP" ("FLAT_ID") DISABLE;
CREATE TABLE "HRIS_POSITION_FLAT_VALUE"
  (
    "FLAT_ID"        NUMBER(7,0),
    "POSITION_ID"    NUMBER(7,0),
    "FISCAL_YEAR_ID" NUMBER(7,0),
    "ASSIGNED_VALUE" NUMBER
  ) ;
CREATE TABLE "HRIS_PAY_SETUP"
  (
    "PAY_ID"         NUMBER(7,0),
    "PAY_CODE"       VARCHAR2(15 BYTE),
    "PAY_EDESC"      VARCHAR2(100 BYTE),
    "PAY_LDESC"      VARCHAR2(100 BYTE),
    "PAY_TYPE_FLAG"  CHAR(1 BYTE),
    "PRIORITY_INDEX" NUMBER,
    "REMARKS"        VARCHAR2(255 BYTE),
    "CREATED_BY"     NUMBER(7,0),
    "CREATED_DT"     DATE,
    "MODIFIED_DT"    DATE,
    "MODIFIED_BY"    NUMBER(7,0),
    "STATUS"         CHAR(1 BYTE),
    "IS_MONTHLY"     CHAR(1 BYTE),
    "FORMULA" LONG
  ) ;
ALTER TABLE "HRIS_PAY_SETUP" MODIFY ("PAY_EDESC" NOT NULL ENABLE);
ALTER TABLE "HRIS_PAY_SETUP" MODIFY ("PAY_TYPE_FLAG" NOT NULL ENABLE);
ALTER TABLE "HRIS_PAY_SETUP" MODIFY ("PRIORITY_INDEX" NOT NULL ENABLE);
ALTER TABLE "HRIS_PAY_SETUP" MODIFY ("CREATED_BY" NOT NULL ENABLE);
ALTER TABLE "HRIS_PAY_SETUP" MODIFY ("CREATED_DT" NOT NULL ENABLE);
ALTER TABLE "HRIS_PAY_SETUP" MODIFY ("STATUS" NOT NULL ENABLE);
ALTER TABLE "HRIS_PAY_SETUP" ADD CHECK (STATUS IN ('E','D')) ENABLE;
ALTER TABLE "HRIS_PAY_SETUP" ADD PRIMARY KEY ("PAY_ID") ENABLE;
ALTER TABLE "HRIS_PAY_SETUP" MODIFY ("IS_MONTHLY" NOT NULL ENABLE);
ALTER TABLE "HRIS_PAY_SETUP" ADD CHECK (IS_MONTHLY IN ('Y','N')) ENABLE;
ALTER TABLE "HRIS_PAY_SETUP" MODIFY ("FORMULA" NOT NULL ENABLE);
ALTER TABLE "HRIS_PAY_SETUP" ADD CONSTRAINT "FK_PAY_EMPLOYEE_EMP_ID" FOREIGN KEY ("CREATED_BY") REFERENCES "HRIS_EMPLOYEES" ("EMPLOYEE_ID") DISABLE;
ALTER TABLE "HRIS_PAY_SETUP" ADD CONSTRAINT "FK_PAY_EMPLOYEE_EMP_ID2" FOREIGN KEY ("MODIFIED_BY") REFERENCES "HRIS_EMPLOYEES" ("EMPLOYEE_ID") DISABLE;
CREATE TABLE "HRIS_SALARY_SHEET"
  (
    "SHEET_NO"    NUMBER(7,0),
    "MONTH_ID"    NUMBER(7,0),
    "STATUS"      CHAR(2 BYTE),
    "REMARKS"     VARCHAR2(255 BYTE),
    "CREATED_DT"  DATE,
    "MODIFIED_DT" DATE,
    "YEAR"        NUMBER,
    "MONTH_NO"    NUMBER,
    "START_DATE"  DATE,
    "END_DATE"    DATE
  ) ;
ALTER TABLE "HRIS_SALARY_SHEET" MODIFY ("MONTH_ID" NOT NULL ENABLE);
ALTER TABLE "HRIS_SALARY_SHEET" MODIFY ("STATUS" NOT NULL ENABLE);
ALTER TABLE "HRIS_SALARY_SHEET" MODIFY ("CREATED_DT" NOT NULL ENABLE);
ALTER TABLE "HRIS_SALARY_SHEET" ADD CONSTRAINT SALARY_SHEET_STATUS_CHECK CHECK (STATUS IN ('CR','ST','FN','LC')) ENABLE;
ALTER TABLE "HRIS_SALARY_SHEET" ADD PRIMARY KEY ("SHEET_NO") ENABLE;
CREATE TABLE "HRIS_SALARY_SHEET_DETAIL"
  (
    "SHEET_NO"    NUMBER(20,0),
    "EMPLOYEE_ID" NUMBER(7,0),
    "PAY_ID"      NUMBER(7,0),
    "VAL"         NUMBER
  ) ;
ALTER TABLE "HRIS_SALARY_SHEET_DETAIL" ADD CONSTRAINT "SAL_SHE_DET_EMPLOYEE_ID_FK" FOREIGN KEY ("EMPLOYEE_ID") REFERENCES "HRIS_EMPLOYEES" ("EMPLOYEE_ID") DISABLE;
ALTER TABLE "HRIS_SALARY_SHEET_DETAIL" ADD CONSTRAINT "SAL_SHE_DET_PAY_ID_FK" FOREIGN KEY ("PAY_ID") REFERENCES "HRIS_PAY_SETUP" ("PAY_ID") DISABLE;
ALTER TABLE "HRIS_SALARY_SHEET_DETAIL" ADD CONSTRAINT "SAL_SHE_DET_SHEET_NO_FK" FOREIGN KEY ("SHEET_NO") REFERENCES "HRIS_SALARY_SHEET" ("SHEET_NO") DISABLE;
CREATE TABLE "HRIS_SALARY_SHEET_EMP_DETAIL"
  (
    "SHEET_NO"            NUMBER(7,0),
    "MONTH_ID"            NUMBER(7,0),
    "YEAR"                NUMBER,
    "MONTH_NO"            NUMBER,
    "START_DATE"          DATE,
    "END_DATE"            DATE,
    "TOTAL_DAYS"          NUMBER,
    "EMPLOYEE_ID"         NUMBER(7,0),
    "FULL_NAME"           VARCHAR2(255 BYTE),
    "DAYOFF"              NUMBER,
    "PRESENT"             NUMBER,
    "HOLIDAY"             NUMBER,
    "LEAVE"               NUMBER,
    "PAID_LEAVE"          NUMBER,
    "UNPAID_LEAVE"        NUMBER,
    "ABSENT"              NUMBER,
    "OVERTIME_HOUR"       NUMBER,
    "TRAVEL"              NUMBER,
    "TRAINING"            NUMBER,
    "WORK_ON_HOLIDAY"     NUMBER,
    "WORK_ON_DAYOFF"      NUMBER,
    "SALARY"              NUMBER(9,0),
    "MARITAL_STATUS"      CHAR(1 BYTE),
    "MARITAL_STATUS_DESC" VARCHAR2(9 BYTE),
    "GENDER_ID"           NUMBER(7,0),
    "GENDER_CODE"         CHAR(1 BYTE),
    "GENDER_NAME"         VARCHAR2(20 BYTE),
    "JOIN_DATE"           DATE,
    "COMPANY_ID"          NUMBER(7,0),
    "COMPANY_NAME"        VARCHAR2(255 BYTE),
    "BRANCH_ID"           NUMBER(7,0),
    "BRANCH_NAME"         VARCHAR2(255 BYTE),
    "DEPARTMENT_ID"       NUMBER(7,0),
    "DEPARTMENT_NAME"     VARCHAR2(255 BYTE),
    "DESIGNATION_ID"      NUMBER(7,0),
    "DESIGNATION_TITLE"   VARCHAR2(255 BYTE),
    "POSITION_ID"         NUMBER(7,0),
    "POSITION_NAME"       VARCHAR2(255 BYTE),
    "LEVEL_NO"            NUMBER(7,0),
    "SERVICE_TYPE_ID"     NUMBER(7,0),
    "SERVICE_TYPE_NAME"   VARCHAR2(255 BYTE),
    "SERVICE_TYPE"        VARCHAR2(64 BYTE),
    "PERMANENT_FLAG"      CHAR(1 BYTE),
    "PERMANENT_DATE"      DATE
  ) ;

ALTER TABLE HRIS_FLAT_VALUE_SETUP MODIFY( FLAT_CODE VARCHAR2(3 BYTE));

CREATE TABLE HRIS_FLAT_VALUE_DETAIL_MIG
  (
    FLAT_CODE     VARCHAR2(30 BYTE),
    EMPLOYEE_CODE VARCHAR2(30 BYTE),
    FLAT_VALUE    NUMBER
  );

CREATE TABLE HRIS_MONTHLY_VALUE_DETAIL_MIG
  (
    MTH_CODE       VARCHAR2(30 BYTE),
    EMPLOYEE_CODE  VARCHAR2(30 BYTE),
    PERIOD_DT_CODE VARCHAR2(2 BYTE),
    MTH_VALUE      NUMBER
  );

ALTER TABLE HRIS_PAY_SETUP ADD INCLUDE_IN_TAX CHAR(1 BYTE) DEFAULT 'Y' NOT NULL CHECK(INCLUDE_IN_TAX IN ('Y','N'));
--
ALTER TABLE HRIS_PAY_SETUP ADD INCLUDE_IN_SALARY CHAR(1 BYTE) DEFAULT 'Y' NOT NULL CHECK(INCLUDE_IN_SALARY IN ('Y','N'));
--
ALTER TABLE HRIS_PAY_SETUP ADD INCLUDE_PAST_VALUE CHAR(1 BYTE) DEFAULT 'Y' NOT NULL CHECK(INCLUDE_PAST_VALUE IN ('Y','N'));
--
ALTER TABLE HRIS_PAY_SETUP ADD INCLUDE_FUTURE_VALUE CHAR(1 BYTE) DEFAULT 'Y' NOT NULL CHECK(INCLUDE_FUTURE_VALUE IN ('Y','N'));


ALTER TABLE HRIS_PAY_SETUP DROP COLUMN IS_MONTHLY;

CREATE TABLE "HRIS_TAX_SHEET"
  (
    "SHEET_NO"    NUMBER(20,0),
    "EMPLOYEE_ID" NUMBER(7,0),
    "PAY_ID"      NUMBER(7,0),
    "VAL"         NUMBER
  ) ;



CREATE TABLE HRIS_SALARY_SHEET_GROUP
  (
    GROUP_ID    NUMBER(7,0) PRIMARY KEY,
    GROUP_CODE  VARCHAR2(15 BYTE),
    GROUP_NAME VARCHAR2(255 BYTE)
  );
ALTER TABLE HRIS_SALARY_SHEET ADD GROUP_ID NUMBER(7,0);
ALTER TABLE HRIS_SALARY_SHEET ADD COMPANY_ID NUMBER(7,0);


CREATE TABLE "HRIS_SS_PAY_VALUE_MODIFIED"
  (
    "MONTH_ID"    NUMBER(7,0),
    "EMPLOYEE_ID" NUMBER(7,0),
    "PAY_ID"      NUMBER(7,0),
    "VAL"         NUMBER
  ) ;