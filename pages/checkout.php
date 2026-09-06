<?php

require __DIR__ . '/includes/session.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/partials/asset-placeholder.php';

if (!$is_logged_in) {
    header('Location: signin_signup.php?redirect=' . urlencode('checkout.php'));
    exit;
}

function fetch_cart(PDO $pdo, int $user_id): array {
    $stmt = $pdo->prepare(
        'SELECT ci.id AS cart_item_id, ci.quantity, p.id AS product_id, p.name, p.price, p.image_path
         FROM cart_items ci
         JOIN products p ON p.id = ci.product_id
         WHERE ci.user_id = ?
         ORDER BY ci.added_at DESC'
    );
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

$order_placed = false;
$order_id = null;
$order_total = 0.0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $cart_items = fetch_cart($pdo, $user_id);

    if (!empty($cart_items)) {
        $total = 0.0;
        foreach ($cart_items as $item) {
            $total += (float) $item['price'] * (int) $item['quantity'];
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('INSERT INTO orders (user_id, total, status) VALUES (?, ?, ?)');
            $stmt->execute([$user_id, $total, 'pending']);
            $new_order_id = (int) $pdo->lastInsertId();

            $item_stmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)'
            );
            foreach ($cart_items as $item) {
                $item_stmt->execute([$new_order_id, $item['product_id'], $item['quantity'], $item['price']]);
            }

            $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?')->execute([$user_id]);

            $pdo->commit();

            $order_placed = true;
            $order_id = $new_order_id;
            $order_total = $total;
        } catch (Exception $e) {
            $pdo->rollBack();
            // In production, log $e->getMessage() instead of exposing it.
            $checkout_error = 'Something went wrong placing your order. Please try again.';
        }
    }
}

$cart_items = $order_placed ? [] : fetch_cart($pdo, $user_id);
$subtotal = 0.0;
foreach ($cart_items as $item) {
    $subtotal += (float) $item['price'] * (int) $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Miguel Gutib — Checkout</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=EB+Garamond:ital,wght@0,400;0,500;1,400&family=Special+Elite&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/style.css">
</head>
<body class="bg-mg-bg">

<div class="max-w-[1400px] mx-auto px-5 md:px-10">

    <header class="bg-mg-panel border-b border-mg-line -mx-5 md:-mx-10 px-5 md:px-10">
        <div class="flex items-center justify-between py-5">
            <nav class="flex items-center gap-8 font-display text-mg-gold text-sm tracking-wide">
                <a href="home.php" class="hover:text-white transition-colors">HOME</a>
                <a href="cart.php" class="hover:text-white transition-colors">CART</a>
            </nav>
            <a href="home.php" class="flex flex-col items-center gap-1 shrink-0">
                <?php asset_placeholder('Miguel Gutib falcon-wings logo mark', '16/9', 'w-24 h-10'); ?>
                <span class="font-display text-mg-gold text-xs tracking-[.3em] border border-mg-gold-line px-3 py-1">MIGUEL • GUTIB</span>
            </a>
            <div class="flex items-center gap-4 text-mg-gold font-display text-sm">
                <span class="hidden lg:inline"><?php echo htmlspecialchars($account_name); ?></span>
                <a href="logout.php" class="text-mg-gold-soft text-xs underline underline-offset-4 hover:text-white transition-colors">Log out</a>
            </div>
        </div>
    </header>

    <section class="mt-8 mb-16 rounded-3xl border border-mg-line bg-mg-panel p-6 md:p-10">

        <?php if ($order_placed): ?>

            <h1 class="font-display text-mg-gold text-2xl md:text-3xl mb-4">Thank you, <?php echo htmlspecialchars($account_name); ?>.</h1>
            <p class="text-mg-gold-soft mb-2">Your order #<?php echo (int) $order_id; ?> has been placed.</p>
            <p class="text-mg-gold-soft mb-8">Order total: $ <?php echo number_format($order_total, 2); ?></p>
            <a href="home.php" class="inline-block bg-[var(--mg-btn)] text-mg-cream-ink font-display text-sm px-6 py-3 rounded-full border border-mg-gold-line">Back to Home</a>

        <?php elseif (empty($cart_items)): ?>

            <h1 class="font-display text-mg-gold text-2xl md:text-3xl mb-4">Checkout</h1>
            <p class="text-mg-gold-soft mb-6">Your cart is empty, so there's nothing to check out yet.</p>
            <a href="home.php" class="inline-block bg-[var(--mg-btn)] text-mg-cream-ink font-display text-sm px-6 py-3 rounded-full border border-mg-gold-line">Continue Shopping</a>

        <?php else: ?>

            <h1 class="font-display text-mg-gold text-2xl md:text-3xl mb-8">Checkout</h1>

            <?php if (!empty($checkout_error)): ?>
                <div class="mb-6 rounded-lg border border-mg-gold-line bg-black/30 px-4 py-3">
                    <p class="text-mg-gold text-sm"><?php echo htmlspecialchars($checkout_error); ?></p>
                </div>
            <?php endif; ?>

            <div class="space-y-4 mb-8">
                <?php foreach ($cart_items as $item): ?>
                <div class="flex items-center justify-between border-b border-mg-line pb-4">
                    <div>
                        <p class="font-display text-mg-gold"><?php echo htmlspecialchars($item['name']); ?></p>
                        <p class="text-mg-gold-soft text-sm">Qty <?php echo (int) $item['quantity']; ?> × $ <?php echo number_format((float) $item['price'], 2); ?></p>
                    </div>
                    <p class="font-display text-mg-gold">$ <?php echo number_format((float) $item['price'] * (int) $item['quantity'], 2); ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center justify-between mb-8">
                <p class="font-display text-mg-gold text-lg">Total</p>
                <p class="font-display text-mg-gold text-2xl">$ <?php echo number_format($subtotal, 2); ?></p>
            </div>

            <form method="post" action="checkout.php">
                <button type="submit" name="place_order"
                    class="w-full bg-[var(--mg-btn)] text-mg-cream-ink font-display tracking-wide py-3 rounded-full border border-mg-gold-line hover:opacity-90 transition-opacity">
                    PLACE ORDER
                </button>
            </form>
            <p class="text-mg-gold-soft text-xs mt-4 text-center">
                This records your order — it does not process a real payment yet.
            </p>

        <?php endif; ?>
    </section>
</div>
</body>
</html>
