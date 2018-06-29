<?php
    include ('config.php');
	function stopList($word){
        $stopList= array("is","a","the","on","there","i","am");
        foreach($stopList as $s)
            if($s==$word)
                return false;
        return true;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="http://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="script.js"></script>
    <title>Data ret</title>
</head>
<body>
    <div id="wrapper">
        <header>
            <nav>
                <ul>
                    <li>
                        <a href="#">Search</a>
                    </li>
                    <li>
                        <a href="admin.php">Admin</a>
                    </li>

                </ul>
            </nav>
        <h1>Data ret</h1>
        </header>
        <div id="searchFile">
            <form action="index.php" method="post" enctype="multipart/form-data">
                <input class="search" type="search" placeholder="Search" aria-label="Search" name="val">
                <button type="submit" name="search">Search</button>
            </form>
                <ul>
<?php
	if(isset($_POST["search"]))
	{
		$val=$_POST["val"];
		$val = strtolower($val);
		if((stopList($val) == false) && substr($val,0,1)!='"')
			echo "the word '$val' is in the stopList";
		else {
			if(substr($val,0,1)=='"')
				$val=substr($val,1);
			if(substr($val,-1)=='"')
				$val=substr($val,0,-1);
			$vals = preg_split("/[\s,]+/", $val);
			$size = count($vals);

			$sql="select * from Words natural join Docs where text='$val';";
			$result = $conn->query($sql);

			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) 
					echo "<p>'".$row["text"]."' appears ".$row["count"]." times in <a href='".$row["url"]."' target='_blank'>".$row["title"]."</a><p>";

			
			} else {
				echo "0 results";
			}
		}
	}
?>
                    
                </ul>
            <div class="clear"></div>
        </div>
</body>
</html>
