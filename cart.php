<?php
session_start();
include("connect.php");

// Function to get correct image path
function cartImagePath($imageRaw) {
    $raw = trim($imageRaw);
    if ($raw === '') {
        return 'skaff1.jpg';
    }
    $candidates = array(
        'admin/uploads/products/' . ltrim($raw, '/\\'),
        'admin/uploads/' . ltrim($raw, '/\\'),
        'uploads/products/' . ltrim($raw, '/\\'),
        'uploads/' . ltrim($raw, '/\\'),
        $raw
    );
    foreach ($candidates as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    return 'skaff1.jpg';
}

// Handle Update Cart Quantity
if (isset($_POST['update_cart']) && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $cart_id => $qty) {
        $cart_id = intval($cart_id);
        $qty = intval($qty);
        
        if ($cart_id <= 0) {
            continue;
        }
        
        if ($qty <= 0) {
            // Remove item from cart
            $delete = $con->prepare("DELETE FROM cart WHERE id = ?");
            $delete->bind_param("i", $cart_id);
            $delete->execute();
        } else {
            // Update quantity
            $update = $con->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $update->bind_param("ii", $qty, $cart_id);
            $update->execute();
        }
    }
    header("Location: cart.php");
    exit;
}

// Handle Remove Item
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $delete = $con->prepare("DELETE FROM cart WHERE id = ?");
    $delete->bind_param("i", $id);
    $delete->execute();
    header("Location: cart.php");
    exit;
}

// Fetch cart items from database
$cart_items = array();
$grandTotal = 0;

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$sql = "SELECT cart.id, cart.product_id, cart.quantity, p.name, p.price, p.image FROM cart JOIN products p ON cart.product_id = p.id WHERE cart.user_id = ? ORDER BY cart.id DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $lineTotal = floatval($row['price']) * intval($row['quantity']);
        $grandTotal += $lineTotal;
        $cart_items[] = array(
            'id' => intval($row['id']),
            'product_id' => intval($row['product_id']),
            'name' => $row['name'],
            'price' => floatval($row['price']),
            'image' => $row['image'],
            'quantity' => intval($row['quantity']),
            'line_total' => $lineTotal
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart - Haya Collection</title>
<link rel="icon" type="image/x-icon" href="/php%20e-com/favicon.ico">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body{background:#f8f3f4;font-family:Arial,sans-serif}
.wrap{max-width:1100px;margin:30px auto}
.cardx{background:#fff;border-radius:16px;box-shadow:0 10px 24px rgba(122,62,62,.1);padding:20px}
.title{color:#7a3e3e;font-weight:700}
.item-img{width:80px;height:80px;border-radius:10px;object-fit:cover}
.btn-main{background:#7a3e3e;border-color:#7a3e3e;color:#fff}
.btn-main:hover{background:#623232;border-color:#623232;color:#fff}
.btn-outline-danger{border-color:#dc3545;color:#dc3545}
.btn-outline-danger:hover{background:#dc3545;color:#fff}

/* Cart Table Styling */
.cart-table { width: 100%; border-collapse: collapse; }
.cart-table th { 
    background: #f8ecef; 
    color: #7a3e3e; 
    padding: 15px; 
    text-align: left; 
    font-weight: 600;
}
.cart-table td { 
    padding: 15px; 
    vertical-align: middle; 
    border-bottom: 1px solid #eee;
}
.cart-table tr:hover { background: #fafafa; }

/* Product Info in Cart */
.cart-product-info {
    display: flex;
    align-items: center;
    gap: 15px;
}
.cart-product-info img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}
.cart-product-name {
    font-weight: 600;
    color: #333;
    font-size: 16px;
}

/* Quantity Input */
.qty-input {
    width: 70px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
    text-align: center;
}

/* Remove Button */
.remove-link {
    color: #dc3545;
    text-decoration: none;
    font-size: 20px;
    transition: transform 0.3s ease;
}
.remove-link:hover {
    transform: scale(1.2);
}

.cart-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.selected-summary {
    background: #e8f5e8;
    border: 2px solid #28a745;
    border-radius: 10px;
    padding: 15px;
}

#proceedBtn:disabled {
    background: #ccc;
    border-color: #ccc;
    cursor: not-allowed;
}


/* Empty Cart */
.empty-cart {
    text-align: center;
    padding: 60px 20px;
}
.empty-cart-icon {
    font-size: 60px;
    margin-bottom: 20px;
}
.empty-cart h3 {
    color: #7a3e3e;
    margin-bottom: 15px;
}

/* Cart Summary */
.cart-summary {
    background: #f8ecef;
    padding: 20px;
    border-radius: 10px;
    margin-top: 20px;
}
.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #e0d0d2;
}
.summary-row:last-child {
    border-bottom: none;
    font-size: 18px;
    font-weight: 700;
    color: #7a3e3e;
}
</style>
<link rel="stylesheet" href="responsive.css">
</head>
<body>
<div class="wrap container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="title mb-0">Shopping Cart</h3>
        <a href="shoppage2.php" class="btn btn-outline-secondary btn-sm">Continue Shopping</a>
    </div>

    <div class="cardx">
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h3>Your cart is empty!</h3>
                <p style="color: #666; margin-bottom: 20px;">Looks like you haven't added anything to your cart yet.</p>
                <a href="shoppage2.php" class="btn btn-main">Shop Now</a>
            </div>
        <?php else: ?>
            <form method="post" action="cart.php">
                <div class="table-responsive">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Remove</th>
                            </tr>

                        </thead>
                        <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="cart-checkbox" name="selected_items[]" value="<?php echo $item['product_id']; ?>" 
                                           data-price="<?php echo $item['price']; ?>" data-qty="<?php echo $item['quantity']; ?>" 
                                           data-total="<?php echo $item['line_total']; ?>">
                                </td>
                                <td>
                                    <div class="cart-product-info">
                                        <img src="<?php echo htmlspecialchars(cartImagePath($item['image'])); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <span class="cart-product-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    </div>
                                </td>

                                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <input type="number" min="1" class="qty-input" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo intval($item['quantity']); ?>">
                                </td>
                                <td>₹<?php echo number_format($item['line_total'], 2); ?></td>
                                <td>
                                    <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="remove-link" title="Remove Item">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <button type="submit" name="update_cart" class="btn btn-outline-secondary">
                        <i class="fas fa-sync-alt"></i> Update Cart
                    </button>
                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>All Items Subtotal:</span>
                            <span>₹<?php echo number_format($grandTotal, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span>Free</span>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Selected Items Checkout Form -->
            <form method="POST" action="checkout.php" id="checkoutForm">
                <div class="selected-summary mt-4" id="selectedSummary" style="display: none;">
                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Selected Items (<span id="selectedCount">0</span>):</span>
                            <span id="selectedSubtotal">₹0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span>Free</span>
                        </div>
                        <div class="summary-row" style="font-weight: bold; font-size: 18px;">
                            <span>Total:</span>
                            <span id="selectedTotal">₹0.00</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-primary" id="selectAllBtn">Select All</button>
                    <button type="submit" name="proceed_selected" class="btn btn-main btn-lg" id="proceedBtn" disabled>
                        Proceed to Checkout
                    </button>
                </div>
            </form>

        <?php endif; ?>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.cart-checkbox');
    const selectedSummary = document.getElementById('selectedSummary');
    const selectedCountEl = document.getElementById('selectedCount');
    const selectedSubtotalEl = document.getElementById('selectedSubtotal');
    const selectedTotalEl = document.getElementById('selectedTotal');
    const proceedBtn = document.getElementById('proceedBtn');
const selectAllBtn = document.getElementById('selectAllBtn');
    const checkoutForm = document.getElementById('checkoutForm');

    let selectedTotal = 0;
    let selectedCount = 0;

    // Checkbox change handler
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelected);
    });

    function updateSelected() {
        selectedTotal = 0;
        selectedCount = 0;

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selectedTotal += parseFloat(checkbox.dataset.total);
                selectedCount += parseInt(checkbox.dataset.qty);
            }
        });

        // Update displays
        selectedCountEl.textContent = selectedCount;
        selectedSubtotalEl.textContent = '₹' + selectedTotal.toFixed(2);
        selectedTotalEl.textContent = '₹' + selectedTotal.toFixed(2);

        // Show/hide summary and enable button
        if (selectedCount > 0) {
            selectedSummary.style.display = 'block';
            proceedBtn.disabled = false;
        } else {
            selectedSummary.style.display = 'none';
            proceedBtn.disabled = true;
        }
    }

    // Select All button
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = true);
            updateSelected();
        });
    }

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const checkedBoxes = document.querySelectorAll('.cart-checkbox:checked');
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('Please select at least one product to proceed to checkout.');
                return false;
            }
        });
    }
});

</script>

<?php include 'includes/global_footer.php'; ?>

</body>
</html>



