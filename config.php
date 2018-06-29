 <?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "DATA_RET";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
