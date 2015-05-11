<?php
require_once 'application/classes/base.php';
require_once 'application/classes/location.php';

class physician extends base
{
    private $profile_id
          , $first_name
          , $middle_name
          , $last_name
          , $license_states
          , $primary_type_id
          , $specialty
          , $is_owner;

    function extract(&$payment)
    {
        $this->_reset();

        if ($num_extracted =
            $this->_extract_raw_from_payment($payment,
                                             ['physician_profile_id'          => TRUE,
                                              'physician_first_name'          => TRUE,
                                              'physician_middle_name'         => TRUE,
                                              'physician_last_name'           => TRUE,
                                              'physician_name_suffix'         => TRUE,
                                              'physician_primary_type'        => TRUE,
                                              'physician_specialty'           => TRUE,
                                              'physician_license_state_code'  => 5,
                                              'physician_ownership_indicator' => TRUE]))
        {
            $this->_import();
            return $num_extracted;
        }

        return FALSE;
    }

    private function _import()
    {
        $this->profile_id      = $this->raw['physician_profile_id'];
        $this->first_name      = $this->raw['physician_first_name'];
        $this->middle_name     = $this->raw['physician_middle_name'];
        $this->last_name       = $this->raw['physician_last_name'];
        $this->primary_type_id = $this->_fetch_primary_type_id($this->raw['physician_primary_type']);
        $this->specialty       = $this->raw['physician_specialty'];
        $this->license_states  = $this->raw['physician_license_state_code'];
        $this->is_owner        = ($this->raw['physician_profile_id'] == 'No' ? 0 : 1);
    }

    private function _fetch_primary_type_id($primary_type)
    {
        if ($rows = $this->db->select("SELECT id FROM physician_type WHERE description = :description",
                                      [':description' => $primary_type]))
        {
            return $rows[0]['id'];
        }

        $sql = "INSERT INTO physician_type
                (
                    description
                )
                VALUES
                (
                    :description
                )";

        if ($this->db->exec($sql, [':description' => $primary_type]))
            return $this->db->last_insert_id();

        return NULL;
    }

    private function _reset()
    {
        $this->raw = [];
        unset($this->profile_id);
        unset($this->first_name);
        unset($this->middle_name);
        unset($this->last_name);
        unset($this->primary_type_id);
        unset($this->specialty);
        unset($this->license_states);
        unset($this->is_owner);
    }

    function save()
    {
        if ($this->_validate())
        {
            $sql = "INSERT INTO physician
                    (
                        profile_id,
                        first_name,
                        middle_name,
                        last_name,
                        name_suffix,
                        primary_type_id,
                        specialty,
                        is_owner
                    )
                    VALUES
                    (
                        :profile_id,
                        :first_name,
                        :middle_name,
                        :last_name,
                        :name_suffix,
                        :primary_type_id,
                        :specialty,
                        :is_owner
                    )
                    ON DUPLICATE KEY
                    UPDATE
                        middle_name = IFNULL(middle_name, VALUES(middle_name)),
                        name_suffix = IFNULL(name_suffix, VALUES(name_suffix)),
                        specialty   = IFNULL(specialty, VALUES(specialty))";

            if (!$this->db->exec($sql, [':profile_id'      => $this->profile_id,
                                        ':first_name'      => $this->first_name,
                                        ':middle_name'     => $this->middle_name,
                                        ':last_name'       => $this->last_name,
                                        ':name_suffix'     => $this->name_suffix,
                                        ':primary_type_id' => $this->primary_type_id,
                                        ':specialty'       => $this->specialty,
                                        ':is_owner'        => $this->owner ? 0 : 1]))
            {
                die("Insert blew up");
            }
            else if ($rows = $this->db->select("SELECT id FROM physician WHERE profile_id = :profile_id",
                                               [':profile_id' => $this->profile_id]))
            {
                $physician_id = $rows[0]['id'];
            }

            foreach ($this->license_states as $state)
            {
                $sql = "INSERT IGNORE INTO physician_license
                    (
                        physician_id,
                        state_id
                    )
                    VALUES
                    (
                        :physician_id,
                        :state_id
                    )";

                if ($state_id = state::fetch($this->db, $state) && $physician_id)
                {
                    if (!$this->db->exec($sql, [':physician_id' => $physician_id,
                                                ':state_id' => $state_id]))
                    {
                        die("Insert blew up");
                    }
                }
            }
        }
    }

    private function _validate()
    {
        return !empty($this->first_name) &&
            !empty($this->last_name) &&
            !empty($this->primary_type_id);
    }

    public static function get(&$db, $profile_id)
    {
        if ($rows = $db->select("SELECT id FROM physician WHERE profile_id = :profile_id",
                                [':profile_id' => $profile_id]))
        {
            return $rows[0]['id'];
        }

        return NULL;
    }
}
?>