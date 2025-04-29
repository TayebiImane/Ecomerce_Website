<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'media';
    $db_port = 4000;

    $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if(isset($_POST['submit'])){
        $file_tmp = $_FILES['file']['tmp_name'];
        $type = $_FILES['file']['type'];
        $file_name = $_FILES['file']['name'];
        $destination = 'uploads/' . $file_name;
        if($file_tmp && $file_name && $type){
            $file_path = $destination;
            if (move_uploaded_file($file_tmp,$file_path)){
                $file_content = file_get_contents($file_path);
                $file_content = $conn->real_escape_string($file_content);
                $sql = "INSERT INTO files (type, file_name, file_path, file) VALUES ('$type','$file_name', '$file_path', '$file_content')";
                if($conn->query($sql) == TRUE){
                    echo "File uploaded successfully";
                } else {
                    echo "Error inserting into database : " . $conn->error;
                }
            }else{
                echo "Error moving file to destination" .$conn->error;
            }
    }else{
        echo "Please select a file and specify its type.";
    }
}
    $conn->close();
?>