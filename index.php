<?php
    include("./config.php");

    $correct_username="admin";
    $correct_password="admin123";

    if($_SERVER['REQUEST_METHOD']==='POST'){
        $username=trim($_POST['username'] ?? '');
        $password=trim($_POST['password'] ?? '');
        $status=($username==$correct_username && $password==$correct_password) ? 'success' : 'fail';
        $ip_address=$_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $sql=$conn->prepare("INSERT INTO login_attempts (username, password, ip_address, status)
                                VALUES (?,?,?,?)");
        if($sql){
            $sql->bind_param('ssss',$username, $password, $ip_address, $status);
            $sql->execute();
            $sql->close();
        }else{
            die("Database error: ".$conn->error());
        }
        if($status=='success'){
            $message="Login successful! Welcome, {$username}";
            header("Location: dashboard.php");
            exit();
        }
        else{
            $message="Invalid credentials. Please try again.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BruteScope - Login</title>
    <link rel="stylesheet" href="assets/login.css"/>
</head>
<body>
    <div class="login-container">
        <h1>🛡 BruteScope</h1>
        <p class="subtitle">Visualize brute-force attacks in real time</p>
        <?php if(!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
                
        <form action="index.php" method="post">
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required autocomplete="off"><br>
            </div>       
            <div class="input-group"> 
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required autocomplete="off"><br>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
    