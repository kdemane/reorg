<?php
require_once 'application/classes/base.php';

class hospital extends base
{
    private $external_id
          , $name;

    function extract(&$payment)
    {
        $this->_reset();

        if ($num_extracted = $this->_extract_raw_from_payment($payment,
                                                              ['teaching_hospital_id'   => TRUE,
                                                               'teaching_hospital_name' => TRUE]))
        {
            $this->_import();
            return $num_extracted;
        }

        return FALSE;
    }

    private function _import()
    {
        $this->external_id = $this->raw['teaching_hospital_id'];
        $this->name        = $this->raw['teaching_hospital_name'];
    }

    private function _reset()
    {
        $this->raw = [];
        unset($this->external_id);
        unset($this->name);
    }

    function save()
    {
        if ($this->_validate())
        {
            $sql = "INSERT INTO hospital
                    (
                        external_id,
                        name
                    )
                    VALUES
                    (
                        :external_id,
                        :name
                    )
                    ON DUPLICATE KEY
                    UPDATE
                        external_id = IFNULL(external_id, VALUES(external_id))";

            if (!$this->db->exec($sql, [':external_id' => $this->external_id,
                                        ':name'        => $this->name]))
            {
                die("Insert blew up");
            }
        }
    }

    private function _validate()
    {
        return !empty($this->external_id) || !empty($this->name);
    }

    public static function get(&$db, $external_id)
    {
        if ($rows = $db->select("SELECT id FROM hospital WHERE external_id = :external_id",
                                [':external_id' => $external_id]))
        {
            return $rows[0]['id'];
        }

        return FALSE;
    }
}

?>