<?php
require 'application/db/db.php';

$sql = "SELECT id
             , name
          FROM user
         WHERE id = 1";

print_r($db->select($sql));
print "\n";
?>