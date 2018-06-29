<?php
    include 'config.php';

	
    if(isset($_POST["delete"]))
    {
		$id=$_POST["fileToDelete"];
		echo "id=$id\n";
       $sql="delete from Docs where id=$id;";
		$result = $conn->query($sql);
		
		if ($result->num_rows > 0) {
			echo "the file was deleted from Docs\n";
		
		} else {
			echo "0 results";
		}
	   $sql="delete from Words where id='$id';";
		$result = $conn->query($sql);
		
		if ($result->num_rows > 0) {
			echo "the file was deleted from Words\n";
		
		} else {
			echo "0 results";
		}

    }


?>