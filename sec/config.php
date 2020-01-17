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

// Config of the app
$toTrustUsers_nbTest = ; 			// (Integer)	number of tests to run to test the reliability of an user
$toTrustUsers_goodAnswersRatio = ;	// (Float)		ratio of good answers in the test phase to trust an user
$toTrustData_nbAnswers = ;			// (Integer)	number of answer to trust an association in the database
$toTrustData_ratio = ;				// (Float)		ratio to validate or deny an association in the database
$main_lg = '';						// (String)		Code of the main language of the app
$translation_lg = '';				// (String)		Code of the translation's language

?>