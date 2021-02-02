#!/usr/bin/php

<?php

	$host = "localhost";
	$user = NULL;
	$password = NULL;
	$file = "users.csv";
	$db = NULL;
	
	function help(){
		echo "php user_upload.php -u [username] -p [password] -h [host]
			\n\n\t--file [csv file name] - this is the name of the csv to be parsed (Default = users.csv)
			\n\t--create_table - this will cause the PostgreSQL users table to be built (and no further action will be taken)
			\n\t--dry_run - runs the script without altering the database
			\n\t-u [username] - PostgreSQL username
			\n\t-p [password] - PostgreSQL password
			\n\t-h [host] - PostgreSQL host (Default = localhost)
			\n\t--help - Displays these details (Must be only argument)
			\n\n**Order of arguments is not important!
			\n";
	}
	
	//connect to database
	function connect(){
		global $host, $user, $password, $db;
		$db = @pg_connect("host=$host user=$user password=$password");
		if($db){
			echo "Connected to Database!\n\n";
		}
		else{
			echo "Connection to database failed with parameters $host|$user|$password\n\n";
		}
	}

?>