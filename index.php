<?php
require 'config.php';
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

$app->post('/login','login'); /* Login */
$app->post('/signup','signup'); /* Insere os dados do cliente  */
$app->post('/edit', 'edit'); /* Edita dados cliente*/
$app->post('/delete', 'delete'); /*Deleta dados cliente*/
$app->run();

/************************* Login Cliente *************************************/
function login() {
    
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    
    try {
        
        $db = getDB();
        $userData ='';
        $sql = "SELECT cpf, nome, email, endereco, municipio, telefone FROM cliente WHERE email= :username and senha= :password";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("username", $data->username, PDO::PARAM_STR);
        $passwordsemcriptografia = $data->password;
        $password=hash('sha256',$data->password);
        $stmt->bindParam("password", $password, PDO::PARAM_STR);     
        $stmt->execute();
        $mainCount=$stmt->rowCount();
        $userData = $stmt->fetch(PDO::FETCH_OBJ);
        
        if(!empty($userData))
        {
            $userData->password = $passwordsemcriptografia;
            $user_id=$userData->user_id;
            $userData->token = apiToken($user_id);
        }
        
        $db = null;
         if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userData . '}';
            } else {
               echo '{"error":{"text":"E-mail ou senha inválidos!"}}';
            }

           
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


/************************* Cadastro Cliente *************************************/
function signup() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $cpf=$data->cpf;
    $nome=$data->name;
    $endereco=$data->endereco;
    $municipio=$data->municipio;
    $telefone=$data->telefone;
    $email=$data->email;   
    $password=$data->password;
    
    try {
        
        $email_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$~i', $email);
                
        if (strlen(trim($cpf))>0 && strlen(trim($password))>0 && strlen(trim($email))>0 && $email_check>0)
        {
            $db = getDB();
            $userData = '';
            $sql = "SELECT cpf FROM cliente WHERE email=:email";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("email", $email,PDO::PARAM_STR);
            $stmt->execute();
            $mainCount=$stmt->rowCount();
            $created=time();
            
            if($mainCount==0)
            {
                
                /*INSERINDO DADOS CLIENTE NO BANCO*/
                $sql1="INSERT INTO cliente(cpf, nome, endereco, municipio, telefone, email, senha)VALUES(:cpf,:nome,:endereco,:municipio,:telefone,:email,:password)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("cpf", $cpf,PDO::PARAM_STR);
                $stmt1->bindParam("nome", $nome,PDO::PARAM_STR);
                $stmt1->bindParam("endereco", $endereco,PDO::PARAM_STR);
                $stmt1->bindParam("municipio", $municipio,PDO::PARAM_STR);
                $stmt1->bindParam("telefone", $telefone,PDO::PARAM_STR);
                $stmt1->bindParam("email", $email,PDO::PARAM_STR);
                $passwordsemcriptografia = $data->password;
                $password=hash('sha256',$data->password);
                $stmt1->bindParam("password", $password,PDO::PARAM_STR);
                $stmt1->execute();
                                
                $userData=internalUserDetails($email);
                $userData->password = $passwordsemcriptografia;
                
            }
            
            $db = null;
         
            if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userData . '}';
            } else {
               echo '{"error":{"text":"Digite valores válidos!"}}';
            }
        }
        else{
            echo '{"error":{"text":"Digite valores válidos!"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

/************************* Retorna valores do cliente *************************************/
function internalUserDetails($input) {
    
    try {
        $db = getDB();
        $sql = "SELECT cpf, nome, email, endereco, municipio, telefone, senha FROM cliente WHERE email=:input";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("input", $input,PDO::PARAM_STR);
        $stmt->execute();
        $usernameDetails = $stmt->fetch(PDO::FETCH_OBJ);
        $usernameDetails->token = apiToken($usernameDetails->user_id);
        $db = null;
        return $usernameDetails;
        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
    
}

/************************* Atualiza cadastro Cliente *************************************/
function edit() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $email=$data->email;
    $cpf=$data->cpf;
    $nome=$data->name;
    $endereco=$data->endereco;
    $municipio=$data->municipio;
    $telefone=$data->telefone;
    $email=$data->email;   
    $password=$data->password;
    
    try {
        
        //$password_check = preg_match('~^[A-Za-z0-9!@#$%^&*()_]{6,20}$~i', $password);
                
        if (strlen(trim($cpf))>0 && strlen(trim($password))>0)
        {
            $db = getDB();
            $userData = '';
            $sql = "SELECT cpf FROM cliente WHERE cpf=:cpf";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("cpf", $cpf,PDO::PARAM_STR);
            $stmt->execute();
            $mainCount=$stmt->rowCount();
            $created=time();
                        
            if($mainCount==1)
            {
                
                /*Atualiza os dados do cliente*/
                $sql1="UPDATE cliente set nome= :nome, endereco= :endereco, municipio= :municipio, telefone= :telefone, senha= :password where cpf= :cpf";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("cpf", $cpf,PDO::PARAM_STR);
                $stmt1->bindParam("nome", $nome,PDO::PARAM_STR);
                $stmt1->bindParam("endereco", $endereco,PDO::PARAM_STR);
                $stmt1->bindParam("municipio", $municipio,PDO::PARAM_STR);
                $stmt1->bindParam("telefone", $telefone,PDO::PARAM_STR);
                $password=hash('sha256',$data->password);
                $stmt1->bindParam("password", $password,PDO::PARAM_STR);
                $stmt1->execute();
                                
                $userData=internalUserDetails($email);
                
            }
            
            $db = null;
         
            if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userData . '}';
            } else {
               echo '{"error":{"text":"Digite valores válidos!"}}';
            }
        }
        else{
            echo '{"error":{"text":"Digite valores válidos!"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

/************************* Deleta Cliente *************************************/
function delete(){
    
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $cpf=$data->cpf;
    try {
        
        $db = getDB();
        $sql = "DELETE FROM cliente WHERE cpf = :cpf";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("cpf", $cpf,PDO::PARAM_STR);
        if($stmt->execute()){
            
            $message = "sucess";
            $userData->message = $message;
             if($userData){
                $userData = json_encode($userData);
                 echo '{"userData": ' .$userData . '}';
             } else {
                echo '{"error":{"text":"Não foi possível deletar cliente!"}}';
             }
        }
    
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
    
}