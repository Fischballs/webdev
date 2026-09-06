<?php
/**
 * partials/asset-placeholder.php
 *
 * Shared helper used by home.php and login_signup.php to stand in for real
 * photos/graphics. Renders a dashed-border box with an icon and a label
 * describing exactly what asset belongs there, sized to the given aspect
 * ratio so swapping in a real <img> later won't shift the layout.
 */

if (!function_exists('asset_placeholder')) {
    /**
     * @param string $label   Description of the asset that belongs here.
     * @param string $ratio   CSS aspect-ratio value, e.g. "16/9", "1/1".
     * @param string $classes Extra Tailwind classes for sizing/positioning.
     */
    function asset_placeholder(string $label, string $ratio = '', string $classes = ''): void {
        $style = $ratio !== '' ? 'style="aspect-ratio:' . htmlspecialchars($ratio) . '"' : '';
        ?>
        <div class="asset-placeholder <?php echo $classes; ?>" <?php echo $style; ?>>
            <svg class="asset-placeholder__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="2" y="4" width="20" height="16" rx="1.5" stroke="currentColor" stroke-width="1.4"/>
                <circle cx="8" cy="10" r="1.6" stroke="currentColor" stroke-width="1.4"/>
                <path d="M3 17L8.5 12.5C9.05 12.05 9.85 12.07 10.38 12.54L13 14.86" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                <path d="M11 17L15.6 13.2C16.14 12.76 16.9 12.76 17.45 13.19L21 16" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            <span class="asset-placeholder__label"><?php echo htmlspecialchars($label); ?></span>
        </div>
        <?php
    }
}
