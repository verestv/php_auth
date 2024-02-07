<?php
require_once 'connection.php';
session_start();

if(isset($_SESSION['user'])){
	header("location: welcome.php");
}

if(isset($_REQUEST['register_btn'])){

	

	$name = filter_var($_REQUEST['name'],FILTER_SANITIZE_STRING);
	$email = filter_var(strtolower($_REQUEST['email']),FILTER_SANITIZE_EMAIL);
	$password = strip_tags($_REQUEST['password']);

	if(empty($name)){
		$errormsg[0][] = 'Name required';
	}

	if(empty($email)){
		$errormsg[1][] = 'Email required';
	}

	if(empty($password)){
		$errormsg[2][] = 'Password required';
	}

	if(strlen($password) < 8){
		$errormsg[2][] = 'Password must be at least 8 characters';
	}

	if(empty($errormsg)){
		try{
			$select_stmt = $pdo->prepare("SELECT username,email FROM users WHERE email = :email");
			$select_stmt->execute([':email' => $email]);
			$row = $select_stmt->fetch(PDO::FETCH_ASSOC);

			if(isset($row['email']) == $email){
				$errormsg[1][] = "Email address already exists, please choose another or login instead";
			} else{
				$hashed_password = hash('sha512', $password.'jfjdlfs820913Ajd');
				$created = new DateTime();
				$created = $created->format('Y-m-d H:i:s');

				$insert_stmt = $pdo->prepare("INSERT INTO users (username,email,passwd,created_at) VALUES (:name,:email,:password,:created)");

				if(
					$insert_stmt->execute(
						[
							':name' => $name,
							':email' => $email,
							':password' => $hashed_password,
							':created' => $created
						]
					)
				)
				{ 	
					$_SESSION['user']['username'] = $name;
					$_SESSION['user']['email'] = $email;
					$_SESSION['user']['id'] = $pdo->lastInsertId();
					
					header("location: welcome.php");
				}
					
			}
		}
		catch(PDOEXCEPTION $err){
			$pdoError = $err->getMessage();

		}

		
	}
}

?>

<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-U1DAWAznBHeqEIlVSCgzq+c9gqGAJn5c/t99JyeKa9xxaYpSvHU5awsuZVVFIhvj" crossorigin="anonymous"></script>
	<title>Register</title>
</head>

<body>
	<div class="container">
		
		<form action="register.php" method="post">
			<div class="mb-3">

				<label for="name" class="form-label">Name</label>
				<input type="text" name="name" class="form-control" placeholder="Your Name">
				<?php

					if(isset($errormsg[0])){
						foreach($errormsg[0] as $nameer){
							echo "<p class='small text-danger'>".$nameer."</p";
						}
					}
				?>

			</div>

			<div class="mb-3">

				<label for="email" class="form-label">Email address</label>
				<input type="email" name="email" class="form-control" placeholder="email@example.com">

				<?php
				if(isset($errormsg[1])){
					foreach($errormsg[1] as $emailer){
						echo "<p class='small text-danger'>".$emailer."</p";
					}
				}
				?>

			</div>

			<div class="mb-3">

				<label for="password" class="form-label">Password</label>
				<input type="password" name="password" class="form-control" placeholder="">

				<?php
				if(isset($errormsg[2])){
					foreach($errormsg[2] as $passwder){
						echo "<p class='small text-danger'>".$passwder."</p>";
					}
				}
				?>
						
			</div>
			<button type="submit" name="register_btn" class="btn btn-primary">Register Account</button>
		</form>
		Already Have an Account? <a class="register" href="index.php">Login Instead</a>
	</div>
</body>

</html>