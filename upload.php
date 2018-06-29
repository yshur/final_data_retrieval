<?php
    include 'config.php';

	
    if(isset($_POST["upload"]))
    {
        $target_dir = "docs/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;

        // Check if file already exists
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }
        // Check file size
        if ($_FILES["fileToUpload"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
        }
        else {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
            }
            else {
                echo "Sorry, there was an error uploading your file.";
            }

            if ($uploadOk != 0) {
				$doc_id = addDocToDB($conn,$target_file,$target_file);
                readAndParse($conn,$target_file,$doc_id);
            }
        }
    }

	function addDocToDB($conn,$url,$title){
		$id = random_int(100,999);
        $query = "insert into Docs(id,url,title) values($id,\"$url\",\"$title\");";
        $result = mysqli_query($conn,$query);
		return $id;
    }
	
	function readAndParse($conn,$filePath,$doc_id){
        $filecontents = file($filePath);
		$word_map = array();
		
		foreach($filecontents as $line) {
			$words = preg_split('/[ \s\n\r\t{}():;,.=\'\"<>*&]+/', $line);	
			foreach($words as $word)
			{	
				$word = strtolower($word);
				echo "$word\n";
				if(array_key_exists(strval($word), $word_map))
					$word_map[strval($word)]++;
				else
					$word_map[strval($word)]=1;
			}
		}
        
		foreach($word_map as $key=>$value)
		{
			echo "$key=$value\n";
			mysqli_query($conn,"insert into Words(text,id,count) values(\"$key\",$doc_id, $value);");
		}
    }

    function getDocIdOf($conn,$filePath){
        $query = "select id from Docs where url=\"".$filePath."\";";
        $result = mysqli_query($conn,$query);

        if ($result->num_rows > 0)
            while($row = $result->fetch_assoc())
                return $row["id"];
    }

    function getAllFiles($conn){
        $query = "select id from Docs;";
        $result = mysqli_query($conn,$query);
        $fileList=array();

        if ($result->num_rows > 0)
            while($row = $result->fetch_assoc())
                array_push($fileList,$row["id"]);

        return $fileList;
    }

    function getAllDocID($conn,$word){
        $fileList=array();
        $query="select id from Words where text=".$word." group by id;";
        $result=mysqli_query($conn,$query);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                array_push($fileList,$row["id"]);
            }
        }
        return $fileList;
    }

    function getAllDocIDNot($conn,$word){
        $fileList=array();
        $query="select id from Words where id not in(select id from Words where text=".$word.") group by id;";
        $result=mysqli_query($conn,$query);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                array_push($fileList,$row["id"]);
            }
        }
        return $fileList;
    }

    function notFile($conn,$list) {
        $fileList=array();
        $query="select id from Docs;";
        $result=mysqli_query($conn,$query);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $count=0;
                foreach($list as $v)
                    if($v==$row["id"])   
						$count++;
                if($count==0)
                    array_push($fileList,$row["id"]);
            }
        }
        return $fileList;
    }
    
	function andOp($list1,$list2) {
        $fileList=array();
        foreach($list1 as $a)
        {
            foreach($list2 as $b)
            {
                if($a==$b)
                    array_push($fileList,$a);
            }
        }
        return $fileList;
    }

    function orOp($list1,$list2) {
        $fileList=array();
        foreach($list1 as $b)
        {
            array_push($fileList,$b);
        }
        foreach($list2 as &$b)
        {
            array_push($fileList,$b);
        }
        return $fileList;
    }

    function searchLine($conn,$line){
        if($line=="")
            return getAllFiles($conn);

        $line = preg_split('/[\s]+/', $line, -1, PREG_SPLIT_NO_EMPTY);
        $fileList=array();
        for($x=0;$x<count($line);$x++)
        {
            if ($line[$x] == "OR")
            {
                $x++;
                if($line[$x]=="NOT")
                {
                    $x++;
                    if(strpos($line[$x],'(')!==false)
                    {
                        $newLine="";
                        while(strpos($line[$x],')')===false)
                            $newLine .= $line[$x++]." ";
                        $newLine .=$line[$x++];
                        $newLine = substr($newLine,1,strpos($newLine,')')-1);
                        $fileList=orOp($fileList,notFile($conn,searchLine($conn,$newLine)));
                    }
                    else
                    {
                        $fileList=orOp($fileList,getAllDocNoNot($conn,$line[$x]));
                    }
                }
                else
                {
                    if(strpos($line[$x],'(')!==false)
                    {
                        $newLine="";
                        while(strpos($line[$x],')')===false)
                            $newLine .= $line[$x++]." ";

                        $newLine .=$line[$x++];
                        $newLine = substr($newLine,1,strpos($newLine,')')-1);
                        $ff=searchLine($conn,$newLine);
                        $fileList=orOp($fileList,searchLine($conn,$newLine));
                    }
                    else
                    {
                        $fileList=orOp($fileList,getAllDocID($conn,$line[$x]));
                    }
                }
            }
            else if($line[$x] == "AND")
            {
                $x++;
                if($line[$x]=="NOT")
                {
                    $x++;
                    if(strpos($line[$x],'(')!==false)
                    {
                        $newLine="";
                        while(strpos($line[$x],')')===false)
                            $newLine .= $line[$x++]." ";
                        $newLine .=$line[$x++];
                        $newLine = substr($newLine,1,strpos($newLine,')')-1);
                        $fileList=andOp($fileList,notFile($conn,searchLine($conn,$newLine)));
                    }
                    else
                    {
                        $fileList=andOp($fileList,getAllDocIDNot($conn,$line[$x]));
                    }
                }
                else
                {
                    if(strpos($line[$x],'(')!==false)
                    {
                        $newLine="";
                        while(strpos($line[$x],')')===false)
                            $newLine .= $line[$x++]." ";

                        $newLine .=$line[$x++];
                        $newLine = substr($newLine,1,strpos($newLine,')')-1);
                        $fileList=andOp($fileList,searchLine($conn,$newLine));
                    }
                    else
                    {
                        $fileList=andOp($fileList,getAllDocID($conn,$line[$x]));
                    }
                }
            }
            else if($line[$x] == "NOT")
            {
                $x++;
                if(strpos($line[$x],'(')!==false)
                {
                    $newLine="";
                    while(strpos($line[$x],')')===false)
                        $newLine .= $line[$x++]." ";

                    $newLine .=$line[$x++];
                    $newLine = substr($newLine,1,strpos($newLine,')')-1);
                    $fileList=array_merge($fileList,notFile($conn,searchLine($conn,$newLine)));
                }

                $fileList=array_merge($fileList,getAllDocIDNot($conn,$line[$x]));
            }
            else
            {
               $fileList=array_merge($fileList,getAllDocID($conn,$line[$x]));
            }
        }
        return $fileList;
    }


?>