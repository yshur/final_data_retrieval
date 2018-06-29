<?php
    include ('config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="http://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script type="text/javascript" src="script.js"></script>
    <title>Data ret</title>
</head>
<body>
<div id="wrapper">
    <header>
        <nav>
            <ul>
                <li>
                    <a href="index.php">Search</a>
                </li>
                <li>
                    <a href="admin.php">Admin</a>
                </li>

            </ul>
        </nav>
        <h1>Data ret</h1>
    </header>
    <div class="container">
            <form action="upload.php" method="post" enctype="multipart/form-data">
                <input class="upload" type="file" name="fileToUpload" id="fileToUpload">
                <button type="submit" value="Upload" name="upload">Upload</button>
            </form>
	
			<?php
					$sql="select * from Docs;";
					$result = $conn->query($sql);

					if ($result->num_rows > 0) {
						while($row = $result->fetch_assoc()) {
							echo "<p><br><form action='delete.php' method='post'>
                <input class='delete' type='number' name='fileToDelete' value='"
				.$row["id"].
				"'><br>
				 <input type='text' name='name' value='"
				.$row["title"].
				"'>
                <button type='submit' value='Delete' name='delete'>Delete</button>
            </form></p>";						
						}
						
					
					} else {
						echo "0 results";
					}
			
			?>

    </div>
</body>
</html>