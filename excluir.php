<?php
    include 'times/function.php';

    $id = base64_decode($_POST['id'] ?? '', true);
    
        if ($id !== false && is_numeric($id)) {
            delete($id);
        }
?>