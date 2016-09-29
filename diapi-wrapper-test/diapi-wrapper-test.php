<?php
require 'bc-diapi.php';
// echo json_encode($_POST);
// echo 'foo';

$BCDI = new BCDIAPI($_POST['account_id'], $_POST['client_id'], $_POST['client_secret']);
echo BCDIAPI['account_id'];
echo BCDI['account_id]
?>
