<?php
	header("Content-Type: application/json");
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	$page = 0;
	if(isset($_GET["page"]) && is_numeric($_GET["page"])) {
		$page = max(intval($_GET["page"])-1,0);
	}

	if(!isset($_GET["q"])) {
		echo "[]";
		exit;
	} else {
		if($_GET["q"]=="") {
			echo "[]";
			exit;
		}
	}
	
	$term = trim($_GET["q"]);
	
	if(preg_match("/(author:\s*(\w+))/",$term,$match)) {
		$map_name = trim(str_replace($match[1],"",$term));
		$author = trim($match[2]);
	}
	
	$db = new SQLite3("/home/maps.db");
	if(!isset($author) || $author=="") {
		$stmt = $db->prepare("select * from data where name like :a order by name,version;");
		$stmt->bindValue(':a','%'.$term.'%',SQLITE3_TEXT);
		$results = $stmt->execute();
	} else {
		$stmt = $db->prepare("select * from data where name like :a and author like :b order by name,author,version;");
		$stmt->bindValue(':a','%'.$map_name.'%',SQLITE3_TEXT);
		$stmt->bindValue(':b','%'.$author.'%',SQLITE3_TEXT);
		$results = $stmt->execute();
	}
	
	if(isset($_GET["mapsofweek"])) {
		$results = $db->query("select * from data order by random() limit 6;");
	}
	
	if(isset($_GET["lastadded"])) {
		$results = $db->query("select * from data order by id desc limit 12;");
	}
	
	//$results->finalize();
	
	$cnt = 0;
	while(($data = $results->fetchArray(SQLITE3_ASSOC))) {
		if($cnt>=$page*24 && $cnt<($page+1)*24) {
			unset($data["desc"]);
			unset($data["misc"]);
			unset($data["size"]);
			unset($data["filename"]);
			unset($data["textfile"]);
			unset($data["MD5"]);
			unset($data["isometric"]);
			unset($data["topdown"]);
			unset($data["uploaded"]);
			$ret["entries"][] = $data;
		}
		$cnt++;
	}
	
	$ret["total"] = $cnt;
	
	if(isset($ret)) {
		echo json_encode($ret);
	} else {
		return "[]";
	}
	
	$db->close();
?>