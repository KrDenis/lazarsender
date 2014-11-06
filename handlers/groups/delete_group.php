<?php
	require_once '../../include/auth.php';
	require_once '../../include/db_connect.php';

	if (isset($_REQUEST['id'])) {
		$id = $_REQUEST['id'];
	} else {
		$id = NULL;
		exit();
	}

	$stm =  $pdo->prepare("DELETE FROM groups WHERE id=:id");
	$stm->bindParam(":id",$id);
		 	try {
				$stm->execute();
			} catch (PDOException $e) {
				logging(implode(",",$stm->errorInfo()),true,__FILE__,__LINE__);
				echo "MySql Error.Watch log.";
			}

	$sth =  $pdo->prepare("DELETE FROM recepients WHERE mail_group=:id");
	$sth->bindParam(":id",$id);
		 	try {
				$sth->execute();
			} catch (PDOException $e) {
				logging(implode(",",$sth->errorInfo()),true,__FILE__,__LINE__);
				echo "MySql Error.Watch log.";
			}

	echo true;
?>