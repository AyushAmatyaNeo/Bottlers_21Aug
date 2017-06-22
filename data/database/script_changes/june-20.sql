ALTER TABLE HRIS_EMPLOYEES ADD FULL_NAME VARCHAR2(255 BYTE);

CREATE TABLE HRIS_ATTENDANCE_PENALTY
  (
    ATTENDANCE_DT DATE,
    EMPLOYEE_ID   NUMBER(7,0),
    REASON        CHAR(1 BYTE) NOT NULL CHECK(REASON IN ('B','T')),
    ACTION        CHAR(1 BYTE) NOT NULL CHECK(ACTION IN ('A'))
  );
ALTER TABLE HRIS_ATTENDANCE_PENALTY ADD CONSTRAINT FK_ATTEN_PEN_EMP FOREIGN KEY(EMPLOYEE_ID) REFERENCES HRIS_EMPLOYEES(EMPLOYEE_ID);

-- 2
UPDATE HRIS_MENUS SET MENU_NAME='Asset Issue' WHERE MENU_NAME='Asset' AND MENU_ID=133
UPDATE HRIS_MENUS SET MENU_NAME='Asset Issue Report' WHERE MENU_NAME='Issue' AND MENU_ID=135

Insert into HRIS_MENUS (MENU_ID,MENU_NAME,PARENT_MENU,ROUTE,STATUS,CREATED_DT,ICON_CLASS,ACTION,MENU_INDEX,IS_VISIBLE) values 
(319,'View',133,'assetIssue','E',trunc(sysdate),'fa fa-pencil','view',3,'N');
