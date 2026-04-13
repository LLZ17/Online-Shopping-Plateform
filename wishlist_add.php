/* Still making (for add wishlist)
// 2. 检查是否已经收藏过，防止重复添加
$stmt_check = $pdo->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt_check->execute([$user_id, $product_id]);

if (!$stmt_check->fetch()) {
    // 3. 没收藏过就插入数据。ID 会自动递增，所以不需要写它
    $stmt_add = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt_add->execute([$user_id, $product_id]);
    
    echo "<script>alert('Product added to Wishlist!'); window.history.back();</script>";
} else {
    echo "<script>alert('This item is already in your Wishlist!'); window.history.back();</script>";
}
?>
*/