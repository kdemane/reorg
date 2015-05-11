<?php
class location extends base
{
    public static function fetch(&$db, array $address)
    {
        $address['state_id'] = state::fetch($db, $address['state']);
        $address['country_id'] = country::fetch($db, $address['country']);
        unset($address['state']);
        unset($address['country']);

        $select_binds = [];

        $select_sql = "SELECT id
                         FROM location
                        " . location::_where($address, $select_binds);

        if ($row = $db->select($select_sql, $select_binds))
        {
            return $row[0]['id'];
        }
        else
        {
            $insert_sql = "INSERT INTO location
                           (
                                address_1,
                                address_2,
                                city,
                                province,
                                state_id,
                                postal,
                                zip,
                                country_id
                            )
                            VALUES
                            (
                                :address_1,
                                :address_2,
                                :city,
                                :province,
                                :state_id,
                                :postal,
                                :zip,
                                :country_id
                            )";

            $insert_binds = [];
            foreach ($address as $k => $v)
                $insert_binds[':' . $k] = $v;

            if ($db->exec($insert_sql, $insert_binds))
            {
                return $db->last_insert_id();
            }

            return NULL;
        }

    }

    private static function _where($address, &$select_binds)
        {
            $sql = "";

            foreach ($address as $k => $v)
            {
                if ($v === NULL)
                {
                    $w = " IS NULL";
                }
                else
                {
                    $w = " = :" . $k;
                    $select_binds[':' . $k] = $v;
                }
                $sql .= (($sql == "" ? "WHERE " : "  AND ") . $k . $w . "
                        ");
            }

            return $sql;
        }
}

class state
{
    public static function fetch(&$db, $code, $full = NULL)
    {
        $sql = "INSERT INTO state
                (
                    code,
                    full
                )
                VALUES
                (
                    :code,
                    :full
                )
                ON DUPLICATE KEY
                UPDATE
                    full = IFNULL(full, VALUES(full))";

        if ($db->exec($sql, [':code' => $code,
                             ':full' => $full]))
        {
            $rows = $db->select("SELECT id FROM state WHERE code = :code",
                                [':code' => $code]);

            return $rows[0]['id'];
        }

        return NULL;
    }
}

class country
{
    public static function fetch(&$db, $full, $code = NULL)
    {
        $sql = "INSERT INTO country
                (
                    code,
                    full
                )
                VALUES
                (
                    :code,
                    :full
                )
                ON DUPLICATE KEY
                UPDATE
                    code = IFNULL(code, VALUES(code))";

        if ($db->exec($sql, [':code' => $code,
                             ':full' => $full]))
        {
            $rows = $db->select("SELECT id FROM country WHERE full = :full",
                                [':full' => $full]);

            return $rows[0]['id'];
        }

        return NULL;
    }
}

class address
{
    public static function create($data)
    {
        $out = [];
        $fields = ['address_1',
                   'address_2',
                   'city',
                   'province',
                   'state',
                   'zip',
                   'postal',
                   'country'];

        foreach ($fields as $f)
            $out[$f] = !empty($data[$f]) ? $data[$f] : NULL;

        return $out;
    }
}
?>