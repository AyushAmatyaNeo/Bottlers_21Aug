-- PAYROLL MENU INSERT SCRIPTS

DELETE FROM HRIS_MENUS WHERE ROUTE ='payslip';
DELETE FROM HRIS_MENUS WHERE ROUTE ='payroll';

DELETE
FROM HRIS_MENUS
WHERE MENU_ID IN
  (SELECT MENU_ID
  FROM HRIS_MENUS
    START WITH MENU_ID       = 8
    CONNECT BY PRIOR MENU_ID = PARENT_MENU
  );
DELETE
FROM HRIS_ROLE_PERMISSIONS
WHERE MENU_ID NOT IN
  (SELECT MENU_ID FROM HRIS_MENUS
  );


Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,8,'Payroll',302,null,'javascript::','E',to_date('23-OCT-16','DD-MON-RR'),to_date('05-APR-17','DD-MON-RR'),'fa fa-folder','javascript::',10,null,null,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,35,'Flat Value',8,null,'javascript::','E',to_date('23-OCT-16','DD-MON-RR'),to_date('05-FEB-17','DD-MON-RR'),'fa fa-gear','javascript::',1,null,null,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,38,'Setup',35,null,'flatValue','E',to_date('23-OCT-16','DD-MON-RR'),to_date('06-APR-18','DD-MON-RR'),'fa fa-power-off','index',1,null,292,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,222,'add',38,null,'flatValue','E',to_date('04-APR-17','DD-MON-RR'),null,null,'add',1,null,null,'N');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,223,'edit',38,null,'flatValue','E',to_date('04-APR-17','DD-MON-RR'),null,null,'edit',2,null,null,'N');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,39,'Employee Wise Assign',35,null,'flatValue','E',to_date('23-OCT-16','DD-MON-RR'),to_date('06-APR-18','DD-MON-RR'),'fa fa-file-text-o','detail',2,null,292,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,351,'Position Wise Assign',35,null,'flatValue','E',to_date('23-MAR-18','DD-MON-RR'),to_date('06-APR-18','DD-MON-RR'),'fa fa-file-text-o','position-wise',3,null,292,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,36,'Monthly Value',8,null,'javascript::','E',to_date('23-OCT-16','DD-MON-RR'),to_date('05-FEB-17','DD-MON-RR'),'fa fa-gear','javascript::',2,null,null,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,40,'Setup',36,null,'monthlyValue','E',to_date('23-OCT-16','DD-MON-RR'),to_date('06-APR-18','DD-MON-RR'),'fa fa-power-off','index',1,null,292,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,224,'add',40,null,'monthlyValue','E',to_date('04-APR-17','DD-MON-RR'),null,null,'add',1,null,null,'N');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,225,'edit',40,null,'monthlyValue','E',to_date('04-APR-17','DD-MON-RR'),null,null,'edit',2,null,null,'N');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,41,'Employee Wise Assign',36,null,'monthlyValue','E',to_date('23-OCT-16','DD-MON-RR'),to_date('06-APR-18','DD-MON-RR'),'fa fa-file-text-o','detail',2,null,292,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,357,'Position Wise Assign',36,null,'monthlyValue','E',to_date('06-APR-18','DD-MON-RR'),null,'fa fa-square-o','position-wise',3,292,null,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,37,'Rules',8,null,'javascript::','E',to_date('23-OCT-16','DD-MON-RR'),to_date('05-FEB-17','DD-MON-RR'),'fa fa-gear','javascript::',3,null,null,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,42,'Setup',37,null,'rules','E',to_date('23-OCT-16','DD-MON-RR'),to_date('06-APR-18','DD-MON-RR'),'fa fa-power-off','index',1,null,292,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,226,'add',42,null,'rules','E',to_date('04-APR-17','DD-MON-RR'),null,null,'add',1,null,null,'N');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,227,'edit',42,null,'rules','E',to_date('04-APR-17','DD-MON-RR'),null,null,'edit',2,null,null,'N');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,51,'Salary',8,null,'javascript::','E',to_date('02-NOV-16','DD-MON-RR'),to_date('06-APR-18','DD-MON-RR'),'fa fa-money','javascript::',4,null,292,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,353,'Salary Sheet',51,null,'salarySheet','E',to_date('06-APR-18','DD-MON-RR'),null,'fa fa-square-o','index',1,292,null,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,354,'Pay Slip',51,null,'salarySheet','E',to_date('06-APR-18','DD-MON-RR'),null,'fa fa-square-o','payslip',2,292,null,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,352,'Tax',8,null,'javascript::','E',to_date('05-APR-18','DD-MON-RR'),to_date('06-APR-18','DD-MON-RR'),'fa fa-money','javascript::',5,null,292,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,355,'Tax Sheet',352,null,'taxSheet','E',to_date('06-APR-18','DD-MON-RR'),null,'fa fa-square-o','index',1,292,null,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,356,'Tax Slip',352,null,'taxSheet','E',to_date('06-APR-18','DD-MON-RR'),null,'fa fa-square-o','taxslip',2,292,null,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,364,'Pay Value Modified',51,null,'salarySheet','E',to_date('17-APR-18','DD-MON-RR'),null,'fa fa-square-o','pay-value-modified',3,292,null,'Y');

-- 

Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,3061,'Payroll',6,null,'payroll','E',to_date('13-APR-18','DD-MON-RR'),null,'fa fa-square-o','javascript::',99,292,null,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,3062,'Payslip',3061,null,'payroll','E',to_date('13-APR-18','DD-MON-RR'),null,'fa fa-square-o','payslip',1,292,null,'Y');
Insert into HRIS_MENUS (MENU_CODE,MENU_ID,MENU_NAME,PARENT_MENU,MENU_DESCRIPTION,ROUTE,STATUS,CREATED_DT,MODIFIED_DT,ICON_CLASS,ACTION,MENU_INDEX,CREATED_BY,MODIFIED_BY,IS_VISIBLE) values (null,3063,'Taxslip',3061,null,'payroll','E',to_date('13-APR-18','DD-MON-RR'),null,'fa fa-square-o','taxslip',2,292,null,'Y');


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
    (select max(menu_id)+1 from hris_menus),
    'Payroll Setup',
    8,
    NULL,
    'javascript::',
    'E',
    trunc(sysdate),
    NULL,
    'fa fa-pencil-square-o',
    'javascript::',
(select max(menu_index)+1 from hris_menus where Parent_Menu=8),
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
    (select max(menu_id)+1 from hris_menus),
    'Variance',
    (select menu_id from hris_menus  where menu_name like 'Payroll Setup%'),
    NULL,
    'varianceSetup',
    'E',
    trunc(sysdate),
    NULL,
    'fa fa-pencil-square-o',
    'index',
(select nvl(max(menu_index),0)+1 from hris_menus where 
Parent_Menu=(select menu_id from hris_menus  where menu_name like 'Payroll Setup%')),
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
    (select max(menu_id)+1 from hris_menus),
    'Payroll Reports',
    8,
    NULL,
    'javascript::',
    'E',
    trunc(sysdate),
    NULL,
    'fa fa-pencil-square-o',
    'javascript::',
(select max(menu_index)+1 from hris_menus where Parent_Menu=8),
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
    (select max(menu_id)+1 from hris_menus),
    'Variance',
    (select menu_id from hris_menus  where menu_name like 'Payroll Report%'),
    NULL,
    'payrollReport',
    'E',
    trunc(sysdate),
    NULL,
    'fa fa-pencil-square-o',
    'variance',
(select nvl(max(menu_index),0)+1 from hris_menus where 
Parent_Menu=(select menu_id from hris_menus  where menu_name like 'Payroll Report%')),
    NULL,
    NULL,
    'Y'
  );
