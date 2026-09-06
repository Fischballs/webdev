<?php
/**
 * home.php
 * Miguel Gutib — Homepage
 *
 * Structure, hierarchy, boxes and colors follow the MG_web_mockup.pdf design.
 * Layout/spacing uses Tailwind CSS utility classes. Brand colors live in
 * styles/style.css since they aren't part of Tailwind's default palette.
 *
 * ACCOUNT / SESSION STATE
 * -----------------------
 * includes/session.php starts the session and exposes $is_logged_in,
 * $user_id and $account_name. While logged out ("null account"), the
 * navbar shows "Example Account" and every Buy Now / Cart / Account
 * control is routed to signin_signup.php via guarded_href(). Once
 * signin_signup.php sets $_SESSION['account_name'], this page shows
 * that name instead and lets Cart/Buy Now actually add to the cart.
 *
 * PRODUCTS
 * --------
 * Pulled live from the `products` table (see database/schema.sql) instead
 * of being hardcoded, so cart/checkout can reference real product ids.
 * A product with no image_path yet renders via asset_placeholder() —
 * fill in image_path once you have the real photo and it becomes a
 * normal <img> automatically.
 */

require __DIR__ . '/includes/session.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/partials/asset-placeholder.php';

// Best Sellers row shown on the homepage (matches the mockup's active tab).
$products_stmt = $pdo->query(
    "SELECT id, name, price, image_path FROM products
     WHERE tab_group = 'best_sellers' ORDER BY id ASC LIMIT 4"
);
$products = $products_stmt->fetchAll();

// Featured Noir Chrono, used in the "New Arrival" feature section.
$featured_stmt = $pdo->query(
    "SELECT id, name, price FROM products WHERE is_featured = 1 LIMIT 1"
);
$featured = $featured_stmt->fetch() ?: null;

/**
 * Renders a product's photo: a real <img> if image_path is set in the
 * database, otherwise the asset placeholder box.
 */
function product_image(array $product, string $ratio, string $classes = ''): void {
    if (!empty($product['image_path'])) {
        printf(
            '<img src="%s" alt="%s" class="w-full h-full object-cover %s" style="aspect-ratio:%s">',
            htmlspecialchars($product['image_path']),
            htmlspecialchars($product['name']),
            htmlspecialchars($classes),
            htmlspecialchars($ratio)
        );
    } else {
        asset_placeholder('Product photo — ' . $product['name'], $ratio, $classes);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Miguel Gutib — Time, Accuracy, Integrity</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=EB+Garamond:ital,wght@0,400;0,500;1,400&family=Special+Elite&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/style.css">
</head>
<body class="bg-mg-bg">

<div class="max-w-[1400px] mx-auto px-5 md:px-10">

    <!-- =========================================================
         HEADER / NAV
    ========================================================== -->
    <header class="bg-mg-panel border-b border-mg-line -mx-5 md:-mx-10 px-5 md:px-10">
        <div class="flex items-center justify-between py-5">

            <nav class="flex items-center gap-8 font-display text-mg-gold text-sm tracking-wide">
                <a href="#" class="hover:text-white transition-colors">MENU</a>
                <a href="home.php" class="hidden sm:inline hover:text-white transition-colors">HOME</a>
                <a href="#" class="hidden sm:inline hover:text-white transition-colors">WATCHES</a>
                <a href="#" class="hidden md:inline hover:text-white transition-colors">COLLECTIONS</a>
            </nav>

            <a href="home.php" class="flex flex-col items-center gap-1 shrink-0">
                <?php asset_placeholder('Miguel Gutib falcon-wings logo mark', '16/9', 'w-24 h-10'); ?>
                <span class="font-display text-mg-gold text-xs tracking-[.3em] border border-mg-gold-line px-3 py-1">MIGUEL • GUTIB</span>
            </a>

            <div class="flex items-center gap-6">
                <label class="hidden sm:flex items-center gap-2 border border-mg-gold-line rounded-full px-4 py-2 text-mg-gold-soft">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3" stroke-linecap="round"/></svg>
                    <input type="text" placeholder="" class="bg-transparent outline-none w-32 md:w-48">
                </label>

                <a href="<?php echo htmlspecialchars(guarded_href($is_logged_in, 'cart.php')); ?>" class="hidden sm:flex items-center gap-1 text-mg-gold" title="Cart">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/><path d="M2.5 3h2l2.2 11.2a2 2 0 0 0 2 1.6h7.4a2 2 0 0 0 2-1.6L20 7.5H6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>

                <a href="<?php echo htmlspecialchars(guarded_href($is_logged_in, '#')); ?>" class="flex items-center gap-2 text-mg-gold font-display text-sm">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="3.2"/><path d="M4.5 20c1.4-3.8 4.2-5.8 7.5-5.8s6.1 2 7.5 5.8" stroke-linecap="round"/></svg>
                    <span class="hidden lg:inline"><?php echo $is_logged_in ? htmlspecialchars($account_name) : 'Example Account'; ?></span>
                </a>
                <?php if ($is_logged_in): ?>
                <a href="logout.php" class="hidden lg:inline text-mg-gold-soft text-xs underline underline-offset-4 hover:text-white transition-colors">Log out</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- =========================================================
         HERO — chamfered frame, background photo + copy overlay
    ========================================================== -->
    <section class="relative mt-6 md:mt-8">
        <div class="hero-frame relative overflow-hidden bg-mg-panel-2 min-h-[420px] md:min-h-[560px]">

            <?php asset_placeholder('Hero background — perigrine falcon in flight (blurred, dark overlay)', '', 'absolute inset-0 w-full h-full'); ?>

            <p class="font-display italic text-mg-gold text-sm md:text-lg leading-relaxed absolute top-8 right-6 md:right-12 max-w-[520px] text-right">
                The Perigrine Falcon Is Known For Their Accuracy And Strength.
                Observing On High And Swooping Their Target Off Their Feet.
            </p>

            <p class="font-display italic text-mg-gold-soft text-base md:text-xl leading-relaxed absolute bottom-8 left-6 md:left-12 max-w-[480px]">
                Miguel Gutib is a luxury watch brand that is focused on tracking
                the most expensive thing — Time. Each timepiece is not only a
                symbol, but the embodiment of Accuracy, Integrity, and
                Authentic Luxury.
            </p>
        </div>
    </section>

    <!-- =========================================================
         PRODUCT RAIL — Classic / Best Sellers / In collaboration
    ========================================================== -->
    <section class="mt-8 rounded-3xl border border-mg-line bg-mg-panel px-6 md:px-10 py-8">

        <div class="flex items-center justify-center gap-10 md:gap-20 font-display text-mg-gold text-base md:text-lg mb-8">
            <a href="#" class="hover:text-white transition-colors">Classic</a>
            <a href="#" class="text-mg-gold underline underline-offset-8 decoration-1">Best Sellers</a>
            <a href="#" class="hover:text-white transition-colors">In collaboration</a>
        </div>

        <div class="mg-scroll flex gap-6 overflow-x-auto pb-4">
            <?php foreach ($products as $p): ?>
            <article class="shrink-0 w-56 md:w-64">
                <div class="w-full aspect-square bg-neutral-200">
                    <?php product_image($p, '1/1'); ?>
                </div>
                <h3 class="font-display text-mg-gold text-center text-lg mt-4"><?php echo htmlspecialchars($p['name']); ?></h3>
                <p class="text-mg-gold-soft text-center text-sm mt-1">$ <?php echo number_format((float) $p['price'], 2); ?></p>
                <div class="flex items-center justify-center gap-3 mt-3">
                    <button class="bg-[var(--mg-btn)] text-mg-cream-ink text-xs font-display px-4 py-2 rounded-full border border-mg-gold-line">QUICK VIEW</button>
                    <?php if ($is_logged_in): ?>
                        <form method="post" action="cart.php" class="inline-block">
                            <input type="hidden" name="product_id" value="<?php echo (int) $p['id']; ?>">
                            <button type="submit" name="add_to_cart"
                                class="bg-[var(--mg-btn)] text-mg-cream-ink text-xs font-display px-5 py-2 rounded-full">CART</button>
                        </form>
                    <?php else: ?>
                        <a href="signin_signup.php" class="bg-[var(--mg-btn)] text-mg-cream-ink text-xs font-display px-5 py-2 rounded-full inline-block">CART</a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <div class="h-1.5 rounded-full bg-white/10 mt-2 overflow-hidden">
            <div class="h-full w-1/3 rounded-full" style="background:var(--mg-gold-line)"></div>
        </div>
    </section>

    <!-- =========================================================
         NEW ARRIVAL — Limited Edition Noir Chrono feature
    ========================================================== -->
    <section class="mt-8 rounded-3xl border border-mg-line bg-mg-panel p-6 md:p-10">

        <div class="flex justify-center mb-8">
            <span class="font-stamp text-white text-2xl md:text-3xl tracking-widest border-2 border-white/80 px-8 py-2">NEW ARRIVAL</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1.4fr_1fr] gap-8">

            <div class="relative rounded-xl overflow-hidden border border-white/20 min-h-[360px] md:min-h-[440px]">
                <?php asset_placeholder('Model wearing the M.G Noir Chrono — black & white lifestyle photo with blueprint/schematic watch overlay', '', 'absolute inset-0'); ?>

                <p class="font-stamp text-white text-xs md:text-sm leading-6 absolute top-6 left-6 max-w-[280px]">
                    Walk into the night with confidence wearing the limited
                    edition Noir Chrono. Featuring a full black metallic bezel
                    and a textured rubber strap for the acquisition of the
                    best aesthetics and comfort.
                </p>

                <div class="absolute bottom-0 left-0 right-0 flex items-center justify-between border-t border-white/50 bg-black/40 px-6 py-3">
                    <span class="font-stamp text-white text-sm md:text-base tracking-wide">LIMITED EDITION NOIR CHRONO</span>
                    <span class="font-stamp text-white text-sm md:text-base">$ <?php echo $featured ? number_format((float) $featured['price'], 2) : '1945.00'; ?></span>
                </div>
            </div>

            <div class="flex flex-col justify-between py-2">
                <div>
                    <p class="font-display text-mg-gold tracking-[.2em] text-sm mb-6">FEATURED</p>
                    <h3 class="font-display text-mg-gold text-xl md:text-2xl leading-snug mb-8">
                        Limited Edition<br>M.G Noir Chrono
                    </h3>
                    <p class="font-display text-mg-gold text-lg mb-10">Only 100 pcs produced</p>
                </div>

                <?php if ($is_logged_in && $featured): ?>
                    <form method="post" action="cart.php">
                        <input type="hidden" name="product_id" value="<?php echo (int) $featured['id']; ?>">
                        <button type="submit" name="add_to_cart" class="font-display italic text-mg-gold underline underline-offset-4">Buy Now</button>
                    </form>
                <?php else: ?>
                    <a href="signin_signup.php" class="font-display italic text-mg-gold underline underline-offset-4">Buy Now</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- =========================================================
         CATEGORIES
    ========================================================== -->
    <section class="mt-8 rounded-3xl border border-mg-line bg-mg-panel p-6 md:p-10">
        <h2 class="font-display text-mg-gold text-center text-2xl md:text-3xl mb-8">CATEGORIES</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <?php
            $categories = [
                ['label' => 'FIELD',    'desc' => 'Hiker overlooking a mountain valley at dusk, wearing a field watch'],
                ['label' => 'SPORTS',   'desc' => 'Diver underwater beside a rugged black sports/dive watch'],
                ['label' => 'ROMANCE',  'desc' => 'Couple silhouetted on a city street beside gold & black skeleton watches'],
            ];
            foreach ($categories as $c) {
            ?>
            <div class="relative rounded-lg overflow-hidden min-h-[220px] md:min-h-[280px]">
                <?php asset_placeholder($c['desc'], '4/5', 'absolute inset-0'); ?>
                <div class="absolute inset-0 flex items-center justify-center bg-black/25">
                    <span class="font-display text-mg-gold text-2xl md:text-3xl tracking-wide"><?php echo htmlspecialchars($c['label']); ?></span>
                </div>
            </div>
            <?php } ?>
        </div>
    </section>

    <!-- =========================================================
         NEW ARRIVAL — Militiare Chrono banner (cream/olive)
    ========================================================== -->
    <section class="mt-8 rounded-3xl border-2 border-mg-gold-line bg-mg-cream p-2 md:p-3">
        <div class="relative rounded-2xl overflow-hidden min-h-[360px] md:min-h-[480px]">

            <?php asset_placeholder('Military sniper team silhouette, green-tinted background with faint watch-face schematic', '', 'absolute inset-0'); ?>

            <span class="font-stamp text-mg-cream-ink text-xl md:text-2xl tracking-widest absolute top-6 left-1/2 -translate-x-1/2 bg-mg-cream px-6 py-1 border border-mg-gold-line">NEW ARRIVAL</span>

            <p class="font-stamp text-white/90 text-lg md:text-xl absolute bottom-24 left-6 md:left-10">M.G Militiare Chrono</p>
            <a href="#" class="font-stamp text-white underline text-sm md:text-base absolute bottom-8 left-6 md:left-10">learn More</a>

            <div class="absolute right-4 md:right-10 bottom-6 md:bottom-10 w-40 md:w-56">
                <?php asset_placeholder('M.G Militiare Chrono — olive dial field watch product shot', '1/1'); ?>
            </div>
        </div>
    </section>

    <!-- =========================================================
         OUR HISTORY
    ========================================================== -->
    <section class="mt-8 rounded-3xl border border-mg-line bg-mg-panel p-6 md:p-10">
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_1.2fr] gap-8 items-center">

            <div class="grid grid-cols-4 gap-2 h-40 md:h-56">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <?php asset_placeholder('Watch movement macro photo ' . $i, '', 'h-full'); ?>
                <?php endfor; ?>
            </div>

            <div>
                <h3 class="font-display text-mg-gold text-xl md:text-2xl mb-4">Our History:</h3>
                <p class="text-mg-gold-soft leading-relaxed mb-4">
                    Founded on the belief that time is life's most valuable
                    possession, Miguel Gutib began with a passion for
                    precision, craftsmanship, and timeless design.
                </p>
                <p class="text-mg-gold-soft leading-relaxed">
                    Today, every timepiece carries our commitment to Accuracy,
                    Integrity, and Authentic Luxury — crafted not just to tell
                    time, but to become part of your story.
                </p>
            </div>
        </div>
    </section>

    <!-- =========================================================
         FOOTER
    ========================================================== -->
    <footer class="mt-8 mb-10 border-t border-mg-gold-line pt-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-10">

            <div>
                <h4 class="font-display text-mg-gold text-lg mb-4">Contact Us</h4>
                <p class="text-mg-gold-soft leading-8">
                    Miguel Gutib<br>
                    0912 345 6789<br>
                    spoofemail@email.com
                </p>
            </div>

            <div>
                <h4 class="font-display text-mg-gold text-lg mb-4">Customer care</h4>
                <ul class="text-mg-gold-soft leading-8">
                    <li><a href="#" class="hover:text-white transition-colors">Help Center</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">How To Buy</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Shipping &amp; delivery</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-display text-mg-gold text-lg mb-4">Localization</h4>
                <ul class="text-mg-gold-soft leading-8">
                    <li><a href="#" class="hover:text-white transition-colors">Available Language</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Stores Near You</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-display text-mg-gold text-lg mb-4">Download the app</h4>
                <div class="w-28 h-28 mb-3">
                    <?php asset_placeholder('QR code linking to app download', '1/1'); ?>
                </div>
                <div class="flex gap-2">
                    <div class="w-28 h-9"><?php asset_placeholder('Google Play badge', '', ''); ?></div>
                    <div class="w-28 h-9"><?php asset_placeholder('App Store badge', '', ''); ?></div>
                </div>
            </div>
        </div>
    </footer>

</div>
</body>
</html>
