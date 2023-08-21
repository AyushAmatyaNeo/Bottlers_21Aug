create or replace PROCEDURE HRIS_MIG_COMPANY
AS
V_COMPANY_ID NUMBER;
V_STATUS CHAR(1 BYTE);
BEGIN
FOR V_COMPANY_LIST IN (SELECT * FROM  COMPANY_SETUP)
LOOP


BEGIN
SELECT COMPANY_ID INTO V_COMPANY_ID FROM HRIS_COMPANY WHERE COMPANY_ID=V_COMPANY_LIST.COMPANY_CODE;
EXCEPTION
  WHEN NO_DATA_FOUND THEN
BEGIN
IF(V_COMPANY_LIST.DELETED_FLAG='N') THEN
V_STATUS:='E';
ELSE
V_STATUS:='D';
END IF;


INSERT INTO HRIS_COMPANY
(COMPANY_ID,COMPANY_CODE,COMPANY_NAME,ADDRESS,TELEPHONE,FAX,CREATED_DT,STATUS)
VALUES
(V_COMPANY_LIST.COMPANY_CODE,
V_COMPANY_LIST.COMPANY_CODE,
V_COMPANY_LIST.COMPANY_EDESC,
V_COMPANY_LIST.ADDRESS,
V_COMPANY_LIST.TELEPHONE,
V_COMPANY_LIST.FAX,
TRUNC(SYSDATE),
V_STATUS
);

END;

END;








END LOOP;
END;