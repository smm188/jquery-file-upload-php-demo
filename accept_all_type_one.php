<?php
require_once 'upload_one_class.php';
$upload_one_class = new upload_one_class();
$result = $upload_one_class->get_preview_src();
echo json_encode($result);
