<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With,X-Powered-By, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

//--------------------------------------------------------
// SQL helper functions
//--------------------------------------------------------
function sqlGetConnection()
{    
	$server = "JIAHAO-VM\SQLEXPRESS2014";
    $user = "sa";
    $password = "imagine2018!!";
    $database = "abell.80-PUB-Dev";
    
    try{
    $conn = new PDO("sqlsrv:Server=".$server.";Database=".$database,$user,$password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(Exception $e)
    {
        die(print_r($e->getMessage() ) );
    }
	return $conn;
}	

function sqlQuery($conn, $sql, $params = [])
{
    $getResults = $conn->prepare($sql);
    $getResults->execute($params);
    $results = $getResults->fetchAll(PDO::FETCH_ASSOC);
	return $results;
}


function sqlExecute($conn, $sql, $params = [])
{
    $getResults = $conn->prepare($sql);
    $getResults->execute($params);
}



//--------------------------------------------------------
// Complete the work.
//--------------------------------------------------------
$app->get('/holo/complete/{ID}', function(Request $request, Response $response){
	$conn = sqlGetConnection();
	
	$id = $request->getAttribute('ID');
	$taskno = $request->getAttribute('TaskNo');
	$value = $request->getAttribute('Value');
	
	// Get the CurrentActivityID from Work
	//
	$tsql1 = "SELECT CurrentActivityID FROM Work WHERE ObjectID = '".$id."'";
	$getresults1 = $conn->prepare($tsql1);
	$getresults1->execute();
    $results = $getresults1->fetchAll(PDO::FETCH_BOTH);
	$currentActivityID = $results[0]['CurrentActivityID'];
	//echo $currentActivityID;

	// Set the actual start/end date
	$tsql1 = "update Work set ActualStartDateTime = '" . date('Y-m-d H:i:s') . "', ActualEndDateTime = '" . date('Y-m-d H:i:s') . "' WHERE ObjectID = '".$id."'";
	$getresults1 = $conn->prepare($tsql1);
	$getresults1->execute();

	// Simplicity's workflow ought to use the Windows Workflow Foundation to transit the state.
	//
	$tsql1 = "update Activity set ObjectName = 'PendingAcceptance' WHERE AttachedObjectID = '".$id."'";
	$getresults1 = $conn->prepare($tsql1);
	$getresults1->execute();
	
	// Simplicity's workflow ought to use the Windows Workflow Foundation to transit the state.
	//
	$tsql1 = "update Work set CurrentActivityName = 'PendingAcceptance' WHERE ObjectID = '".$id."'";
	$getresults1 = $conn->prepare($tsql1);
	$getresults1->execute();
	
	
	// Remove users assigned to this work
	//
	$tsql1 = "DELETE FROM ActivityUser WHERE ActivityID = '".$currentActivityID."'";
	$getresults1 = $conn->prepare($tsql1);
	$getresults1->execute();
	
	// Assign the super admin to this work.
	//
	$tsql1 = "INSERT INTO ActivityUser (ActivityID, UserID) VALUES ( '".$currentActivityID."', '611125EF-41FC-4A2A-B1E9-08D525316173')";
	$getresults1 = $conn->prepare($tsql1);
	$getresults1->execute();
	
   });


//--------------------------------------------------------
// Save individual checklist step.
//--------------------------------------------------------
$app->get('/holo/check/{ID}/{TaskNo}/{Value}', function(Request $request, Response $response){
	$conn = sqlGetConnection();
	
	$id = $request->getAttribute('ID');
	$taskno = $request->getAttribute('TaskNo');
	$value = $request->getAttribute('Value');
	
	if($value == "O") {
		$tsql1 = "update TaskChecklistItem set SelectedResponseID = 'E37A14E6-43E4-4355-B218-18030F6ED008' FROM ChecklistItem WHERE TaskChecklistItem.AttachedObjectID = '".$id."' AND ChecklistItem.ObjectID = TaskChecklistItem.ChecklistItemID AND ChecklistItem.StepNumber = '".$taskno."'";
		$getresults1 = $conn->prepare($tsql1);
		$getresults1->execute();
		echo "Checked";
	}
	else if($value == "X") {
		$tsql1 = "update TaskChecklistItem set SelectedResponseID = '83A2DE7A-18ED-412B-A386-AF040F6ED008' FROM ChecklistItem WHERE TaskChecklistItem.AttachedObjectID = '".$id."'  AND ChecklistItem.ObjectID = TaskChecklistItem.ChecklistItemID AND ChecklistItem.StepNumber = '".$taskno."'";
		$getresults1 = $conn->prepare($tsql1);
		$getresults1->execute();
		echo "Unchecked";
	}		
	else {
		$tsql1 = "update TaskChecklistItem set SelectedResponseID = NULL FROM ChecklistItem WHERE TaskChecklistItem.AttachedObjectID = '".$id."'  AND ChecklistItem.ObjectID = TaskChecklistItem.ChecklistItemID AND ChecklistItem.StepNumber = '".$taskno."'";
		$getresults1 = $conn->prepare($tsql1);
		$getresults1->execute();
		echo "Unchecked";
	}		
   }); 

   
//--------------------------------------------------------
// Gets the most recent announcement
//--------------------------------------------------------
$app->get('/holo/alert', function(Request $request, Response $response){
    $conn = sqlGetConnection();

    $tsql = "SELECT TOP 1 * FROM Announcement WHERE IsDeleted = 0 Order By CreatedDateTime DESC";

    $getResults = $conn->prepare($tsql);
    $getResults->execute();
    $results = $getResults->fetchAll(PDO::FETCH_BOTH);
    
    foreach($results as $row){
        echo $row['Announcement'] ;
    }
    
   });
   
//--------------------------------------------------------
// Gets Inbox detail.
//--------------------------------------------------------
$app->get('/holo/inbox', function(Request $request, Response $response){
	$conn = sqlGetConnection();
    $results = sqlQuery(
		$conn,
		"SELECT w.ObjectID, w.ObjectNumber, w.Priority, w.WorkDescription, w.CreatedDateTime, l.ObjectName as 'LocationName', e.ObjectName as 'EquipmentName', w.EquipmentID
		FROM Work w 
		LEFT JOIN Location l ON w.LocationID = l.ObjectID 
		LEFT JOIN Equipment e ON w.EquipmentID = e.ObjectID 
		LEFT JOIN Activity a on w.CurrentActivityID = a.ObjectID 
		WHERE w.IsDeleted = 0 AND a.ObjectName = 'PendingExecution'");
	
	$work = [];
	$work["Works"] = $results;
	
	echo json_encode($work);
		

});


//--------------------------------------------------------
// Gets Checklist detail.
//--------------------------------------------------------
$app->get('/holo/checklist/{ID}', function(Request $request, Response $response){
	$id = $request->getAttribute('ID');

	$conn = sqlGetConnection();
    $results = sqlQuery(
		$conn,
		"SELECT w.ObjectID, w.ObjectNumber, w.Priority, w.WorkDescription, w.CreatedDateTime, l.ObjectName as 'LocationName', e.ObjectName as 'EquipmentName', w.EquipmentID
		FROM Work w 
		LEFT JOIN Location l ON w.LocationID = l.ObjectID 
		LEFT JOIN Equipment e ON w.EquipmentID = e.ObjectID 
		LEFT JOIN Activity a on w.CurrentActivityID = a.ObjectID 
		WHERE w.IsDeleted = 0 AND w.ObjectID = :id",
		[$id]);
		
    foreach($results as $row)
	{
		$work = $row;
		$work['TaskChecklistItems'] = 
			sqlQuery(
				$conn,
				"SELECT 
				t.ObjectID, t.ObjectName, t.SelectedResponseID, ci.StepNumber as 'ChecklistItemStepNumber', 
					CASE 
					WHEN t.SelectedResponseID = 'E37A14E6-43E4-4355-B218-18030F6ED008' THEN 'O' 
					WHEN t.SelectedResponseID = '83A2DE7A-18ED-412B-A386-AF040F6ED008' THEN 'X' 
					ELSE '_'					
					END as 'SelectedResponse'
				from TaskChecklistItem t, Work w, ChecklistItem ci 
				where w.ObjectID = t.AttachedObjectID and ci.ObjectID = t.ChecklistItemID and w.ObjectID = :id 
				order by w.ObjectNumber, ci.StepNumber",
				[$id]);		
		
		break;
	}		
	
	echo json_encode($work);
		

});


//--------------------------------------------------------
// Gets Equipment detail.
//--------------------------------------------------------
$app->get('/holo/equipment/{ID}', function(Request $request, Response $response){
	$id = $request->getAttribute('ID');

	$conn = sqlGetConnection();
    $results = sqlQuery(
		$conn,
		"
		SELECT
		e.ObjectName as 'EquipmentName', l.FullPath as 'Location', e.Supplier, e.Brand, e.ModelNumber, e.SerialNumber, e.DateOfManufacture, e.WarrantyExpiryDate, et.ObjectName as 'EquipmentType' 
		FROM Equipment e 
		LEFT JOIN Location l ON e.LocationID = l.ObjectID 
		LEFT JOIN EquipmentType et ON e.EquipmentTypeID = et.ObjectID 
		WHERE e.ObjectID = :id",
		[$id]);
    
    foreach($results as $row)
	{
		$row['Works'] = sqlQuery(
			$conn,
			"SELECT w.ObjectID, w.ObjectNumber, w.Priority, w.WorkDescription, w.CreatedDateTime, l.ObjectName as 'LocationName', e.ObjectName as 'EquipmentName', w.EquipmentID 
			FROM Work w 
			LEFT JOIN Location l ON w.LocationID = l.ObjectID 
			LEFT JOIN Equipment e ON w.EquipmentID = e.ObjectID 
			LEFT JOIN Activity a on w.CurrentActivityID = a.ObjectID 
			WHERE w.IsDeleted = 0 AND w.EquipmentID = :id",
			[$id]);
		
		echo (json_encode($row));
		break;
    }
});
  
  
//--------------------------------------------------------
// Gets the Video streaming URL for the demo.
//--------------------------------------------------------
$app->get('/holo/stream', function(Request $request, Response $response){
    echo "http://172.20.10.3/webcamstreamer/video.aspx";
});
   

  
   
