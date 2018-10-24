<?php
session_start();
define('EMP_MANGEMENT_LIST_URL', 'http://localhost/curlphp/emplist.php');
define('EMP_MANGEMENT_ADD_URL', 'http://localhost/curlphp/add_employee.php');
define('EMP_MANGEMENT_EDIT_URL', 'http://localhost/curlphp/edit_employee.php');

define('IMMAGE_UPLODE_PARTH','C:/xampp/htdocs/curlphp/emp_profile_img/');
define('IMMAGE_DISPLAY_PARTH','http://localhost/curlphp/emp_profile_img/');	
Class dbObj{
	/* Database connection start */
	private $servername = 'localhost';
	private $dbname = 'invoicenew';
	private $username = 'root';
	private $password = '';

	
	function getConnstring() {
		try {
				$conn = new PDO('mysql:host=' .$this->servername.';dbname='.$this->dbname, $this->username, $this->password);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				return $conn;
			} catch (\Exception $e) {
				echo "Database Error: " . $e->getMessage();
			}
	}
}
 
?>