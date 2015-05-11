<?php
require_once 'application/classes/base.php';

class drug_or_biological extends base
{
    private $names
          , $NDCs;

    function extract(&$payment)
    {
        $this->_reset();

        if ($num_extracted = $this->_extract_raw_from_payment($payment,
                                                              ['name_of_associated_covered_drug_or_biological' => 5,
                                                               'ndc_of_associated_covered_drug_or_biological'  => 5]))
        {
            $this->_import();
            return $num_extracted;
        }

        return FALSE;
    }

    private function _import()
    {
        $this->names = $this->raw['name_of_associated_covered_drug_or_biological'];
        $this->NDCs = $this->raw['ndc_of_associated_covered_drug_or_biological'];
    }

    private function _reset()
    {
        $this->raw = [];
        unset($this->names);
        unset($this->NDCs);
    }

    function save()
    {
        if ($this->_validate())
        {
            $select_sql = "SELECT id
                             FROM drug_or_biological
                            WHERE ";

            $insert_sql = "INSERT IGNORE INTO drug_or_biological
                           (
                               NDC,
                               name
                           )
                           VALUES
                           (
                               :NDC,
                               :name
                           )";

            for ($i = 0; $i < 5; $i++)
            {
                if (!empty($this->names[$i]) && !empty($this->NDCs[$i]))
                {
                    if (!$rows = $this->db->select($select_sql . "name = :name AND NDC = :NDC",
                                                   [':name' => $this->names[$i],
                                                    ':NDC'  => $this->NDCs[$i]]))
                    {
                        if (!$this->db->exec($insert_sql, [':NDC' => $this->NDCs[$i],
                                                           ':name' => $this->names[$i]]))
                        {
                            die("Insert blew up");
                        }
                    }
                }
                else if (!empty($this->names[$i]))
                {
                    if (!$rows = $this->db->select($select_sql . " name = :name", [':name' => $this->names[$i]]))
                    {
                        if (!$this->db->exec($insert_sql, [':NDC' => NULL, ':name' => $this->names[$i]]))
                        {
                            die("Insert blew up");
                        }
                    }
                }
                else if (!empty($this->NDCs[$i]))
                {
                    if (!$rows = $this->db->select($select_sql . " NDC = :NDC", [':NDC' => $this->NDCs[$i]]))
                    {
                        if (!$this->db->exec($insert_sql, [':NDC' => $this->NDCs[$i], ':name' => NULL]))
                        {
                            die("Insert blew up");
                        }
                    }
                }
            }
        }
    }

    private function _validate()
    {
        return count($this->names) || count($NDCs);
    }
}

?>