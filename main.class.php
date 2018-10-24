<?php
include_once("connect/db_cls_connect.php");
class Main {
	protected $dbConn;
	protected $data = array();
	function __construct($connString) {

		$this->dbConn = $connString;
	}
	
	public function login() {

		if(isset($_POST['btnlogin'])) {

			$user_email = trim($_POST['useremail']);

			$user_password = trim($_POST['userpass']);

			$sql = "SELECT id, name email, password FROM users WHERE email=:email and password=:password";

			$stmt = $this->dbConn->prepare($sql);
			$stmt->bindParam(':email', $user_email);
			$stmt->bindParam(':password', $user_password);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if($stmt->rowCount()){
				$otp = rand(100000,999999);
				
				$optselequery = "SELECT id FROM opt_expiry WHERE user_id=:user_id";
				$checkoptsql = $this->dbConn->prepare($optselequery);
				$checkoptsql->bindParam(':user_id', $row['id']);
				$checkoptsql->execute();
				$checkoptrow = $checkoptsql->fetch(PDO::FETCH_ASSOC);

				if(!$checkoptrow){

					$current_id = '';
					$opt_insert_query = "INSERT INTO opt_expiry (user_id,opt_number,is_expired,create_at) VALUES ('" .$row['id'] . "','" . $otp . "', 0, NOW())";

					$optinsertstmt = $this->dbConn->prepare($opt_insert_query);
					$optinsertstmt->execute();
					$current_id =   $this->dbConn->lastInsertId();

					if($current_id){

						
						echo "SUCCESS";

					}else{

						echo "WRONG";
					}

				}else{

					$opt_update_query = "UPDATE opt_expiry SET opt_number='".$otp."',is_expired=0,create_at=NOW() where user_id='".$row['id']."'";

					$optupdatestmt = $this->dbConn->prepare($opt_update_query);
					$optupdatestmt->execute();
					
					if ($optupdatestmt->execute())
					{
						echo "SUCCESS";

					}else{
						
						echo "WRONG";						
					}	
				}
			}else{
				echo "WRONG";
			}			
		}
	}

	public function logout() {
		
		unset($_SESSION['user_session']);
		
		if(session_destroy()) {
			
			header("Location: login.php");
		}
	}

	public function checkopt(){

		$optcheckquery = "SELECT * FROM opt_expiry WHERE opt_number='" . $_POST["optnumber"] . "' AND is_expired!=1 AND NOW() <= DATE_ADD(create_at, INTERVAL 5 MINUTE)";

		$optcheckstmt = $this->dbConn->prepare($optcheckquery);
		$optcheckstmt->execute();
		if($optcheckstmt->rowCount()) {
			
			$optcheckupdatequery = "UPDATE opt_expiry SET is_expired = 1 WHERE opt_number = '" . $_POST["optnumber"] . "'";
			$optcheckupdatestmt = $this->dbConn->prepare($optcheckupdatequery);
			$optcheckupdatestmt->execute();

			$sql = "SELECT id, name email, password FROM users WHERE id=:user_id";
			$stmt = $this->dbConn->prepare($sql);
			$stmt->bindParam(':user_id', $user_email);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			$_SESSION['user_session'] = $row['name'];
			
			echo 'SUCCESS';

		}else{

			echo 'EXPIRETOKEN';
		}	
	}

	public function regenerateopt() {

		$user_email = trim($_POST['useremail']);

		$sql = "SELECT id, name email FROM users WHERE email=:email";

		$stmt = $this->dbConn->prepare($sql);
		$stmt->bindParam(':email', $user_email);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
				
		if($stmt->rowCount()){
			
			$otp = rand(100000,999999);
			
			$optselequery = "SELECT id FROM opt_expiry WHERE user_id=:user_id";
			$checkoptsql = $this->dbConn->prepare($optselequery);
			$checkoptsql->bindParam(':user_id', $row['id']);
			$checkoptsql->execute();
			$checkoptrow = $checkoptsql->fetch(PDO::FETCH_ASSOC);

			if($checkoptsql->rowCount()){

				$current_id = '';
				$opt_insert_query = "INSERT INTO opt_expiry(user_id,opt_number,is_expired,create_at) VALUES ('" .$row['id'] . "','" . $otp . "', 0,  NOW())";

				$optinsertstmt = $this->dbConn->prepare($opt_insert_query);
				$optinsertstmt->execute();
				$current_id =  $optinsertstmt->lastInsertId();

				if($current_id){

					echo "SUCCESS";
		
				}else{

					echo "WRONG";
				}

			}else{

				$opt_update_query = "UPDATE opt_expiry SET opt_number='".$otp."',is_expired=0,create_at= NOW() where user_id='".$row['id']."'";

				$optupdatestmt = $this->dbConn->prepare($opt_update_query);
				$optupdatestmt->execute();
				
				if ($optupdatestmt->execute())
				{
					echo "SUCCESS";

				}else{
					
					echo "WRONG";						
				}	
			}
		}			
	}

	public function getAllEmployeeList(){
		
		$emp_query = 'select e.id,e.emp_name,e.emp_email,e.emp_mobile_number,e.emp_profile_image,e.emp_name, (SELECT emp_name from employee where id = e.emp_reporting_manager) as reporting_manager_name, country.country_name as country,state.name as state,e. emp_city_name as city, GROUP_CONCAT(s.name) as subject_names from employee e inner join emp_subject_relation es ON e.id=es.emp_id inner join subjects s ON s.id=es.subject_id Left Join country ON e.emp_country_id=country.id Left Join state ON e.emp_state_id=state.id group by e.id order by e.created_date desc';

		$p_emp_query = $this->dbConn->prepare($emp_query);
		$p_emp_query->execute();
		$p_emp_rows = $p_emp_query->fetchAll(PDO::FETCH_ASSOC);
				
		if($p_emp_query->rowCount()){
			return $p_emp_rows;
		}
	}

	public function getCountryList(){

		$country_query = 'SELECT id,country_name FROM country';

		$p_country_query = $this->dbConn->prepare($country_query);
		$p_country_query->execute();
		$p_country_rows = $p_country_query->fetchAll(PDO::FETCH_ASSOC);
				
		if($p_country_query->rowCount()){
			return $p_country_rows;
		}	
	}

	public function getStateListByCountry(){
		$country_id = trim($_POST['country_id']);

		$state_query = 'SELECT id,name FROM state where country_id=:country_id';

		$p_state_query = $this->dbConn->prepare($state_query);
		$p_state_query->bindParam(':country_id', $country_id);
		$p_state_query->execute();
		$p_state_rows = $p_state_query->fetchAll(PDO::FETCH_ASSOC);
				
		if($p_state_query->rowCount()){
			return json_encode($p_state_rows);
		}	
	}

	public function getSubjectList(){

		$subjects_query = 'SELECT id,name FROM subjects';
		$p_subjects_query = $this->dbConn->prepare($subjects_query);
		$p_subjects_query->execute();
		$p_subjects_rows = $p_subjects_query->fetchAll(PDO::FETCH_ASSOC);
			
		if($p_subjects_query->rowCount()){
			return $p_subjects_rows;
		}
	}

	public function getAllReportingManager($parent_id){
		if($parent_id=="NULL"){
			$emp_query = 'SELECT id,emp_name FROM employee where emp_reporting_manager IS NULL';
		}else{
			$emp_query = 'SELECT id,emp_name FROM employee where emp_reporting_manager=:emp_reporting_manager';
		}
		$p_emp_query = $this->dbConn->prepare($emp_query);
		$p_emp_query->bindParam(':emp_reporting_manager', $parent_id);
		$p_emp_query->execute();
		$p_emp_rows = $p_emp_query->fetchAll(PDO::FETCH_ASSOC);
				
		if($p_emp_query->rowCount()){
			return $p_emp_rows;
		}
	}

	public function add_employee_information(){
		
		try {

			$emp_name = trim($_POST['emp_name']);
			$emp_email = trim($_POST['emp_email']);
			$emp_mobilenumber = trim($_POST['emp_mobilenumber']);
			$emp_gender = trim($_POST['emp_gender']);
			$emp_country = trim($_POST['emp_country']);
			$emp_state = trim($_POST['emp_state']);
			$emp_city_name = trim($_POST['emp_city_name']);
			$emp_reporing_manager = trim($_POST['emp_reporing_manager']);
			$emp_subject = $_POST['emp_subject'];
			$emp_image = trim($_FILES['emp_image']['name']);

			$upload_image_param = array();
			$upload_image_param['uploadpath']= IMMAGE_UPLODE_PARTH ;
			$upload_image_param['displaypath']= IMMAGE_DISPLAY_PARTH ;
			$upload_image_param['maxsize'] = 6;
			$upload_image_param['filetype'] = array('png','jpeg','gif','PNG','JPEG','GIF','jpg','JPEG');
			$upload_image_param['limit'] = 1;
			$uploadfile = $this->uploadFile($_FILES['emp_image'],$upload_image_param);

			if($uploadfile['flagimageupload']=='sucess'){
				
				$emp_image = trim($uploadfile['filnames']);
				
			}else{
				echo "IMAGENOTUPLOADED";
				exit;
			}
	
			$this->dbConn->beginTransaction();
			
			$emp_insert_query = "INSERT INTO employee (id,emp_name,emp_email,emp_mobile_number,emp_profile_image,emp_gender,emp_country_id,emp_state_id,emp_city_name,emp_reporting_manager) VALUES (NULL,'" .$emp_name. "','" .$emp_email. "','" .$emp_mobilenumber. "','" .$emp_image. "','" .$emp_gender. "','" .$emp_country. "','" .$emp_state. "','" .$emp_city_name. "'," .$emp_reporing_manager. ")";
			$p_emp_query = $this->dbConn->prepare($emp_insert_query);
			$p_emp_query->execute();

			$last_insert_id = $this->dbConn->lastInsertId();
			
			if(isset($last_insert_id) && !empty($last_insert_id)){
				
				$subjects_insert_query = 'INSERT INTO emp_subject_relation (emp_id,subject_id) VALUES ';
				
				foreach ($emp_subject as $emp_subject_key => $emp_subject_value) {
					
					$subjects_insert_query.='('.$last_insert_id.','.$emp_subject_value.'),';
				}

				$subjects_insert_query = rtrim($subjects_insert_query,",");
				
				$p_subject_query = $this->dbConn->prepare($subjects_insert_query);
				$p_subject_query->execute();

				$this->dbConn->commit();
				echo "SUCCESS";
			}else{
				$this->dbConn->rollBack();
				$imagearr = array();
				$imagearr['deletepath'] = IMMAGE_UPLODE_PARTH;
				$imagearr['deletefilename'] = $emp_image;
				$this->deleteImageFile($imagearr);
				echo "FAIL";	
			} 
		} catch(PDOException $ex) {
  			  //Something went wrong rollback!

    		$this->dbConn->rollBack();
    		echo $ex->getMessage();
    		$imagearr = array();
			$imagearr['deletepath'] = IMMAGE_UPLODE_PARTH;
			$imagearr['deletefilename'] = $emp_image;
			$this->deleteImageFile($imagearr);
    		echo "FAIL";
    	}	
	}

	public function delete_employee_information(){

		try {

			$emp_id = trim($_POST['emp_id']);
			
			$this->dbConn->beginTransaction();
			
			$empdata = $this->getAllEmployeeRecordById($emp_id);
			
			$imagearr = array();
			$imagearr['deletepath'] = IMMAGE_UPLODE_PARTH;
			$imagearr['deletefilename'] = $empdata[0]['emp_profile_image'];

			$emp_subject_rel_delete_query = "DELETE FROM emp_subject_relation WHERE emp_id = $emp_id";
			$p_emp_subject_rel_query = $this->dbConn->prepare($emp_subject_rel_delete_query);
			$p_emp_subject_rel_query->execute();	


			if($p_emp_subject_rel_query->rowCount()){
				
				$emp_delete_query = "DELETE FROM employee WHERE id = $emp_id";
				$p_emp_query = $this->dbConn->prepare($emp_delete_query);
				$p_emp_query->execute();

				$this->dbConn->commit();
				$this->deleteImageFile($imagearr);

				echo "SUCCESS";
			}else{

				$this->dbConn->rollBack();
				echo "FAIL";	
			}	
		} catch(PDOException $ex) {
	  		//Something went wrong rollback!
	    	$this->dbConn->rollBack();
	    	//echo $ex->getMessage();
	    	echo "FAIL";
    	}	
		
	}

	public function getAllEmployeeRecordById($emp_id){

		$emp_query = 'select e.emp_state_id, e.emp_country_id, e.emp_reporting_manager, e.emp_gender,e.emp_city_name, e.id,e.emp_name,e.emp_email,e.emp_mobile_number,e.emp_profile_image,e.emp_name, (SELECT emp_name from employee where id = e.emp_reporting_manager) as reporting_manager_name, country.country_name as country,state.name as state,e. emp_city_name as city, GROUP_CONCAT(s.name) as subject_names, GROUP_CONCAT(s.id) as subject_ids  from employee e inner join emp_subject_relation es ON e.id=es.emp_id inner join subjects s ON s.id=es.subject_id Left Join country ON e.emp_country_id=country.id Left Join state ON e.emp_state_id=state.id where e.id='.$emp_id.' group by e.id order by e.created_date desc';

		$p_emp_query = $this->dbConn->prepare($emp_query);
		$p_emp_query->execute();
		$p_emp_rows = $p_emp_query->fetchAll(PDO::FETCH_ASSOC);
				
		if($p_emp_query->rowCount()){
			return $p_emp_rows;
		}
	}

	public function update_employee_information(){
	
		try {
			$emp_id = trim($_POST['emp_id']);
			$emp_name = trim($_POST['emp_name']);
			$emp_mobilenumber = trim($_POST['emp_mobilenumber']);
			$emp_gender = trim($_POST['emp_gender']);
			$emp_country = trim($_POST['emp_country']);
			$emp_state = trim($_POST['emp_state']);
			$emp_city_name = trim($_POST['emp_city_name']);
			$emp_reporing_manager = trim($_POST['emp_reporing_manager']);
			$emp_subject = $_POST['emp_subject'];

			if(isset($_FILES['emp_image']['name']) && !empty($_FILES['emp_image']['name'])){
				$upload_image_param = array();
				$upload_image_param['uploadpath']= IMMAGE_UPLODE_PARTH ;
				$upload_image_param['displaypath']= IMMAGE_DISPLAY_PARTH ;
				$upload_image_param['maxsize'] = 10;
				$upload_image_param['filetype'] = array('png','jpg','jpeg','gif','PNG','JPEG','GIF','JPG');
				$upload_image_param['limit'] = 1;
				$uploadfile = $this->uploadFile($_FILES['emp_image'],$upload_image_param);

				if($uploadfile['flagimageupload']=='sucess'){
					
					$emp_image = trim($uploadfile['filnames']);
					
				}else{
					echo "IMAGENOTUPLOADED";
					exit;
				}
			}else{
				$emp_image = trim($_POST['emp_old_image']);
			}
			
			$this->dbConn->beginTransaction();

			$emp_update_query = "Update employee set emp_name='".$emp_name."',emp_mobile_number='".$emp_mobilenumber."',emp_gender='".$emp_gender."',emp_country_id='".$emp_country."',emp_state_id='".$emp_state."',emp_city_name='".$emp_city_name."',emp_reporting_manager='".$emp_reporing_manager."',emp_profile_image='".$emp_image."' where id=".$emp_id."";			
			
			$p_emp_query = $this->dbConn->prepare($emp_update_query);
			
			if($p_emp_query->execute()){
				
				$emp_subject_rel_delete_query = "DELETE FROM emp_subject_relation WHERE emp_id = $emp_id";
				$p_emp_subject_rel_query = $this->dbConn->prepare($emp_subject_rel_delete_query);
				$p_emp_subject_rel_query->execute();

				$subjects_insert_query = 'INSERT INTO emp_subject_relation (emp_id,subject_id) VALUES ';
			
				foreach ($emp_subject as $emp_subject_key => $emp_subject_value) {
					$subjects_insert_query.='('.$emp_id.','.$emp_subject_value.'),';
				}

				$subjects_insert_query = rtrim($subjects_insert_query,",");
			
				$p_subject_query = $this->dbConn->prepare($subjects_insert_query);
				$p_subject_query->execute();

				$this->dbConn->commit();
				
				if(isset($_FILES['emp_image']['name']) && !empty($_FILES['emp_image']['name'])){
					$imagearr['deletepath'] = IMMAGE_UPLODE_PARTH;
					$imagearr['deletefilename'] = trim($_POST['emp_old_image']);
					$this->deleteImageFile($imagearr);
				}

				echo "SUCCESS";
			}else{

				$imagearr = array();
				$imagearr['deletepath'] = IMMAGE_UPLODE_PARTH;
				$imagearr['deletefilename'] = $emp_image;
				$this->deleteImageFile($imagearr);

				$this->dbConn->rollBack();
				echo "FAIL";
			}	
		} catch(PDOException $ex) {
	  		//Something went wrong rollback!
	  		
	  		$imagearr = array();
			$imagearr['deletepath'] = IMMAGE_UPLODE_PARTH;
			$imagearr['deletefilename'] = $emp_image;
			$this->deleteImageFile($imagearr);

	    	$this->dbConn->rollBack();
	    	echo $ex->getMessage();
	    	echo "FAIL";
    	}
	} 

	public function uploadFile($files,array $array)
		{

			$uploaded_files = array();

			if(isset($files) && $files['name']!=""){

				//CHANGING PERMISSION OF THE DIRECTORY
				@chmod($array['uploadpath'], 0755);

				if($array['limit']==0 || $array['limit']>@count($files['name'])){
					$array['limit']=@count($files['name']);
				}

				for($a=0;$a<$array['limit'];$a++){

					if(@$array['maxsize']<=0) {$array['maxsize']=5000;}
					$allowedfiletypes = $array['filetype'];
					$max_size = $array['maxsize']*1024*1024;	//in KB

					$filename="";
					if($array['limit']>1){

						$currentfile_extension = end(@explode(".",$files['name'][$a]));

						if(in_array(strtolower($currentfile_extension),$allowedfiletypes)){

							$filename = date("YmdHis").rand(1000,9999).".".$currentfile_extension;

							if($files['size'][$a]<$max_size){	

								if(@move_uploaded_file($files['tmp_name'][$a], $array['uploadpath'].$filename)){

									$uploaded_files[]=$filename;

									//CHANGIN FILE PERMISSION
									@chmod($array['uploadpath'].$filename, 0755);
									return array('response'=>'IMAGESAVE','flagimageupload'=>'sucess','filnames'=>$uploaded_files);
								}else{
									return array('response'=>'IMAGENOTSAVE','flagimageupload'=>'fail','filnames'=>'');
								}
							}else{
								return array('response'=>'LIMITFORUPLOADIMAGE','flagimageupload'=>'fail','filnames'=>'');
							}
						}else{
							return array('response'=>'IMAGENOTVALIDATE','flagimageupload'=>'fail','filnames'=>'');
						}
					} else {
						
						$arrayimagename = @explode(".",$files['name']);
						$currentfile_extension = end($arrayimagename);

						if(in_array(strtolower($currentfile_extension),$allowedfiletypes)){
							
							$filename = date("YmdHis").rand(1000,9999).".".$currentfile_extension;
							
							if($files['size']<$max_size){

								if(@move_uploaded_file($files['tmp_name'], $array['uploadpath'].$filename)){

									$uploaded_files=$filename;
									
									@chmod($array['uploadpath'].$filename, 0755);
									return array('response'=>'IMAGESAVE','flagimageupload'=>'sucess','filnames'=>$uploaded_files);
								}else{
									return array('response'=>'IMAGENOTSAVE','flagimageupload'=>'fail','filnames'=>'');
								}
							}else{
								return array('response'=>'LIMITFORUPLOADIMAGE','flagimageupload'=>'fail','filnames'=>'');
							}
						}else{
							return array('response'=>'IMAGENOTVALIDATE','flagimageupload'=>'fail','filnames'=>'');
						}
					}
				}
			}
			return $uploaded_files;
			}

			public function deleteImageFile(array $array)
			{
				$deletepath = $array['deletepath'];
				$deletefilename = $array['deletefilename'];
	
				if(file_exists($deletepath.$deletefilename)){
					unlink($deletepath.$deletefilename);
					return true;
				}else{
					return false;
				}
			}

	public function getAllInvoceList(){
		
		$invoce_query = 'SELECT id,order_number,order_receive_name,order_receive_address,order_date,order_total FROM orders';

		$p_invoice_query = $this->dbConn->prepare($invoce_query);
		$p_invoice_query->execute();
		$p_invoice_rows = $p_invoice_query->fetchAll(PDO::FETCH_ASSOC);
				
		if($p_invoice_query->rowCount()){
			return $p_invoice_rows;
		}
	}

	public function getInvoceByIds($order_ids){
		$invoce_query = 'SELECT id,order_number,order_receive_name,order_receive_address,order_date,order_total FROM orders where id=:order_id';

		$p_invoice_query = $this->dbConn->prepare($invoce_query);
		$p_invoice_query->bindParam(':order_id', $order_ids);
		$p_invoice_query->execute();
		$p_invoice_rows = $p_invoice_query->fetchAll(PDO::FETCH_ASSOC);
				
		if($p_invoice_query->rowCount()){
			return $p_invoice_rows;
		}
	}

	public function getorderItemByorderIds($order_id){

		$order_item_query = 'SELECT order_item_id, order_id, order_item_product_id, order_item_quantity, order_item_price, order_item_actual_amount  FROM  order_item  WHERE order_id =:order_id';

		$p_order_item_query = $this->dbConn->prepare($order_item_query);
		$p_order_item_query->bindParam(':order_id', $order_id);
		$p_order_item_query->execute();
		$p_order_item_rows = $p_order_item_query->fetchAll(PDO::FETCH_ASSOC);
		
		if($p_order_item_query->rowCount()){
			return $p_order_item_rows;
		}	
	}

	public function getAllProductDetail(){

		$products_query = 'SELECT name, id FROM products';

		$p_products_query = $this->dbConn->prepare($products_query );
		$p_products_query->bindParam(':order_id', $order_id);
		$p_products_query->execute();
		$p_products_rows = $p_products_query->fetchAll(PDO::FETCH_ASSOC);
		
		if($p_products_query->rowCount()){
			return $p_products_rows;
		}	
	}

 		
}
?>