  ALTER TABLE HRIS_TRAINING_MASTER_SETUP MODIFY
  TRAINING_TYPE CHAR(2 BYTE) CHECK (TRAINING_TYPE IN ('CC','CP'));
    
    
  ALTER TABLE HRIS_TRAINING_MASTER_SETUP MODIFY
  TRAINING_TYPE CHECK (TRAINING_TYPE IN ('CC','CP'));

-- done on jwl_hris_apr5
DROP TABLE HRIS_EMPLOYEE_TRAINING_REQUEST;
CREATE TABLE "HRIS_EMPLOYEE_TRAINING_REQUEST"
  (
    "REQUEST_ID"          NUMBER(7,0) PRIMARY KEY,
    "REQUESTED_DATE"      DATE DEFAULT SYSDATE NOT NULL,
    "EMPLOYEE_ID"         NUMBER(7,0),
    "TRAINING_ID"         NUMBER(7,0),
    "TITLE"               VARCHAR2(255 BYTE),
    "DESCRIPTION"         VARCHAR2(255 BYTE),
    "TRAINING_TYPE"       CHAR(2 BYTE) CHECK (TRAINING_TYPE IN ('CC','CP')),
    "START_DATE"          DATE,
    "END_DATE"            DATE,
    "STATUS"              VARCHAR2(2 BYTE) NOT NULL CHECK (STATUS IN ('RQ','R','RC','AP','C')),
    "RECOMMENDED_BY"      NUMBER(7,0),
    "RECOMMENDED_DATE"    DATE,
    "RECOMMENDED_REMARKS" VARCHAR2(255 BYTE),
    "APPROVED_BY"         NUMBER(7,0),
    "APPROVED_DATE"       DATE,
    "APPROVED_REMARKS"    VARCHAR2(255 BYTE),
    "MODIFIED_DATE"       DATE,
    "REMARKS"             VARCHAR2(255 BYTE),
    "DURATION"            NUMBER(7,0)
  ) ;

-- done on jwl_hris_apr5
CREATE TABLE "HRIS_HOLIDAY_DESIGNATION"
  (
    "DESIGNATION_ID" NUMBER(7,0),
    "HOLIDAY_ID"     NUMBER(7,0)
  ) ;

-- done on jwl_hris_apr5
ALTER TABLE HRIS_LEAVE_MASTER_SETUP
ADD MAX_ACCUMULATE_DAYS NUMBER(7,0);

-- NOT DONE || SOMKALA PACHHAI || 26 APRIL
CREATE TABLE HRIS_FORGOT_PWD_DTL(
  EMPLOYEE_ID   NUMBER(7,0) NOT NULL,
  CODE          NUMBER(6,0) NOT NULL,
  EXPIRY_DATE   TIMESTAMP NOT NULL
);
ALTER TABLE HRIS_FORGOT_PWD_DTL ADD CONSTRAINT FK_FORGOT_PWD_DTL_EMP_EMP_ID FOREIGN KEY(EMPLOYEE_ID) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);
