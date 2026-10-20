$content = Get-Content 'shoppage2.php' -Raw

# Replace the category alias mapping section
$oldCode = @'
// Support both ?cat= and ?category= from links/buttons
$selectedCategoryRaw = '';
if (isset($_GET['cat'])) {
    $selectedCategoryRaw = trim($_GET['cat']);
} elseif (isset($_GET['category'])) {
    $selectedCategoryRaw = trim($_GET['category']);
}

// Normalize common category labels to DB values
$categoryAliasMap = array(
    'all' => 'all',
    'scarf' => 'Skaff',
    'skaff' => 'Skaff',
    'cap' => 'Cap',
    'pin' => 'Pin',
    'burkha' => 'Burkha',
    'burqa' => 'Burkha'
);
$categoryLookupKey = strtolower($selectedCategoryRaw);
$selectedCategory = isset($categoryAliasMap[$categoryLookupKey]) ? $categoryAliasMap[$categoryLookupKey] : $selectedCategoryRaw;
if ($selectedCategory === '') {
    $selectedCategory = 'all';
}
'@

$newCode = @'
// Get category from URL parameter - no alias mapping, use direct value
$cat = isset($_GET['cat']) ? trim($_GET['cat']) : 'all';
if ($cat === '') {
    $cat = 'all';
}
'@

$content = $content -replace [regex]::Escape($oldCode), $newCode

# Also replace the SQL query section
$oldSql = @'
if ($selectedCategory === 'all') {
    $result = $con->query("SELECT * FROM products ORDER BY id DESC");
} else {
    $stmt = $con->prepare("SELECT * FROM products WHERE LOWER(TRIM(category)) = LOWER(TRIM(?)) ORDER BY id DESC");
    $stmt->bind_param("s", $selectedCategory);
    $stmt->execute();
    $result = $stmt->get_result();
}
'@

$newSql = @'
if ($cat == 'all' || $cat == '') {
    $result = $con->query("SELECT * FROM products ORDER BY id DESC");
} else {
    $sql = "SELECT * FROM products WHERE TRIM(category) = '" . $cat . "' ORDER BY id DESC";
    $result = $con->query($sql);
}
'@

$content = $content -replace [regex]::Escape($oldSql), $newSql

# Replace the selectedCategory references in the display section
$content = $content -replace '\$selectedCategory', '$cat'

$content | Set-Content 'shoppage2.php'
Write-Host "Done!"

