<?php
//database connection details

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "fileuploaddownload";

 $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);


 if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

 //Fetch the uploaded files from the database

 $sql = "SELECT *FROM uploads";
 $result = $conn->query($sql);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Uploaded files</title>
	<link rel="stylesheet" href="style.css">
    <style>
        .file-table {
            color: white; 
            margin: 50px; 
            border-collapse: collapse;
            justify-content:center;
        }
        .file-table th, .file-table td {
            border: 1px solid white; /* Ensure table has borders */
            padding: 38px;
            text-align: center;
            color:black;
            font-size:19px;
        }
        .file-table th {
            background-color: #39b3e6;
            font-size:20px;
            color:black;
        }
    </style>
     <script>
        function returnBack() {
            window.location.href = "home.html";
        }
    </script>
</head>
<body>
<div class="main-content">
        <div class="nav">
            <img id="logo" src="nitte.png">
            <p id="head">Academic Integrity Portal</p>
            <button class="logout-button" type="button" onclick="returnBack()">Return Back</button>
        </div>
        <div class="container mt-5">
        <h2>Uploaded Files</h2>
        <table class="table table-bordered table-striped file-table">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>File Size</th>
                    <th>File Type</th>
                    <th>Upload Time</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display the uploaded files and download links
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $file_path = "uploads/" . $row['filename'];
                        ?>
                        <tr>
                            <td><?php echo $row['filename']; ?></td>
                            <td><?php echo $row['filesize']; ?> bytes</td>
                            <td><?php echo $row['filetype']; ?></td>
                            <td><?php echo $row['upload_time']; ?></td>
                            <td><a href="<?php echo $file_path; ?>" class="btn btn-primary" download>Download</a></td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="4">No files uploaded yet.</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    <footer>
            <p>Copyright &copy; 2024</p>
            NMAM Institute Of Technology, Nitte
        </footer>
    </div>
</body>
</html>
<?php
$conn->close();
?>
