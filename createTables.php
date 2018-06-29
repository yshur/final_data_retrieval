 <?php
include('config.php');

$sql = "CREATE TABLE Docs (
id int(4) not null,
url varchar(50) not null,
title varchar(50) not null
)";

if ($conn->query($sql) === TRUE) {
    echo "Table Docs created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$sql = "CREATE TABLE Words (
text varchar (50),
id int(4),
count varchar(10)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table Words created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

?> 