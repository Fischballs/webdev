<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/partials/asset-placeholder.php';

// Where to send the visitor after a successful login/signup. Restricted to
// a plain local filename (no scheme, no "//") to avoid an open redirect.
$redirect_target = $_GET['redirect'] ?? $_POST['redirect'] ?? 'home.php';
if (!preg_match('/^[A-Za-z0-9_\-\.]+\.php$/', $redirect_target)) {
    $redirect_target = 'home.php';
}

// Already logged in? Don't show the form again — just continue on.
if ($is_logged_in) {
    header('Location: ' . $redirect_target);
    exit;
}

$errors = [];
$active_tab = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['login_submit'])) {
        $active_tab = 'login';
        $email    = trim($_POST['login_email'] ?? '');
        $password = trim($_POST['login_password'] ?? '');

        if ($email === '' || $password === '') {
            $errors[] = 'Please enter both your email and password.';
        } else {
            $stmt = $pdo->prepare('SELECT id, full_name, password_hash FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true); // new session id on privilege change
                $_SESSION['user_id']      = $user['id'];
                $_SESSION['account_name'] = $user['full_name'];
                header('Location: ' . $redirect_target);
                exit;
            }
            $errors[] = 'Incorrect email or password.';
        }

    } elseif (isset($_POST['signup_submit'])) {
        $active_tab = 'signup';
        $full_name = trim($_POST['signup_name'] ?? '');
        $email     = trim($_POST['signup_email'] ?? '');
        $password  = trim($_POST['signup_password'] ?? '');
        $confirm   = trim($_POST['signup_confirm'] ?? '');

        if ($full_name === '' || $email === '' || $password === '' || $confirm === '') {
            $errors[] = 'Please fill in all fields to create your account.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Your password should be at least 6 characters.';
        } elseif ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $errors[] = 'An account with that email already exists. Try logging in instead.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)');
                $stmt->execute([$full_name, $email, $hash]);

                session_regenerate_id(true);
                $_SESSION['user_id']      = (int) $pdo->lastInsertId();
                $_SESSION['account_name'] = $full_name;
                header('Location: ' . $redirect_target);
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Miguel Gutib — Log In or Sign Up</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=EB+Garamond:ital,wght@0,400;0,500;1,400&family=Special+Elite&display=swap" rel="stylesheet">

<!-- Same brand stylesheet as home.php -->
<link rel="stylesheet" href="styles/style.css">
</head>
<body class="bg-mg-bg">

<div class="max-w-[1400px] mx-auto px-5 md:px-10">

    <!-- =========================================================
         HEADER — simplified: logo only, links back to the homepage
    ========================================================== -->
    <header class="bg-mg-panel border-b border-mg-line -mx-5 md:-mx-10 px-5 md:px-10">
        <div class="flex items-center justify-center py-5">
            <a href="home.php" class="flex flex-col items-center gap-1">
                <?php asset_placeholder('Miguel Gutib falcon-wings logo mark', '16/9', 'w-24 h-10'); ?>
                <span class="font-display text-mg-gold text-xs tracking-[.3em] border border-mg-gold-line px-3 py-1">MIGUEL • GUTIB</span>
            </a>
        </div>
    </header>

    <!-- =========================================================
         LOGIN / SIGN UP CARD
    ========================================================== -->
    <section class="flex justify-center mt-10 md:mt-16 mb-16">
        <div class="w-full max-w-md rounded-3xl border border-mg-line bg-mg-panel p-6 md:p-10">

            <!-- tabs -->
            <div class="grid grid-cols-2 gap-2 mb-8" role="tablist">
                <button type="button" data-tab="login"
                    class="mg-tab-btn font-display text-sm tracking-wide py-3 rounded-full border transition-colors <?php echo $active_tab === 'login' ? 'bg-[var(--mg-btn)] text-mg-cream-ink border-mg-gold-line' : 'text-mg-gold-soft border-mg-line'; ?>">
                    LOG IN
                </button>
                <button type="button" data-tab="signup"
                    class="mg-tab-btn font-display text-sm tracking-wide py-3 rounded-full border transition-colors <?php echo $active_tab === 'signup' ? 'bg-[var(--mg-btn)] text-mg-cream-ink border-mg-gold-line' : 'text-mg-gold-soft border-mg-line'; ?>">
                    SIGN UP
                </button>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="mb-6 rounded-lg border border-mg-gold-line bg-black/30 px-4 py-3">
                <?php foreach ($errors as $error): ?>
                    <p class="text-mg-gold text-sm"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- LOG IN FORM -->
            <form method="post" action="signin_signup.php" data-panel="login" class="mg-tab-panel <?php echo $active_tab === 'login' ? '' : 'hidden'; ?> space-y-5">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_target); ?>">

                <div>
                    <label class="block text-mg-gold-soft text-sm mb-2" for="login_email">Email</label>
                    <input id="login_email" name="login_email" type="email" autocomplete="email"
                        class="w-full bg-transparent border border-mg-line focus:border-mg-gold-line outline-none rounded-lg px-4 py-3 text-mg-gold-soft placeholder:text-mg-gold-soft/40"
                        placeholder="you@example.com">
                </div>

                <div>
                    <label class="block text-mg-gold-soft text-sm mb-2" for="login_password">Password</label>
                    <input id="login_password" name="login_password" type="password" autocomplete="current-password"
                        class="w-full bg-transparent border border-mg-line focus:border-mg-gold-line outline-none rounded-lg px-4 py-3 text-mg-gold-soft placeholder:text-mg-gold-soft/40"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-mg-gold-soft">
                        <input type="checkbox" name="remember" class="accent-[var(--mg-gold-line)]">
                        Remember me
                    </label>
                    <a href="#" class="text-mg-gold-soft underline underline-offset-4 hover:text-mg-gold">Forgot password?</a>
                </div>

                <button type="submit" name="login_submit" value="1"
                    class="w-full bg-[var(--mg-btn)] text-mg-cream-ink font-display tracking-wide py-3 rounded-full border border-mg-gold-line hover:opacity-90 transition-opacity">
                    LOG IN
                </button>

                <p class="text-center text-mg-gold-soft text-sm">
                    New to Miguel Gutib?
                    <button type="button" data-tab-link="signup" class="text-mg-gold underline underline-offset-4">Create an account</button>
                </p>
            </form>

            <!-- SIGN UP FORM -->
            <form method="post" action="signin_signup.php" data-panel="signup" class="mg-tab-panel <?php echo $active_tab === 'signup' ? '' : 'hidden'; ?> space-y-5">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_target); ?>">

                <div>
                    <label class="block text-mg-gold-soft text-sm mb-2" for="signup_name">Full Name</label>
                    <input id="signup_name" name="signup_name" type="text" autocomplete="name"
                        class="w-full bg-transparent border border-mg-line focus:border-mg-gold-line outline-none rounded-lg px-4 py-3 text-mg-gold-soft placeholder:text-mg-gold-soft/40"
                        placeholder="e.g. Juan Dela Cruz">
                </div>

                <div>
                    <label class="block text-mg-gold-soft text-sm mb-2" for="signup_email">Email</label>
                    <input id="signup_email" name="signup_email" type="email" autocomplete="email"
                        class="w-full bg-transparent border border-mg-line focus:border-mg-gold-line outline-none rounded-lg px-4 py-3 text-mg-gold-soft placeholder:text-mg-gold-soft/40"
                        placeholder="you@example.com">
                </div>

                <div>
                    <label class="block text-mg-gold-soft text-sm mb-2" for="signup_password">Password</label>
                    <input id="signup_password" name="signup_password" type="password" autocomplete="new-password"
                        class="w-full bg-transparent border border-mg-line focus:border-mg-gold-line outline-none rounded-lg px-4 py-3 text-mg-gold-soft placeholder:text-mg-gold-soft/40"
                        placeholder="At least 6 characters">
                </div>

                <div>
                    <label class="block text-mg-gold-soft text-sm mb-2" for="signup_confirm">Confirm Password</label>
                    <input id="signup_confirm" name="signup_confirm" type="password" autocomplete="new-password"
                        class="w-full bg-transparent border border-mg-line focus:border-mg-gold-line outline-none rounded-lg px-4 py-3 text-mg-gold-soft placeholder:text-mg-gold-soft/40"
                        placeholder="Repeat your password">
                </div>

                <button type="submit" name="signup_submit" value="1"
                    class="w-full bg-[var(--mg-btn)] text-mg-cream-ink font-display tracking-wide py-3 rounded-full border border-mg-gold-line hover:opacity-90 transition-opacity">
                    CREATE ACCOUNT
                </button>

                <p class="text-center text-mg-gold-soft text-sm">
                    Already have an account?
                    <button type="button" data-tab-link="login" class="text-mg-gold underline underline-offset-4">Log in</button>
                </p>
            </form>
        </div>
    </section>
</div>

<script src="scripts/scripts.js"></script>

</body>
</html>
