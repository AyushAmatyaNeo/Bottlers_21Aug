
-- HRIS_NEO | 26-MAR-2017 | UKESH
-- 
ALTER TABLE HRIS_ADVANCE_MASTER_SETUP 
ADD COMPANY_ID NUMBER(7,0);

ALTER TABLE HRIS_ADVANCE_MASTER_SETUP
ADD CONSTRAINT FK_ADV_MAS_SET_EMP_EMP_ID
FOREIGN KEY(COMPANY_ID) REFERENCES HRIS_COMPANY(COMPANY_ID);
--

-- HRIS_NEO | 26-MAR-2017 | UKESH
-- 
ALTER TABLE HRIS_DESIGNATIONS 
ADD COMPANY_ID NUMBER(7,0);

ALTER TABLE HRIS_DESIGNATIONS
ADD CONSTRAINT FK_DEGIGNATION_EMP_EMP_ID
FOREIGN KEY(COMPANY_ID) REFERENCES HRIS_COMPANY(COMPANY_ID);

-- 

-- HRIS_NEO | 26-MAR-2017 | UKESH
-- 
ALTER TABLE HRIS_EMAIL_TEMPLATE
ADD COMPANY_ID NUMBER(7,0);

ALTER TABLE HRIS_EMAIL_TEMPLATE
ADD CONSTRAINT FK_EMAIL_TEMP_EMP_EMP_ID
FOREIGN KEY(COMPANY_ID) REFERENCES HRIS_COMPANY(COMPANY_ID);

-- 

-- HRIS_NEO | 26-MAR-2017 | UKESH
-- 
ALTER TABLE HRIS_LEAVE_MASTER_SETUP
ADD COMPANY_ID NUMBER(7,0);

ALTER TABLE HRIS_LEAVE_MASTER_SETUP
ADD CONSTRAINT LEAVE_MASTER_EMP_EMP_ID
FOREIGN KEY(COMPANY_ID) REFERENCES HRIS_COMPANY(COMPANY_ID);

-- 

-- HRIS_NEO | 26-MAR-2017 | UKESH
-- 
ALTER TABLE HRIS_LOAN_MASTER_SETUP
ADD COMPANY_ID NUMBER(7,0);

ALTER TABLE HRIS_LOAN_MASTER_SETUP
ADD CONSTRAINT FK_LOAN_MASTER_EMP_EMP_ID
FOREIGN KEY(COMPANY_ID) REFERENCES HRIS_COMPANY(COMPANY_ID);

-- 


-- HRIS_NEO | 26-MAR-2017 | UKESH
-- 
ALTER TABLE HRIS_POSITIONS
ADD COMPANY_ID NUMBER(7,0);

ALTER TABLE HRIS_POSITIONS
ADD CONSTRAINT FK_POSITIONS_EMP_EMP_ID
FOREIGN KEY(COMPANY_ID) REFERENCES HRIS_COMPANY(COMPANY_ID);

-- 

-- HRIS_NEO | 26-MAR-2017 | UKESH
-- 

ALTER TABLE HRIS_SHIFTS
ADD COMPANY_ID NUMBER(7,0);

ALTER TABLE HRIS_SHIFTS
ADD CONSTRAINT FK_SHIFTS_EMP_EMP_ID
FOREIGN KEY(COMPANY_ID) REFERENCES HRIS_COMPANY(COMPANY_ID);

-- 

-- HRIS_NEO | 26-MAR-2017 | UKESH
--
ALTER TABLE HRIS_TRAINING_MASTER_SETUP
ADD COMPANY_ID NUMBER(7,0);

ALTER TABLE HRIS_TRAINING_MASTER_SETUP
ADD CONSTRAINT FK_TRA_MAS_SET_EMP_EMP_ID
FOREIGN KEY(COMPANY_ID) REFERENCES HRIS_COMPANY(COMPANY_ID);

--  

-- HRIS_NEO | 26-MAR-2017 | UKESH
--
ALTER TABLE HRIS_SHIFTS
DROP COLUMN LATE_IN;

ALTER TABLE HRIS_SHIFTS
ADD LATE_IN TIMESTAMP;

ALTER TABLE HRIS_SHIFTS
DROP COLUMN EARLY_OUT;

ALTER TABLE HRIS_SHIFTS
ADD EARLY_OUT TIMESTAMP;
-- 

-- HRIS_NEO | 26-MAR-2017 | UKESH
--
ALTER TABLE HRIS_SHIFTS
DROP COLUMN TOTAL_WORKING_HR;

ALTER TABLE HRIS_SHIFTS
ADD TOTAL_WORKING_HR TIMESTAMP DEFAULT TO_TIMESTAMP('00:00','HH24:MI') NOT NULL ;

ALTER TABLE HRIS_SHIFTS 
DROP COLUMN ACTUAL_WORKING_HR;

ALTER TABLE HRIS_SHIFTS
ADD ACTUAL_WORKING_HR TIMESTAMP DEFAULT TO_TIMESTAMP('00:00','HH24:MI') NOT NULL;

--
 
-- HRIS_NEO | 26-MAR-2017 | UKESH
--
CREATE TABLE "HRIS_ATTENDANCE_DEVICE" 
   ("DEVICE_ID" NUMBER(7,0) PRIMARY KEY, 
	"DEVICE_NAME" VARCHAR2(255 BYTE) NOT NULL, 
	"DEVICE_IP" VARCHAR2(255 BYTE) NOT NULL, 
	"DEVICE_LOCATION" VARCHAR2(255 BYTE) NOT NULL, 
	"ISACTIVE" CHAR(1 BYTE) DEFAULT 'Y' NOT NULL CHECK (ISACTIVE IN ('Y','N')), 
	"COMPANY_ID" NUMBER(7,0), 
	"BRANCH_ID" NUMBER(7,0)
   ) ;

  ALTER TABLE HRIS_ATTENDANCE_DEVICE ADD CONSTRAINT FK_ATT_DEV_BRA_BRA_ID FOREIGN KEY (BRANCH_ID)
	  REFERENCES HRIS_BRANCHES (BRANCH_ID) ;
 
  ALTER TABLE HRIS_ATTENDANCE_DEVICE ADD CONSTRAINT FK_ATT_DEV_COMP_COMP_ID FOREIGN KEY (COMPANY_ID)
	  REFERENCES HRIS_COMPANY(COMPANY_ID);



ALTER TABLE HRIS_ATTENDANCE_DEVICE 
ADD STATUS CHAR(1 BYTE) DEFAULT 'E' NOT NULL  CHECK (STATUS IN ('E','D'));
-- 

-- HRIS_NEO | 26-MAR-2017 | UKESH
--
CREATE TABLE HRIS_EMP_TRAVEL_EXPENSE_DTL
(
	ID			                    NUMBER(7,0) NOT NULL,	
	TRAVEL_ID			              NUMBER(7,0) NOT NULL,
	DEPARTURE_DATE              DATE NOT NULL,
  DEPARTURE_TIME              TIMESTAMP(6) NOT NULL,
  DEPARTURE_PLACE             VARCHAR2(255 BYTE) NOT NULL,
  DESTINATION_DATE            DATE NOT NULL,
  DESTINATION_TIME            TIMESTAMP(6) NOT NULL,
  DESTINATION_PLACE           VARCHAR2(255 BYTE) NOT NULL,
  TRANSPORT_TYPE              CHAR(2 BYTE) NOT NULL,
  FARE                        FLOAT NOT NULL,
  ALLOWANCE                   FLOAT NOT NULL,
  LOCAL_CONVEYENCE            FLOAT NOT NULL,
  MISC_EXPENSES               FLOAT NOT NULL,
  TOTAL_AMOUNT                FLOAT NOT NULL,
  REAMRKS                     VARCHAR2(255 BYTE),
	COMPANY_ID          	      NUMBER(7,0),	
	BRANCH_ID           	      NUMBER(7,0),
	CREATED_BY                  NUMBER(7,0),
	CREATED_DATE                DATE DEFAULT SYSDATE,
	MODIFIED_BY		              NUMBER(7,0),
	MODIFIED_DATE		            DATE,
	CHECKED					            VARCHAR2(1 BYTE) DEFAULT 'N',
	APPROVED_BY				          NUMBER(7,0),
	APPROVED_DATE			          DATE DEFAULT SYSDATE,
	APPROVED				            VARCHAR2(1 BYTE) DEFAULT 'N',
	STATUS        			        CHAR(1 BYTE),
	CONSTRAINT FK_TRL_EXP_DTL_COM_COM_ID FOREIGN KEY(COMPANY_ID) REFERENCES HRIS_COMPANY(COMPANY_ID),
	CONSTRAINT FK_TRL_EXP_DTL_BRN_BNC_ID FOREIGN KEY(BRANCH_ID) REFERENCES HRIS_BRANCHES(BRANCH_ID),
	CONSTRAINT PK_TRL_EXP_DTL PRIMARY KEY (ID),
	CONSTRAINT FK_TRL_EXP_DTL FOREIGN KEY(TRAVEL_ID) REFERENCES HRIS_EMPLOYEE_TRAVEL_REQUEST(TRAVEL_ID),
	CONSTRAINT FK_TRL_EXP_DTL_EMP_EMP_ID FOREIGN KEY(CREATED_BY) REFERENCES 	HRIS_EMPLOYEES(EMPLOYEE_ID),
	CONSTRAINT FK_TRL_EXP_DTL_EMP_EMP_ID2 FOREIGN KEY(MODIFIED_BY) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID),
	CONSTRAINT FK_TRL_EXP_DTL_EMP_EMP_ID3 FOREIGN KEY(APPROVED_BY) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID)
);


ALTER TABLE HRIS_EMP_TRAVEL_EXPENSE_DTL 
ADD
CONSTRAINT CHEK_TRANS CHECK (TRANSPORT_TYPE IN ('AP','OV','TI','BS'));

ALTER TABLE HRIS_EMPLOYEE_TRAVEL_REQUEST
ADD ( TRANSPORT_TYPE      CHAR(2 BYTE),
      DEPARTURE_DATE      DATE,
      RETURED_DATE        DATE
);


ALTER TABLE HRIS_EMP_TRAVEL_EXPENSE_DTL
RENAME COLUMN REAMRKS TO REMARKS;
-- 
-- HRIS_NEO | 27-MAR-2017 | UKESH
--
CREATE TABLE HRIS_USER_LOG
  (
    USER_ID    NUMBER(7,0) NOT NULL,
    LOGIN_IP   VARCHAR2(255 BYTE) NOT NULL,
    LOGIN_DATE TIMESTAMP DEFAULT SYSDATE NOT NULL
  );
CREATE TABLE HRIS_NEWS
  (
    NEWS_ID   NUMBER(12,0) PRIMARY KEY,
    NEWS_DATE DATE NOT NULL,
    NEWS_TYPE VARCHAR2(100 BYTE) NOT NULL CHECK(NEWS_TYPE IN ('NEWS','NOTICE','CIRCULAR','RULE','OTHERS')),
    NEWS_TITLE     VARCHAR2(255 BYTE) NOT NULL,
    NEWS_EDESC     VARCHAR2(3000 BYTE) NOT NULL,
    NEWS_LDESC     VARCHAR2(3000 BYTE) NOT NULL,
    REMARKS        VARCHAR2(400 BYTE) DEFAULT NULL,
    COMPANY_ID     NUMBER(7,0) NOT NULL,
    BRANCH_ID      NUMBER(7,0) DEFAULT NULL,
    DESIGNATION_ID NUMBER(7,0) DEFAULT NULL,
    DEPARTMENT_ID  NUMBER(7,0) DEFAULT NULL,
    CREATED_BY     NUMBER(7,0) NOT NULL,
    CREATED_DT     DATE DEFAULT SYSDATE NOT NULL,
    MODIFIED_BY    NUMBER(7,0),
    MODIFIED_DT    DATE,
    APPROVED_BY    NUMBER(7,0),
    APPROVED_DT    DATE ,
    STATUS         CHAR(1 BYTE) DEFAULT 'E' NOT NULL CHECK(STATUS IN ('E','D'))
  );
ALTER TABLE HRIS_NEWS ADD CONSTRAINT FK_NEWS_COM_COMPANY_ID FOREIGN KEY(COMPANY_ID) REFERENCES HRIS_COMPANY(COMPANY_ID);
ALTER TABLE HRIS_NEWS ADD CONSTRAINT FK_NEWS_BRA_BRANCH_ID FOREIGN KEY(BRANCH_ID) REFERENCES HRIS_BRANCHES(BRANCH_ID);
ALTER TABLE HRIS_NEWS ADD CONSTRAINT FK_NEWS_DESIG_DESIGNATION_ID FOREIGN KEY(DESIGNATION_ID) REFERENCES HRIS_DESIGNATIONS(DESIGNATION_ID);
ALTER TABLE HRIS_NEWS ADD CONSTRAINT FK_NEWS_DEPT_DEPT_ID FOREIGN KEY(DEPARTMENT_ID) REFERENCES HRIS_DEPARTMENTS(DEPARTMENT_ID);


ALTER TABLE HRIS_NEWS ADD CONSTRAINT FK_NEWS_EMP_EMPLOYEE_ID1 FOREIGN KEY(CREATED_BY) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);
ALTER TABLE HRIS_NEWS ADD CONSTRAINT FK_NEWS_EMP_EMPLOYEE_ID2 FOREIGN KEY(MODIFIED_BY) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);
ALTER TABLE HRIS_NEWS ADD CONSTRAINT FK_NEWS_EMP_EMPLOYEE_ID3 FOREIGN KEY(APPROVED_BY) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);
-- 

-- HRIS_NEO | 27-MAR-2017 | UKESH
-- 
TRUNCATE TABLE HRIS_ATTENDANCE;
ALTER TABLE HRIS_ATTENDANCE ADD CONSTRAINT UNQ_ATTENDANCE UNIQUE (EMPLOYEE_ID, ATTENDANCE_DT,ATTENDANCE_TIME);
-- 
  
-- HRIS_NEO | 27-MAR-2017 | SOMKALA
-- 
ALTER TABLE HRIS_EMP_TRAVEL_EXPENSE_DTL
MODIFY ALLOWANCE FLOAT NULL;

ALTER TABLE HRIS_EMP_TRAVEL_EXPENSE_DTL
MODIFY LOCAL_CONVEYENCE FLOAT NULL;

ALTER TABLE HRIS_EMP_TRAVEL_EXPENSE_DTL
MODIFY MISC_EXPENSES FLOAT NULL;
--

-- HRIS_NEO | 27-MAR-2017 | SOMKALA
ALTER TABLE HRIS_EMP_TRAVEL_EXPENSE_DTL
MODIFY ALLOWANCE FLOAT NULL;

ALTER TABLE HRIS_EMP_TRAVEL_EXPENSE_DTL
MODIFY LOCAL_CONVEYENCE FLOAT NULL;

ALTER TABLE HRIS_EMP_TRAVEL_EXPENSE_DTL
MODIFY MISC_EXPENSES FLOAT NULL;

ALTER TABLE HRIS_EMP_TRAVEL_EXPENSE_DTL
DROP(COMPANY_ID,BRANCH_ID,CHECKED,APPROVED,APPROVED_BY,APPROVED_DATE);

ALTER TABLE HRIS_EMPLOYEE_TRAVEL_REQUEST
RENAME COLUMN RETURED_DATE TO RETURNED_DATE
--

-- HRIS_NEO | 27-MAR-2017 | UKESH
-- HRIS_MODERN | 27-MAR-2017 | UKESH
-- 
ALTER TABLE HRIS_ATTENDANCE_DETAIL
ADD SHIFT_ID NUMBER(7,0) NOT NULL;

ALTER TABLE HRIS_ATTENDANCE_DETAIL
ADD CONSTRAINT FK_ATT_DET_SFT_SHIFT_ID
FOREIGN KEY(SHIFT_ID) REFERENCES HRIS_SHIFTS(SHIFT_ID);

ALTER TABLE HRIS_ATTENDANCE_DETAIL
ADD DAYOFF_FLAG CHAR(1 BYTE) DEFAULT 'N' NOT NULL CHECK(DAYOFF_FLAG IN ('Y','N'));
-- 

-- HRIS_NEO | 30-MAR-2017 | UKESH
-- 
CREATE TABLE HRIS_TASK
  (
    TASK_ID        NUMBER(12,0) PRIMARY KEY ,
    TASK_EDESC     VARCHAR2(500 BYTE) NOT NULL,
    TASK_NDESC     VARCHAR2(500 BYTE),
    START_DATE     DATE DEFAULT NULL,
    END_DATE       DATE DEFAULT NULL,
    ESTIMATED_TIME NUMBER(10,2) DEFAULT NULL,
    EMPLOYEE_ID    NUMBER(7,0) NOT NULL,
    STATUS         CHAR(1 BYTE) DEFAULT 'O' NOT NULL CHECK(STATUS        IN ('O','C')),
    TASK_PRIORITY  CHAR(1 BYTE) DEFAULT 'L' NOT NULL CHECK(TASK_PRIORITY IN ('L','M','H')),
    REMARKS        VARCHAR2(400 BYTE) DEFAULT NULL,
    COMPANY_ID     NUMBER(7,0),
    BRANCH_ID      NUMBER(7,0),
    CREATED_BY     NUMBER(7,0)  NOT NULL,
    CREATED_DT     DATE DEFAULT SYSDATE NOT NULL,
    MODIFIED_BY    NUMBER(7,0),
    MODIFIED_DT    DATE,
    APPROVED_FLAG  CHAR(1 BYTE) DEFAULT 'Y',
    APPROVED_BY    NUMBER(7,0),
    APPROVED_DATE  DATE,
    DELETED_FLAG   CHAR(1 BYTE) DEFAULT 'N' NOT NULL CHECK(DELETED_FLAG IN ('Y','N'))
  );

ALTER TABLE HRIS_TASK ADD CONSTRAINT FK_TASK_EMP_EMP_ID_1 FOREIGN KEY(EMPLOYEE_ID) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);
ALTER TABLE HRIS_TASK ADD CONSTRAINT FK_TASK_COMP_COMP_ID FOREIGN KEY(EMPLOYEE_ID) REFERENCES HRIS_COMPANY(COMPANY_ID);
ALTER TABLE HRIS_TASK ADD CONSTRAINT FK_TASK_BRA_BRA_ID FOREIGN KEY(BRANCH_ID) REFERENCES HRIS_BRANCHES(BRANCH_ID);
ALTER TABLE HRIS_TASK ADD CONSTRAINT FK_TASK_EMP_EMP_ID_2 FOREIGN KEY(CREATED_BY) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);
ALTER TABLE HRIS_TASK ADD CONSTRAINT FK_TASK_EMP_EMP_ID_3 FOREIGN KEY(MODIFIED_BY) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);
ALTER TABLE HRIS_TASK ADD CONSTRAINT FK_TASK_EMP_EMP_ID_4 FOREIGN KEY(APPROVED_BY) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);


-- 

-- HRIS_NEO || 2017-03-30
-- 
CREATE TABLE HRIS_LEAVE_SUBSTITUTE
  (
    LEAVE_REQUEST_ID NUMBER(7,0) NOT NULL,
    EMPLOYEE_ID      NUMBER(7,0) NOT NULL,
    REMARKS          VARCHAR2(400 BYTE) DEFAULT NULL,
    CREATED_BY       NUMBER(7,0) NOT NULL,
    CREATED_DT       DATE DEFAULT SYSDATE NOT NULL,
    APPROVED_FLAG    CHAR(1 BYTE) DEFAULT NULL CHECK(APPROVED_FLAG IN ('Y','N')),
    APPROVED_DATE    DATE,
    STATUS           CHAR(1 BYTE) DEFAULT 'N' CHECK (STATUS IN ('E','D'))
  );
  
ALTER TABLE HRIS_LEAVE_SUBSTITUTE ADD CONSTRAINT FK_LEAVESUB_EMP_EMP_ID_1 FOREIGN KEY(EMPLOYEE_ID) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);
ALTER TABLE HRIS_LEAVE_SUBSTITUTE ADD CONSTRAINT FK_LEAVESUB_LREQUEST_ID FOREIGN KEY(LEAVE_REQUEST_ID) REFERENCES HRIS_EMPLOYEE_LEAVE_REQUEST(ID);
ALTER TABLE HRIS_LEAVE_SUBSTITUTE ADD CONSTRAINT FK_LEAVESUB_EMP_EMP_ID_2 FOREIGN KEY(CREATED_BY) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);


ALTER TABLE HRIS_EMPLOYEE_LEAVE_REQUEST ADD CONSTRAINT PK_LEAVE_REQUEST_ID PRIMARY KEY(ID);

  
  
CREATE TABLE HRIS_TRAVEL_SUBSTITUTE
  (
    TRAVEL_ID          NUMBER(12) NOT NULL,
    EMPLOYEE_ID      NUMBER(7,0) NOT NULL,
    REMARKS          VARCHAR2(400 BYTE) DEFAULT NULL,
    CREATED_BY       NUMBER(7,0) NOT NULL,
    CREATED_DT       DATE DEFAULT SYSDATE NOT NULL,
    APPROVED_FLAG    CHAR(1 BYTE) DEFAULT NULL CHECK(APPROVED_FLAG IN ('Y','N')),
    APPROVED_DATE    DATE,
    STATUS  CHAR(1 BYTE) DEFAULT 'N' CHECK (STATUS IN ('E','D'))
  );
  

ALTER TABLE HRIS_TRAVEL_SUBSTITUTE ADD CONSTRAINT FK_TRAVELSUB_EMP_EMP_ID_1 FOREIGN KEY(EMPLOYEE_ID) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);
ALTER TABLE HRIS_TRAVEL_SUBSTITUTE ADD CONSTRAINT FK_TRAVELSUB_TREQUEST_ID FOREIGN KEY(TRAVEL_ID) REFERENCES HRIS_EMPLOYEE_TRAVEL_REQUEST(TRAVEL_ID);
ALTER TABLE HRIS_TRAVEL_SUBSTITUTE ADD CONSTRAINT FK_TRAVELSUB_EMP_EMP_ID_2 FOREIGN KEY(CREATED_BY) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);



-- 


-- NEO-HRIS | SOMKALA PACHHAI | MARCH 31 2017
ALTER TABLE HRIS_LEAVE_SUBSTITUTE
RENAME COLUMN CREATED_DT TO CREATED_DATE;

ALTER TABLE HRIS_TRAVEL_SUBSTITUTE
RENAME COLUMN CREATED_DT TO CREATED_DATE;
--


-- HRIS_NEO | 26-MAR-2017 | PRABIN
-- 

ALTER TABLE HRIS_TASK
ADD TASK_TITLE  VARCHAR2(100 BYTE) NOT NULL;


--ITNEPAL_HRIS_APR2 |2nd april 2017| PRABIN

ALTER TABLE HRIS_EMPLOYEES
MODIFY  ID_CARD_NO VARCHAR2(100 BYTE);




