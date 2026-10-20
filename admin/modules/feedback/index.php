<?php
$pageTitle = 'Feedback';
$currentPage = 'feedback';

require_once '../../includes/header.php';
?>

<div class="page-breadcrumb">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Feedback</li>
        </ol>
    </nav>
</div>

<?php
// Database connection using connect.php
require_once '../../../connect.php';

// Handle delete
$deleteMessage = '';
$deleteType = '';
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $con->prepare("DELETE FROM feedback WHERE id = ?");
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

// Fetch feedback
$feedbackList = array();
$sql = "SELECT * FROM feedback ORDER BY created_at DESC";
$result = mysqli_query($con, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $feedbackList[] = $row;
    }
}
mysqli_close($con);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="mb-0">All Feedback <span class="badge bg-primary ms-2"><?php echo count($feedbackList); ?></span></h2>
        <a href="../dashboard.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
    
    <?php if ($deleteMessage): ?>
        <div class="alert alert-<?php echo $deleteType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($deleteMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="card-body p-0">
        <?php if (empty($feedbackList)): ?>
            <div class="text-center py-5">
                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No Feedback Yet</h4>
                <p class="text-muted">Customer feedback will appear here once submitted.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Rating</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbackList as $feedback): ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo $feedback['id']; ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($feedback['name']); ?></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($feedback['email']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($feedback['email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= $feedback['rating'] ? '' : ' far'; ?> text-warning"></i>
                                    <?php endfor; ?>
                                    <span class="text-muted small ms-1">(<?php echo $feedback['rating']; ?>/5)</span>
                                </td>
                                <td class="text-truncate" style="max-width: 300px;">
                                    <?php echo nl2br(htmlspecialchars(substr($feedback['message'], 0, 100))); ?>
                                    <?php if (strlen($feedback['message']) > 100): ?>...<?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($feedback['created_at'])); ?></small>
                                </td>
                                <td>
                                    <a href="?action=delete&id=<?php echo $feedback['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Delete this feedback?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

