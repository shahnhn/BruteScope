<?php
    include("./config.php");

    try{
        $sql= "SELECT * FROM login_attempts ORDER BY created_at DESC";
        $result=$conn->query($sql);
        $attempts=[];
        if($result && $result->num_rows>0){
            while($row=$result->fetch_assoc()){
                $attempts[]=$row;
            }
        }
    } catch (Exception $e){
        die("Database error:" . $e->getMessage());
    }
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BruteScope - Login Attempts Log</title>
    <link rel="stylesheet" href="assets/logview.css"/>
</head>
<body>
    <div class="log-container">
        <h1>🛡 BruteScope Logs</h1>
        <p class="subtitle">All login attempts captured</p>
        <table class="log-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>IP Address</th>
                    <th>Status</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($attempts)): ?>
                    <?php foreach($attempts as $attempt): ?>
                        <tr class="<?php echo ($attempt['status']=='success') ? 'success-row' : 'fail-row'; ?>">
                            <td><?php echo htmlspecialchars($attempt['id']); ?></td>
                            <td><?php echo htmlspecialchars($attempt['username']); ?></td>
                            <td><?php echo htmlspecialchars($attempt['password']); ?></td>
                            <td><?php echo htmlspecialchars($attempt['ip_address']); ?></td>
                            <td><?php echo htmlspecialchars($attempt['status']); ?></td>
                            <td><?php echo htmlspecialchars($attempt['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No attempts logged yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="index.php" class="btn back-btn">Back to Login</a>
    </div>
</body>
</html>