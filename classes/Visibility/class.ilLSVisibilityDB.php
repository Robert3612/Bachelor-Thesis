<?php

declare(strict_types=1);

/**
 * Storage for ilLSPostConditions
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
include_once("/var/www/html/ilias/Modules/LearningSequence/classes/Visibility/class.ilLSVisibility.php");
class ilLSVisibilityDB
{
    const TABLE_NAME = 'lso_visibility';
    const STD_ALWAYS_OPERATOR = 'nothing';

    /**
     * @var ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return ilLSPostCondition[]
     */
    public function select(array $ref_ids) : array
    {
        if (count($ref_ids) === 0) {
            return [];
        }

        $data = [];
        $query = "SELECT ref_id, operator, value, pre, vis" . PHP_EOL
            . "FROM " . static::TABLE_NAME . PHP_EOL
            . "WHERE ref_id IN ("
            . implode(',', $ref_ids)
            . ")";

        $result = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($result)) {
            $data[$row['ref_id']] = [$row['operator'], (int) $row['value'], $row['pre'], $row['vis']];
        }

        $conditions = [];
        foreach ($ref_ids as $ref_id) {
            //always-condition, standard
            $op = self::STD_ALWAYS_OPERATOR;
            $pre = "";
            $value = null;
            $vis = "false";

            //if from db: proper values
            if (array_key_exists($ref_id, $data)) {
                list($op, $value, $pre, $vis) = $data[$ref_id];
            }
            if($vis=="true"){
                $truevis = true;
            }else{
                $truevis = false;
            }

            $conditions[] = new \ilLSVisibility($ref_id, $op,$pre, $truevis ,$value);
        }
        return $conditions;
    }

    public function delete(array $ref_ids, \ilDBInterface $db = null)
    {
        if (count($ref_ids) === 0) {
            return;
        }

        if (is_null($db)) {
            $db = $this->db;
        }

        $query = "DELETE FROM " . static::TABLE_NAME . PHP_EOL
            . "WHERE ref_id IN ("
            . implode(',', $ref_ids)
            . ")";
        $db->manipulate($query);
    }

    protected function insert(array $ls_post_conditions, \ilDBInterface $db)
    {

        foreach ($ls_post_conditions as $condition) {
            if($condition->getVis()){
                $vis = "true";
            }else{
                $vis = "false";
            }
            $values = array(
                "ref_id" => array("integer", $condition->getRefId()),
                "value" => array("integer", 0),
                "operator" => array("text", $condition->getVisibilityOperator()),
                "pre" => array("text", $condition->getPre()),
                "vis" => array("text", $vis)
            );
            $db->insert(static::TABLE_NAME, $values);
        }
    }

    /**
     * @param ilLSPostCondition[]
     */
    public function upsert(array $ls_post_conditions)
    {
        if (count($ls_post_conditions) === 0) {
            return;
        }

        $ref_ids = array_map(
            function ($condition) {
                return (int) $condition->getRefId();
            },
            $ls_post_conditions
        );
        $ilAtomQuery = $this->db->buildAtomQuery();
        $ilAtomQuery->addTableLock(static::TABLE_NAME);
        $ilAtomQuery->addQueryCallable(
            function (\ilDBInterface $db) use ($ref_ids, $ls_post_conditions) {
                $this->delete($ref_ids, $db);
                $this->insert($ls_post_conditions, $db);
            }
        );
        $ilAtomQuery->run();
    }
}
