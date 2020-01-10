<?php

// Parameters to instanciate the class Db_manager
$db_host = '';						// (String)	host of the database
$db_dbname = '';					// (String)	name of the database
$db_username = '';					// (String)	username of the account to connect to the database
$db_password = '';					// (String)	password of the account to connect to the database

// Parameters to instanciate the class Api_manager
$api_url = '';						// (String)	url of the api
$api_username = '';					// (String)	username of the account to connect to the api
$api_password = '';					// (String)	password of the account to connect to the api

// Parameters to connect to the translation database
$dbtrad_host = '';					// (String)	host of the database
$dbtrad_dbname = '';				// (String)	name of the database
$dbtrad_username = '';				// (String)	username of the account to connect to the database
$dbtrad_password = '';				// (String)	password of the account to connect to the database

// Parameters to connect to the frequency database
$dbfreq_host = '';					// (String)	host of the database
$dbfreq_dbname = '';				// (String)	name of the database
$dbfreq_username = '';				// (String)	username of the account to connect to the database
$dbfreq_password = '';				// (String)	password of the account to connect to the database

// Parameters of the files used
$inputscsv_path = '';				// (String)	path to the inputs.csv file
$inputscsv_delimiter = '';			// (String)	delimiter of the inputs.csv file
$catcsv_path = '';					// (String)	path to the catGenToCatWiki.csv file
$cat_csv_delimiter = '';			// (String)	delimiter of the catGenToCatWiki.csv file
$dataxml_path = '';					// (String)	path to the xml file

// Parameter for the script create_db.php
$useTraduction = ; 					// (Bool)	if the script needs to use translations

// Config of the app
$toTrustUsers_nbTest = ; 			// (Integer)	number of test to run for testing the reliability of an user
$toTrustUsers_goodAnswersRatio = ;	// (Float)		ratio of good answers in the test phase to trust an user
$toTrustData_nbAnswers = 5;			// (Integer)	number of answer to trust an association in the database
$toTrustData_ratio = 0.9;			// (Float)		ratio to validate or deny an association in the database

?>