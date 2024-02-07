<?php
require_once 'connection.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('location:index.php');
    exit;
}

$password_changed = false; // Variable to track if password change was successful

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate if new password and confirm password match
    if ($new_password !== $confirm_password) {
        $errormsg[] = 'New password and confirm password do not match';
    } else {
        try {
            $user_id = $_SESSION['user']['id'];
            $select_stmt = $pdo->prepare("SELECT passwd FROM users WHERE id = :id");
            $select_stmt->execute([':id' => $user_id]);
            $row = $select_stmt->fetch(PDO::FETCH_ASSOC);

            if ($select_stmt->rowCount() == 1) {
                // Verify current password
                $current_password_hashed = hash('sha512', $current_password . 'jfjdlfs820913Ajd');
                if ($current_password_hashed == $row['passwd']) {
                    // Hash the new password with salt
                    $new_password_hashed = hash('sha512', $new_password . 'jfjdlfs820913Ajd');

                    // Update password in the database
                    $update_stmt = $pdo->prepare("UPDATE users SET passwd = :password WHERE id = :id");
                    $update_stmt->execute([':password' => $new_password_hashed, ':id' => $user_id]);

                    // Set flag to indicate successful password change
                    $password_changed = true;

                    // Redirect back to the welcome page after password change
                    
                } else {
                    $errormsg[] = 'Incorrect current password';
                }
            } else {
                $errormsg[] = 'User not found';
            }
        } catch (PDOException $err) {
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
    <title>Welcome</title>
</head>

<body>
    <div class="container">
        <?php
        echo "<h1> Welcome " . $_SESSION['user']['username'] . "</h1>";
        
        ?>
        <?php
        if (!empty($errormsg)) {
            foreach ($errormsg as $error) {
                echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
            }
        }
        ?>
        <?php if ($password_changed): ?>
            <div class="alert alert-success" role="alert">
                Password changed successfully!
            </div>
        <?php endif; ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary" name="change_password">Change Password</button>
        </form>
        <a class="btn btn-primary" href="logout.php">Logout</a>
    </div>
</body>

</html>
