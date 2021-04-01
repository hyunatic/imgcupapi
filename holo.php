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

// Get All Customers
$app->get('/api/customers', function(Request $request, Response $response){
    $sql = "SELECT * FROM customers";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->query($sql);
        $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo json_encode($customers);
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Get Chat
$app->get('/api/chat/getchat/{username}/{receiver}', function(Request $request, Response $response){
     $username = $request->getAttribute('username');
    $receiver = $request->getAttribute('receiver');

    $arr = array($username,$receiver);
    sort($arr);
    $chatid = $arr[0] . $arr[1];

    $servername = "aain9dw2210mx9.czi05dgbbsnf.ap-southeast-1.rds.amazonaws.com";
    $username = "admin";
    $password = "nyp12345";
    $dbname = "CodeX";

    $conn = new mysqli($servername,$username,$password,$dbname);
    //Check Connection
    if(!$conn){
        die("Connection failed.". mysqli_connect_error());
    }
    $sql = "SELECT * FROM ChattingSystem WHERE ChatID = '$chatid'";
    $result = mysqli_query($conn,$sql);

    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            echo $row['UserName'] .":" . $row['Message'] . ",";
        }
    }
});

// Add Chat
$app->get('/chat/{sender}/{messagesent}/{receiver}', function(Request $request, Response $response){
    $username = $request->getAttribute('sender');
    $message = $request->getAttribute('messagesent');
    $receiver = $request->getAttribute('receiver');

    $arr = array($username,$receiver);
    sort($arr);
    $chatid = $arr[0] . $arr[1];

    $sql = "INSERT INTO ChattingSystem (UserName,Message,Receiver,ChatID) VALUES
    (:username,:message,:receiver,:chatid)";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':message',  $message);
        $stmt->bindParam(':receiver',  $receiver);
        $stmt->bindParam(':chatid',  $chatid);
        $stmt->execute();


        echo '{"notice": {"text": "Customer Updated"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Get Single Customer
$app->get('/api/customer/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');

    $sql = "SELECT * FROM customers WHERE id = $id";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->query($sql);
        $customer = $stmt->fetch(PDO::FETCH_OBJ);
        $db = null;
        echo json_encode($customer);
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

//Hololens Login
$app->get('/holo/login/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');

    $servername = "aain9dw2210mx9.czi05dgbbsnf.ap-southeast-1.rds.amazonaws.com";
    $username = "admin";
    $password = "nyp12345";
    $dbname = "CodeX";

    $conn = new mysqli($servername,$username,$password,$dbname);
    //Check Connection
    if(!$conn){
        die("Connection failed.". mysqli_connect_error());
    }
    $sqlupdate  = "UPDATE UserLogin SET LoginStatus = 'Hololens' WHERE id = $id";
    $sql = "SELECT * FROM UserLogin WHERE id = $id";
    $result = mysqli_query($conn,$sql);
    $result1 = mysqli_query($conn,$sqlupdate);

    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            echo $row['UserName'];
        }
    }
});

//Hololens Login
$app->get('/holo/logout/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');

    $servername = "aain9dw2210mx9.czi05dgbbsnf.ap-southeast-1.rds.amazonaws.com";
    $username = "admin";
    $password = "nyp12345";
    $dbname = "CodeX";

    $conn = new mysqli($servername,$username,$password,$dbname);
    //Check Connection
    if(!$conn){
        die("Connection failed.". mysqli_connect_error());
    }
    $sqlupdate  = "UPDATE UserLogin SET LoginStatus = 'Offline' WHERE id = $id";
    $sql = "SELECT * FROM UserLogin WHERE id = $id";
    $result = mysqli_query($conn,$sql);
    $result1 = mysqli_query($conn,$sqlupdate);

    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            echo $row['UserName'];
        }
    }
});

//Hololens List Non-empty items
$app->get('/holo/list', function(Request $request, Response $response){


    $servername = "aain9dw2210mx9.czi05dgbbsnf.ap-southeast-1.rds.amazonaws.com";
    $username = "admin";
    $password = "nyp12345";
    $dbname = "CodeX";

    $conn = new mysqli($servername,$username,$password,$dbname);
    //Check Connection
    if(!$conn){
        die("Connection failed.". mysqli_connect_error());
    }
        $sql = "SELECT * FROM ItemInventory WHERE ProdName IS NOT NULL";

    $result = mysqli_query($conn,$sql);

    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            echo $row['PositionID'] . "~";
        }
    }
});

//Hololens List Empty items
$app->get('/holo/empty', function(Request $request, Response $response){


    $servername = "aain9dw2210mx9.czi05dgbbsnf.ap-southeast-1.rds.amazonaws.com";
    $username = "admin";
    $password = "nyp12345";
    $dbname = "CodeX";

    $conn = new mysqli($servername,$username,$password,$dbname);
    //Check Connection
    if(!$conn){
        die("Connection failed.". mysqli_connect_error());
    }
        $sql = "SELECT * FROM ItemInventory WHERE ProdName IS NULL";

    $result = mysqli_query($conn,$sql);

    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            echo $row['PositionID'] . "~";
        }
    }
});

//Hololens Stock-up Message
$app->get('/holo/stockup/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    echo "You have started moving items from " . $id;
   
});

//Hololens complete order Message
$app->get('/holo/complete/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    echo "You have completed the order for " . $id;
   
});

//Hololens Send Latest Nofications to user wearing Hololens
$app->get('/holo/msg/{username}', function(Request $request, Response $response){
    $username = $request->getAttribute('username');

        $sql = "SELECT * FROM ChattingSystem WHERE Receiver = :username ORDER BY ChatNo DESC LIMIT 1;";

   try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $customer = $stmt->fetch(PDO::FETCH_OBJ);
        $result = json_encode($customer);
        $array = json_decode($result , true);
        echo $array['UserName'] . "|" . $array['Message'];

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

//Hololens item picked from the their shelves
$app->get('/holo/picked/{username}/{itemid}', function(Request $request, Response $response){
    $userid = $request->getAttribute('username');
    $itemid = $request->getAttribute('itemid');
    $status = "Stock-out task has been ended by  " . $userid;
    $date = date("Y-m-d H:i:s");

    $sql = "UPDATE Instructions SET ItemPicked = 'Yes' WHERE AssignedTo = '$userid' AND PositionID = '$itemid' AND ItemPicked IS NULL;";
    $sql1 = "SELECT * FROM Instructions  WHERE AssignedTo = '$userid' AND PositionID = '$itemid' AND ItemPicked IS NOT NULL;";
    $sql2 = "INSERT INTO ItemReporting (ItemNumber,UserName, ReportType, CurrentTime) VALUES
    (:item,:user,:type,:datetime)";

    $servername = "aain9dw2210mx9.czi05dgbbsnf.ap-southeast-1.rds.amazonaws.com";
    $username = "admin";
    $password = "nyp12345";
    $dbname = "CodeX";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql2);
        $stmt->bindParam(':item', $item);
        $stmt->bindParam(':user',  $userid);
        $stmt->bindParam(':type',  $status);
        $stmt->bindParam(':datetime',  $date);
        $stmt->execute();
              echo '{"notice": {"text": "Customer Updated"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
    

    $conn = new mysqli($servername,$username,$password,$dbname);
    //Check Connection
    if(!$conn){
        die("Connection failed.". mysqli_connect_error());
    }
      $result = mysqli_query($conn,$sql);
      $result1 = mysqli_query($conn,$sql1);
       if(mysqli_num_rows($result1) > 0){
        while($row = mysqli_fetch_assoc($result1)){
            echo $row['InstructionID'] . "~";
        }
       }
});

//Hololens Send Auto Pick-up List to user wearing Hololens
$app->get('/holo/pickup/{username}', function(Request $request, Response $response){
    $userid = $request->getAttribute('username');
    $type = $userid . " has received a Stock-Out task";
     $date = date("Y-m-d H:i:s");

    $servername = "aain9dw2210mx9.czi05dgbbsnf.ap-southeast-1.rds.amazonaws.com";
    $username = "admin";
    $password = "nyp12345";
    $dbname = "CodeX";

    $conn = new mysqli($servername,$username,$password,$dbname);
    //Check Connection
    if(!$conn){
        die("Connection failed.". mysqli_connect_error());
    }
    
    $sqlupdate  = "UPDATE Instructions SET AssignedTo = :username WHERE AssignedTo IS NULL LIMIT 1";
    $sql = "SELECT * FROM Instructions WHERE AssignedTo = '$userid' AND ItemPicked IS NULL ORDER BY InstructionID DESC LIMIT 1;"; 
    $sqlinsert = "INSERT INTO ItemReporting (UserName, ReportType, CurrentTime) VALUES
    (:username,:type,:datetime)";
        try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sqlupdate);
        $stmt->bindParam(':username', $userid);
        $stmt->execute();

                $result = mysqli_query($conn,$sql);
        // Get DB Object
        $db1 = new db();
        // Connect
        $db1 = $db1->connect();

        $stmt = $db1->prepare($sqlinsert);
        $stmt->bindParam(':username', $userid);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':datetime', $date);
        $stmt->execute();

        }
        catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
          
           $result1 = mysqli_query($conn,$sqlinsert);
       if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            echo $row['PositionID'] . "|" . $row['ItemName'] . "|" . $row['Quantity'];
        }
    }

});

//Retrieve Current Item Scanned
$app->get('/holo/item/{itemid}', function(Request $request, Response $response){
    $itemid = $request->getAttribute('itemid');

        $sql = "SELECT * FROM ItemInventory WHERE PositionID = :itemid";

   try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':itemid', $itemid);
        $stmt->execute();

        $customer = $stmt->fetch(PDO::FETCH_OBJ);
        $result = json_encode($customer);
        $array = json_decode($result , true);
        echo $array['PositionID'] . "|" . $array['ProdName'] . "|" . $array['Keyword'] . "|" . $array['Quantity'] . "|" . $array['RegisteredDate'];

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

//End task for stockout
$app->get('/holo/pickup/end/{user}', function(Request $request, Response $response){
    $user = $request->getAttribute('user');
    $status = $user . " has placed the items in Loading Bay 2";
     $date = date("Y-m-d H:i:s");

    $sql = "INSERT INTO ItemReporting (UserName, ReportType, CurrentTime) VALUES
    (:user,:type,:datetime)";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user',  $user);
        $stmt->bindParam(':type',  $status);
        $stmt->bindParam(':datetime',  $date);
        $stmt->execute();


        echo '{"notice": {"text": "Customer Updated"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

//Hololens Send Auto Stock-In List to user wearing Hololens
$app->get('/holo/stockin/{username}', function(Request $request, Response $response){
    $userid = $request->getAttribute('username');
    $type = $userid . " has received a new Stock-In task";
     $date = date("Y-m-d H:i:s");

    $servername = "aain9dw2210mx9.czi05dgbbsnf.ap-southeast-1.rds.amazonaws.com";
    $username = "admin";
    $password = "nyp12345";
    $dbname = "CodeX";

    $conn = new mysqli($servername,$username,$password,$dbname);
    //Check Connection
    if(!$conn){
        die("Connection failed.". mysqli_connect_error());
    }
    
    $sqlupdate  = "UPDATE StockInInstructions SET AssignedTo = :username WHERE AssignedTo IS NULL LIMIT 1";
    $sql = "SELECT * FROM StockInInstructions WHERE AssignedTo = '$userid' AND ItemPicked IS NULL ORDER BY InstructionID DESC LIMIT 1;"; 
    $sqlinsert = "INSERT INTO ItemReporting (UserName, ReportType, CurrentTime) VALUES
    (:username,:type,:datetime)";
        try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sqlupdate);
        $stmt->bindParam(':username', $userid);
        $stmt->execute();

        // Get DB Object
        $db1 = new db();
        // Connect
        $db1 = $db1->connect();

        $stmt = $db1->prepare($sqlinsert);
        $stmt->bindParam(':username', $userid);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':datetime', $date);
        $stmt->execute();
        }
        catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
          $result = mysqli_query($conn,$sql);
           $result1 = mysqli_query($conn,$sqlinsert);
       if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            echo $row['PositionID'] . "|" . $row['ItemName'] . "|" . $row['Quantity'];
        }
    }

});

//Hololens item placed on their shelves and ended their stock in Task (Docking bay)
$app->get('/holo/stockin/end/{username}/{itemid}', function(Request $request, Response $response){
    $userid = $request->getAttribute('username');
    $itemid = $request->getAttribute('itemid');
    $status = "Stock-in Task has been completed by " . $userid;
    $date = date("Y-m-d H:i:s");

    $sql = "UPDATE StockInInstructions SET ItemPicked = 'Yes' WHERE AssignedTo = '$userid' AND PositionID = '$itemid' AND ItemPicked IS NULL;";
    $sql1 = "SELECT * FROM StockInInstructions  WHERE AssignedTo = '$userid' AND PositionID = '$itemid' AND ItemPicked IS NOT NULL;";
    $sql2 = "INSERT INTO ItemReporting (ItemNumber,UserName, ReportType, CurrentTime) VALUES
    (:item,:user,:type,:datetime)";

    $servername = "aain9dw2210mx9.czi05dgbbsnf.ap-southeast-1.rds.amazonaws.com";
    $username = "admin";
    $password = "nyp12345";
    $dbname = "CodeX";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql2);
        $stmt->bindParam(':item', $item);
        $stmt->bindParam(':user',  $userid);
        $stmt->bindParam(':type',  $status);
                $stmt->bindParam(':datetime',  $date);
        $stmt->execute();

                     

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
    

    $conn = new mysqli($servername,$username,$password,$dbname);
    //Check Connection
    if(!$conn){
        die("Connection failed.". mysqli_connect_error());
    }
      $result = mysqli_query($conn,$sql);
      $result1 = mysqli_query($conn,$sql1);
       if(mysqli_num_rows($result1) > 0){
        while($row = mysqli_fetch_assoc($result1)){
            echo '{"notice": {"text": "Customer Updated"}';
        }
       }
});

// Report that user has started stock-In task
$app->get('/holo/stockin/start/{user}/{item}', function(Request $request, Response $response){
    $user = $request->getAttribute('user');
    $item = $request->getAttribute('item');
    $status = $user . " has started the stock-In task in " . $item;
     $date = date("Y-m-d H:i:s");

    $sql = "INSERT INTO ItemReporting (ItemNumber,UserName, ReportType, CurrentTime) VALUES
    (:item,:user,:type,:datetime)";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':item', $item);
        $stmt->bindParam(':user',  $user);
        $stmt->bindParam(':type',  $status);
                $stmt->bindParam(':datetime',  $date);
        $stmt->execute();


        echo '{"notice": {"text": "Customer Updated"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Report that user has started stock-In task
$app->get('/holo/stockout/start/{user}/{item}', function(Request $request, Response $response){
    $user = $request->getAttribute('user');
    $item = $request->getAttribute('item');
    $status = $user . " has started the stock-out task in " . $item;
     $date = date("Y-m-d H:i:s");

    $sql = "INSERT INTO ItemReporting (ItemNumber,UserName, ReportType, CurrentTime) VALUES
    (:item,:user,:type,:datetime)";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':item', $item);
        $stmt->bindParam(':user',  $user);
        $stmt->bindParam(':type',  $status);
                $stmt->bindParam(':datetime',  $date);
        $stmt->execute();


        echo '{"notice": {"text": "Customer Updated"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Report item is missing
$app->get('/holo/missing/{user}/{item}', function(Request $request, Response $response){
    $user = $request->getAttribute('user');
    $item = $request->getAttribute('item');
    $status = "Missing";
     $date = date("Y-m-d H:i:s");

    $sql = "INSERT INTO ItemReporting (ItemNumber,UserName, ReportType, CurrentTime) VALUES
    (:item,:user,:type,:datetime)";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':item', $item);
        $stmt->bindParam(':user',  $user);
        $stmt->bindParam(':type',  $status);
                $stmt->bindParam(':datetime',  $date);
        $stmt->execute();


        echo '{"notice": {"text": "Customer Updated"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Report item has been stocked up
$app->get('/holo/stock/{user}/{item}/{positionid}', function(Request $request, Response $response){
    $user = $request->getAttribute('user');
    $item = $request->getAttribute('item');
    $position = $request->getAttribute('positionid');
    $status = "Item has been place at " . $position;
     $date = date("Y-m-d H:i:s");

    $sql = "INSERT INTO ItemReporting (ItemNumber,UserName, ReportType, CurrentTime) VALUES
    (:item,:user,:type,:datetime)";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':item', $item);
        $stmt->bindParam(':user',  $user);
        $stmt->bindParam(':type',  $status);
                $stmt->bindParam(':datetime',  $date);
        $stmt->execute();


        echo '{"notice": {"text": "Customer Updated"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Report item is damaged
$app->get('/holo/damage/{user}/{item}', function(Request $request, Response $response){
    $user = $request->getAttribute('user');
    $item = $request->getAttribute('item');
    $status = $item . " is Damaged report by " . $user;
     $date = date("Y-m-d H:i:s");

    $sql = "INSERT INTO ItemReporting (ItemNumber,UserName, ReportType,CurrentTime) VALUES
    (:item,:user,:type,:datetime)";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':item', $item);
        $stmt->bindParam(':user',  $user);
        $stmt->bindParam(':type',  $status);
                $stmt->bindParam(':datetime',  $date);
        $stmt->execute();


        echo '{"notice": {"text": "Customer Updated"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Report item has been placed
$app->get('/holo/placed/{user}/{item}', function(Request $request, Response $response){
    $user = $request->getAttribute('user');
    $item = $request->getAttribute('item');
    $status = "Moved Successfully";
    $date = date("Y-m-d H:i:s");

    $sql = "INSERT INTO ItemReporting (ItemNumber,UserName, ReportType,CurrentTime) VALUES
    (:item,:user,:type,:datetime)";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':item', $item);
        $stmt->bindParam(':user',  $user);
        $stmt->bindParam(':type',  $status);
        $stmt->bindParam(':datetime',  $date);
        $stmt->execute();


        echo $date;

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

//Report item that is moving
$app->get('/holo/move/{user}/{item}', function(Request $request, Response $response){
    $user = $request->getAttribute('user');
    $item = $request->getAttribute('item');
    $status = "Started Moving";
    $date = date("Y-m-d H:i:s");

    $sql = "INSERT INTO ItemReporting (ItemNumber,UserName, ReportType,CurrentTime) VALUES
    (:item,:user,:type,:datetime)";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':item', $item);
        $stmt->bindParam(':user',  $user);
        $stmt->bindParam(':type',  $status);
        $stmt->bindParam(':datetime',  $date);
        $stmt->execute();


        echo $date;

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});


// Add Customer
$app->post('/api/customer/add', function(Request $request, Response $response){
    $first_name = $request->getParam('first_name');
    $last_name = $request->getParam('last_name');
    $phone = $request->getParam('phone');
    $email = $request->getParam('email');
    $address = $request->getParam('address');
    $city = $request->getParam('city');
    $state = $request->getParam('state');

    $sql = "INSERT INTO customers (first_name,last_name,phone,email,address,city,state) VALUES
    (:first_name,:last_name,:phone,:email,:address,:city,:state)";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);

        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name',  $last_name);
        $stmt->bindParam(':phone',      $phone);
        $stmt->bindParam(':email',      $email);
        $stmt->bindParam(':address',    $address);
        $stmt->bindParam(':city',       $city);
        $stmt->bindParam(':state',      $state);

        $stmt->execute();

        echo '{"notice": {"text": "Customer Added"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Update Customer
$app->put('/api/customer/update/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $first_name = $request->getParam('first_name');
    $last_name = $request->getParam('last_name');
    $phone = $request->getParam('phone');
    $email = $request->getParam('email');
    $address = $request->getParam('address');
    $city = $request->getParam('city');
    $state = $request->getParam('state');

    $sql = "UPDATE customers SET
				first_name 	= :first_name,
				last_name 	= :last_name,
                phone		= :phone,
                email		= :email,
                address 	= :address,
                city 		= :city,
                state		= :state
			WHERE id = $id";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);

        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name',  $last_name);
        $stmt->bindParam(':phone',      $phone);
        $stmt->bindParam(':email',      $email);
        $stmt->bindParam(':address',    $address);
        $stmt->bindParam(':city',       $city);
        $stmt->bindParam(':state',      $state);

        $stmt->execute();

        echo '{"notice": {"text": "Customer Updated"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Delete Customer
$app->delete('/api/customer/delete/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');

    $sql = "DELETE FROM customers WHERE id = $id";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "Customer Deleted"}';
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});
