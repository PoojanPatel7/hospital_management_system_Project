<?php
require_once 'db.php';
$conn->query('ALTER TABLE patients ADD COLUMN gender VARCHAR(20), ADD COLUMN blood_group VARCHAR(10), ADD COLUMN age INT');
echo 'Done';
?>
