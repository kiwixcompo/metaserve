<?php
require 'config/database.php';
require 'src/Models/Course.php';
$courseModel = new Course();
$programmes = $courseModel->getProgrammes();
$prog1_id = 1; $prog2_id = 2;
foreach($programmes as $p) {
    if (stripos($p['name'], 'Mandatory') !== false || stripos($p['name'], 'Digital Literacy') !== false) {
        $prog1_id = $p['id'];
    } else if (stripos($p['name'], 'Professional') !== false) {
        $prog2_id = $p['id'];
    }
}
var_dump($prog1_id);
var_dump($prog2_id);
