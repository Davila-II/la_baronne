<?php
	$cible = isset($_GET['cible'])? trim($_GET['cible']) : "load";
    
    if (isset($_REQUEST['token'])){

		$current_user = Utilisateur::getUserByToken($_REQUEST['token']);
		
		if($current_user->ID_UTILISATEUR==0){
			$result = array();
			$result['status'] = "error";
			$result['reason'] = "Non authorisé";
			echo json_encode($result);
		}
		else {
			switch($cible){	
				case 'services':{ 
					echo json_encode(Service::allServices());
					break;
				}
				case 'chauffeurs':{ 
					include("controller/ChauffeurController.php");
					break;
				}
				case 'statistique':{ 
					include("controller/StatistiqueController.php");
					break;
				}
				case 'pays':{ 
					include("models/pays.php");
					break;
				}
				case 'logoutunlock':{ 
					include("logout.php");
					break;
				}
				default:{
					header("Location: ".getBaseUrl()."home");
					/*header('location:../views/error.php');*/
				}
			}
		}
	}
	else if($cible=='login'){
		echo json_encode(Utilisateur::login());
	}
	else if($cible=='register'){
		echo json_encode(Utilisateur::register());
	}
	else{
		$result = array();
		$result['status'] = "error";
		$result['reason'] = "Rou";
		echo json_encode($result);
	}
?>