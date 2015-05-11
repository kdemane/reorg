<?php
require_once 'application/classes/base.php';
require_once 'application/classes/location.php';

class manufacturer_or_GPO extends base
{
    private $external_id
          , $name
          , $location_id
          , $submitting_name;

    function extract(&$payment)
    {
        $this->_reset();

        if ($num_extracted =
            $this->_extract_raw_from_payment($payment,
                                             ['applicable_manufacturer_or_applicable_gpo_making_payment_name'    => TRUE,
                                              'applicable_manufacturer_or_applicable_gpo_making_payment_id'      => TRUE,
                                              'applicable_manufacturer_or_applicable_gpo_making_payment_state'   => TRUE,
                                              'applicable_manufacturer_or_applicable_gpo_making_payment_country' => TRUE]))
        {
            $this->_import();
            return $num_extracted;
        }

        return FALSE;
    }

    private function _import()
    {
        $this->external_id = $this->raw['applicable_manufacturer_or_applicable_gpo_making_payment_id'];
        $this->name        = $this->raw['applicable_manufacturer_or_applicable_gpo_making_payment_name'];

        $this->location_id = location::fetch($this->db,
            address::create(['state'   => $this->raw['applicable_manufacturer_or_applicable_gpo_making_payment_state'],
                             'country' => $this->raw['applicable_manufacturer_or_applicable_gpo_making_payment_country']]));
    }

    private function _reset()
    {
        $this->raw = [];
        unset($this->external_id);
        unset($this->name);
        unset($this->location_id);
        unset($this->submitting_name);
    }

    function save()
    {
        if ($this->_validate())
        {
            $sql = "INSERT INTO manufacturer_or_GPO
                    (
                        external_id,
                        name,
                        location_id
                    )
                    VALUES
                    (
                        :external_id,
                        :name,
                        :location_id
                    )
                    ON DUPLICATE KEY
                    UPDATE
                        external_id = IFNULL(external_id, VALUES(external_id)),
                        location_id = IFNULL(location_id, VALUES(location_id))";

            if (!$this->db->exec($sql, [':external_id' => $this->external_id,
                                        'name' => $this->name,
                                        'location_id' => $this->location_id]))
            {
                die("Insert blew up");
            }
        }
    }

    private function _validate()
    {
        return !empty($this->name);
    }

    public static function get(&$db, $name)
    {
        if ($rows = $db->select("SELECT id FROM manufacturer_or_GPO WHERE name = :name",
                                [':name' => $name]))
        {
            return $rows[0]['id'];
        }

        return NULL;
    }
}

?>