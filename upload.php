<?php

$inipath = php_ini_loaded_file();

define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5000000);
define('ALLOWED_EXTENSIONS', ['png', 'jpg', 'jpeg', 'webp', 'svg']);

define('ITEMS_PER_PAGE', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!isset($_FILES['file'])) {
        header('Content-Type: application/json');
        echo json_encode(["error"=>"No file was uploaded."]);
        return;
    }
    
    $file = $_FILES['file'];
    $fileName = basename($file['name']);
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $targetFile = UPLOAD_DIR . $fileName;
    
    
    if(!in_array($fileExtension, ALLOWED_EXTENSIONS)) {
        header('Content-Type: application/json');
        echo json_encode(["error"=>"Invalid file type. Only " . implode(", ", ALLOWED_EXTENSIONS) . " are allowed."]);
        return;
    }
    
    if ($fileSize > MAX_FILE_SIZE) {
        header('Content-Type: application/json');
        echo json_encode(["error"=>"File is too large. Maximum size is " . (MAX_FILE_SIZE / 1000000) . "MB."]);
        return;
    }
    
    if ($fileTmpName && getimagesize($fileTmpName) === false) {
        header('Content-Type: application/json');
        echo json_encode(["error"=>"Uploaded file is not a valid image."]);
        return;
    }
    
    if (file_exists($targetFile)) {
        header('Content-Type: application/json');
        echo json_encode(["error"=>"A file with the same name already exists."]);
        return;
    }
    
    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) {
        header('Content-Type: application/json');
        echo json_encode(["error"=>"Failed to create upload directory."]);
        return;
    }
    
    if (move_uploaded_file($fileTmpName, $targetFile)) {
        header('Content-Type: application/json');
        echo json_encode(["success"=>htmlspecialchars($targetFile)]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(["error"=>"An error occurred during the file upload."]);
    }
}

else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $files = scandir(UPLOAD_DIR);

    foreach ($files as $key => $file) {
        $filePath = UPLOAD_DIR . $file;
        if (!is_file($filePath)) {
            unset($files[$key]);
        }
    }
    header('Content-Type: application/json');
    echo json_encode($files);
}



else {
    header('Content-Type: application/json');
    echo json_encode(["error"=>"Invalid request method."]);
}

?>