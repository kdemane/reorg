<?php
require_once 'application/classes/base.php';

class medical_supply extends base
{
    private $names;

    function extract(&$payment)
    {
        $this->_reset();

        if ($num_extracted = $this->_extract_raw_from_payment($payment,
                                                              ['name_of_associated_covered_device_or_medical_supply' => 5]))
        {
            $this->_import();
            return $num_extracted;
        }

        return FALSE;
    }

    private function _import()
    {
        foreach ($this->raw['name_of_associated_covered_device_or_medical_supply'] as $ms)
            $this->names[$ms] = TRUE;
    }

    private function _reset()
    {
        $this->raw = [];
        unset($this->names);
    }

    function save()
    {
        if ($this->_validate())
        {
            $sql = "INSERT IGNORE INTO medical_supply
                    (
                        name
                    )
                    VALUES
                    (
                        :name
                    )";

            foreach ($this->descriptions as $d => $crap)
            {
                if (!$this->db->exec($sql, [':name' => $d]))
                {
                    die("Insert blew up");
                }
            }
        }
    }

    private function _validate()
    {
        return count($this->descriptions);
    }
}

?>