<?php
include dirname(__FILE__)."/session.php";
include_once dirname(__FILE__)."/conf.php";
include_once dirname(__FILE__)."/functions.php";


$json = new stdClass();
if( true
    && isset($_POST['username']) 
    && isset($_POST['password']) 
    && isset($_POST['remote_dir']) 
    && isset($_FILES['file']['tmp_name'])
    )
{

	$uid = trim($_POST['username']);
	$pas = trim($_POST['password']);
	$userid = "";
	if(strlen($cfg->users))
	{
		if(HTPasswd::auth($uid, $pas, $cfg->users))
		{
			$userid = $uid;
		}
	}
	if(!$userid)
	{
		$json = array(
			'response_code'=>'002',
			'response_text'=>'Unauthorized',
			'data'=>new StdClass()
		);
	}
	else
	{
	
		$remote_dir = @$_POST['remote_dir'];
		$target_dir = rtrim($cfg->rootdir, "/")."/".ltrim($remote_dir, "/");
		if(!file_exists($target_dir))
		{
			mkdir($target_dir, 0755);
		}
		$basename = basename($_FILES['file']['name']);
		$target_path = $target_dir."/".$basename;   

		if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path))
		{  
			unset($json);
			$json = array(
				'response_code'=>'001',
				'response_text'=>'File uploaded successfully',
				'data'=>array(
					'file_name'=>$basename,
					'target_path'=>$target_path
				)
			);
		} 
		else
		{  
			unset($json);
			$json = array(
				'response_code'=>'000',
				'response_text'=>'Sorry, file not uploaded, please try again',
				'data'=>array(
					'file_name'=>$basename,
					'target_path'=>$target_path
				)
			);
		}
	}
	header("Content-type: application/json");
	echo json_encode($json);
}
?>