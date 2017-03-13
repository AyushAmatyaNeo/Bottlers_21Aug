<?php
namespace Appraisal\Repository;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Appraisal\Model\Question;
use Zend\Db\Sql\Select;
use Application\Repository\RepositoryInterface;

class QuestionRepository implements RepositoryInterface{
    private $tableGateway;
    private $adapter;
    
    public function __construct(AdapterInterface $adapter) {
        $this->tableGateway = new TableGateway(Question::TABLE_NAME,$adapter);
        $this->adapter = $adapter;
    }

    public function add(\Application\Model\Model $model) {
        $this->tableGateway->insert($model->getArrayCopyForDB());
    }

    public function delete($id) {
        $this->tableGateway->update([Question::STATUS=>'D'],[Question::QUESTION_ID=>$id]);
    }

    public function edit(\Application\Model\Model $model, $id) {
        $data = $model->getArrayCopyForDB();
        unset($data[Question::QUESTION_ID]);
        unset($data[Question::CREATED_DATE]);
        unset($data[Question::STATUS]);
        $this->tableGateway->update($data,[Question::QUESTION_ID=>$id]);
    }

    public function fetchAll() {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            new Expression("AQ.QUESTION_ID AS QUESTION_ID"), 
            new Expression("AQ.QUESTION_CODE AS QUESTION_CODE"),
            new Expression("AQ.QUESTION_EDESC AS QUESTION_EDESC"), 
            new Expression("AQ.QUESTION_NDESC AS QUESTION_NDESC"),
            new Expression("AQ.ANSWER_TYPE AS ANSWER_TYPE"),
            new Expression("AQ.APPRAISEE_FLAG AS APPRAISEE_FLAG"),
            new Expression("AQ.APPRAISER_FLAG AS APPRAISER_FLAG"),
            new Expression("AQ.REVIEWER_FLAG AS REVIEWER_FLAG"),
            new Expression("AQ.APPRAISEE_RATING AS APPRAISEE_RATING"),
            new Expression("AQ.APPRAISER_RATING AS APPRAISER_RATING"),
            new Expression("AQ.REVIEWER_RATING AS REVIEWER_RATING"),
            new Expression("AQ.MIN_VALUE AS MIN_VALUE"),
            new Expression("AQ.MAX_VALUE AS MAX_VALUE"),
            new Expression("AQ.REMARKS AS REMARKS"),
            new Expression("AQ.ORDER_NO AS ORDER_NO")
            ], true);
        $select->from(['AQ' => "HR_APPRAISAL_QUESTION"])
                ->join(['AH' => 'HR_APPRAISAL_HEADING'], 'AH.HEADING_ID=AQ.HEADING_ID', ["HEADING_EDESC"], "left");
        
        $select->where(["AQ.STATUS='E'"]);
        $select->order("AQ.QUESTION_EDESC");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result;
    }

    public function fetchById($id) {
        $rowset = $this->tableGateway->select([Question::QUESTION_ID => $id, Question::STATUS => 'E']);
        return $result = $rowset->current();
    }
    public function fetchByHeadingId($headingId){
        $rowset= $this->tableGateway->select(function(Select $select) use($headingId) {
            $select->where([Question::STATUS=>'E',Question::HEADING_ID=>$headingId]);
            $select->order(Question::QUESTION_ID." ASC");
        });
        $result = [];
        foreach($rowset as $row){
            array_push($result,$row);
        }
        return $result;
    }
}