<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: ../loginform.php");
    exit;
}

$conn = mysqli_connect("localhost", "root", "", "23bca090");

if (!$conn) {
    die("Database connection failed");
}

$conn->set_charset("utf8mb4");

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $deleteMessage = "Feedback deleted successfully";
        $deleteType = "success";
    } else {
        $deleteMessage = "Error deleting feedback";
        $deleteType = "error";
    }
    $stmt->close();
}

// Fetch all feedback
$feedbackList = array();
$sql = "SELECT * FROM feedback ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $feedbackList[] = $row;
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feedback - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .header {
            background: linear-gradient(135deg, #823c49 0%, #a64d5c 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
        }
        
        .header a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            font-size: 14px;
        }
        
        .header a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .card-header h2 {
            color: #333;
            font-size: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        tr:hover {
            background: #fafafa;
        }
        
        .star-rating {
            color: #ffc107;
            font-size: 16px;
        }
        
        .message-cell {
            max-width: 300px;
            line-height: 1.5;
        }
        
        .date-cell {
            white-space: nowrap;
            color: #666;
            font-size: 13px;
        }
        
        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .delete-btn:hover {
            background: #c82333;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h1><i class="fas fa-comments"></i> Manage Feedback</h1>
    <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>

<div class="container">
    <?php if (isset($deleteMessage)): ?>
        <div class="message <?php echo $deleteType; ?>">
            <?php echo $deleteMessage; ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h2>All Feedback (<?php echo count($feedbackList); ?>)</h2>
        </div>
        
        <?php if (empty($feedbackList)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Feedback Yet</h3>
                <p>When users submit feedback, it will appear here.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Rating</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedbackList as $feedback): ?>
                        <tr>
                            <td>#<?php echo intval($feedback['id']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['name']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['email']); ?></td>
                            <td>
                                <span class="star-rating">
                                    <?php
                                    $rating = intval($feedback['rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </span>
                            </td>
                            <td class="message-cell"><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></td>
                            <td class="date-cell"><?php echo date('d M Y, h:i A', strtotime($feedback['created_at'])); ?></td>
                            <td>
                                <a href="?action=delete&id=<?php echo intval($feedback['id']); ?>" 
                                   class="delete-btn" 
                                   onclick="return confirm('Are you sure you want to delete this feedback?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

