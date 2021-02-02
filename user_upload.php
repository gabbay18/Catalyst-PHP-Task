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
	
	//function for creating table, for normal running and --create_table
	function table($rebuild){
		global $db;
		
		if($rebuild){
			pg_query($db, "DROP TABLE IF EXISTS USERS");
		}
		
		$table =
		"CREATE TABLE IF NOT EXISTS USERS	
			(NAME		TEXT    NOT NULL,
			SURNAME		TEXT	NOT NULL,
			EMAIL	TEXT	PRIMARY KEY	NOT NULL)";

		$test = pg_query($db, $table);
		if(!$test){
			echo pg_last_error($db);
		}
		else{
			echo "Table Users Successful!\n";
		}	
		pg_close($db);
	}
	
	function insert($fname, $lname, $email){
		global $db;
		$query =
			"INSERT INTO USERS (NAME, SURNAME, EMAIL)
			VALUES (E'$fname', E'$lname', E'$email')";

		if (@pg_query($db, $query) == False){
			return False;
		}
		else{
			return True;
		}
	}
	
	//reads file and can call insert to database depeding on $dry run
	function database ($dry){
		global $file;
		echo "Opening File: $file...\n";
		$row = 1;
		$accepted = -1;
		$rejected = 0;
		$read = @fopen($file, "r");
		if ($read != FALSE){
			echo "Reading File: $file\n\n";
			while (($data = fgetcsv($read, 1000, ",")) != FALSE){
				$fields = sizeof($data);
				if($fields == 3){
					$email = strtolower(trim($data[2]));
					if (($row != 1) && !filter_var($email, FILTER_VALIDATE_EMAIL)) { //email validation
						$rejected++;
					}
					else{						
						$fname = ucfirst(strtolower(trim($data[0])));
						$lname = ucfirst(strtolower(trim($data[1])));
						if($row == 1){
							$email = ucfirst($email);
						}
						else{
							//Temp Variables
							$fnameT = $fname;
							$lnameT = $lname;
							$emailT = $email;
							$index = 0;
							
							for($x = 0; $x < strlen($fname); $x++){
								if($fname[$x] == "'"){
									$fname[$x+1] = strtoupper($fname[$x+1]);
									$fnameT[$x+1+$index] = $fname[$x+1];
									$fnameT = substr($fnameT,0,$x+$index) . "\\" . substr($fnameT,$x+$index);
									$index++;
								}
							}
							$index = 0;
							for($x = 0; $x < strlen($lname); $x++){
								if($lname[$x] == "'"){
									$lname[$x+1] = strtoupper($lname[$x+1]);
									$lnameT[$x+1+$index] = $lname[$x+1];
									$lnameT = substr($lnameT,0,$x+$index) . "\\" . substr($lnameT,$x+$index);
									$index++;
								}
							}
							$index = 0;
							for($x = 0; $x < strlen($email); $x++){
								if($email[$x] == "'"){
									$emailT = substr($emailT,0,$x+$index) . "\\" . substr($emailT,$x+$index);
									$index++;
								}
							}
						}
						
						if($row == 1){
							echo "\t\t$fname, $lname, $email, ";
							echo "Inserted to Database\n\n";
						}
						else{
							if(!$dry){
								if(!insert($fnameT, $lnameT, $emailT)){
									$accepted--;
									$rejected++;
								}
								else{
									echo "\t\t$fname, $lname, $email, Yes\n";
								}
							}
							else{
								echo "\t\t$fname, $lname, $email, No\n";
							}
						}
						$accepted++;
					}
				}
				else{
					$rejected++;
				}
				$row++;
			}
			echo "\n$accepted accepted entries, $rejected rejected entries\n";
			fclose($read);
		}
		else{
			echo "Could not open file: $file\n";
		}
	}

?>