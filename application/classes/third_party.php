<?php
require_once 'application/classes/base.php';

class third_party extends base
{
    private $name
          , $recipient_policy_id;

    function extract(&$payment)
    {
        $this->_reset();

        if ($num_extracted =
            $this->_extract_raw_from_payment($payment,
                                             ['third_party_payment_recipient_indicator'                           => TRUE,
                                              'name_of_third_party_entity_receiving_payment_or_transfer_of_value' => TRUE]))
        {
            $this->_import();
            return $num_extracted;
        }

        return FALSE;
    }

    private function _import()
    {
        $this->name = $this->raw['name_of_third_party_entity_receiving_payment_or_transfer_of_value'];

        if (!empty($this->raw['third_party_payment_recipient_indicator']))
        {
            $this->recipient_policy_id =
                $this->_fetch_recipient_policy_id($this->raw['third_party_payment_recipient_indicator']);
        }
    }

    private function _fetch_recipient_policy_id($description)
    {
        if ($rows = $this->db->select("SELECT id
                                         FROM third_party_recipient_policy
                                        WHERE description = :description",
                                      [':description' => $description]))
        {
            return $rows[0]['id'];
        }

        $sql = "INSERT INTO third_party_recipient_policy
                (
                    description
                )
                VALUES
                (
                    :description
                )";

        if ($this->db->exec($sql, [':description' => $description]))
            return $this->db->last_insert_id();

        return NULL;
    }

    private function _reset()
    {
        $this->raw = [];
        unset($this->name);
        unset($this->equals_covered_recipient);
    }

    function save()
    {
        if ($this->_validate())
        {
            $sql = "SELECT id
                      FROM third_party
                     WHERE name " . (empty($this->name) ? "IS NULL" : "= :name") . "
                       AND recipient_policy_id = :recipient_policy_id";

            $binds = [':recipient_policy_id' => $this->recipient_policy_id];
            if (!empty($this->name))
                $binds[':name'] = $this->name;

            if (!$rows = $this->db->select($sql, $binds))
            {
                $sql = "INSERT INTO third_party
                    (
                        name,
                        recipient_policy_id
                    )
                    VALUES
                    (
                        :name,
                        :recipient_policy_id
                    )";

                if (!$this->db->exec($sql, [':name'                => $this->name,
                                            ':recipient_policy_id' => $this->recipient_policy_id]))
                {
                    die("Insert blew up");
                }
            }
        }
    }

    private function _validate()
    {
        return !empty($this->recipient_policy_id);
    }

    public static function get(&$db, $name, $description)
    {
        $sql = "SELECT t.id
                  FROM third_party t
                  JOIN third_party_recipient_policy p
                    ON t.recipient_policy_id = p.id
                 WHERE t.name " . (empty($name) ? " IS NULL" : " = :name") . "
                   AND p.description = :description";

        $binds = [':description' => $description];

        if (!empty($name))
            $binds[':name'] = $name;

        /* print $sql . "\n"; */
        /* print_r($binds); */
        /* print "\n\n"; */

        if ($rows = $db->select($sql, $binds))
        {
            return $rows[0]['id'];
        }

        return FALSE;
    }
}

?>