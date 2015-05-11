<?php
require_once 'application/classes/base.php';
require_once 'application/classes/location.php';

class recipient extends base
{
    private $location_id;

    function extract(&$payment)
    {
        $this->_reset();

        if ($num_extracted =
            $this->_extract_raw_from_payment($payment,
                                             ['recipient_city'        => TRUE,
                                              'recipient_state'       => TRUE,
                                              'recipient_zip_code'    => TRUE,
                                              'recipient_country'     => TRUE,
                                              'recipient_province'    => TRUE,
                                              'recipient_postal_code' => TRUE]))
        {
            $this->_import();
            return $num_extracted;
        }

        return FALSE;
    }

    private function _import()
    {
        $this->location_id = location::fetch($this->db,
                                             address::create(['city'     => $this->raw['recipient_city'],
                                                              'province' => $this->raw['recipient_province'],
                                                              'state'    => $this->raw['recipient_state'],
                                                              'zip'      => $this->raw['recipient_zip_code'],
                                                              'postal'   => $this->raw['recipient_postal_code'],
                                                              'country'  => $this->raw['recipient_country']]));


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

    public static function fetch_type_id(&$db, $description)
    {
        if ($rows = $db->select("SELECT id FROM recipient_type WHERE description = :description",
                                [':description' => $description]))
        {
            return $rows[0]['id'];
        }

        $sql = "INSERT INTO recipient_type
                (
                    description
                )
                VALUES
                (
                    :description
                )";

        if (!$db->exec($sql, [':description' => $description]))
        {
            die("Insert blew up");
        }

        return $db->last_insert_id();
    }
}

?>
