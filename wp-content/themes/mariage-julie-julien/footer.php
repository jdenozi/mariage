<?php
$footer_names = get_theme_mod('footer_names', 'Julie & Julien');
$footer_date  = get_theme_mod('footer_date', '8 mai 2027');
?>
<footer class="site-footer">
    <p class="footer-names"><?php echo esc_html($footer_names); ?></p>
    <p><?php echo esc_html($footer_date); ?></p>
</footer>

<?php
// Decorations de la page actuelle
$page_id = get_queried_object_id();
if (is_front_page()) {
    $page_id = get_option('page_on_front');
}
$decorations = get_post_meta($page_id, '_mariage_decorations', true);
if (!empty($decorations) && is_array($decorations)):
?>
<div class="site-decorations" aria-hidden="true">
    <?php foreach ($decorations as $deco):
        if (empty($deco['image'])) continue;
        $style = sprintf(
            'position:absolute;left:%s%%;top:%s%%;width:%dpx;height:auto;opacity:%s;z-index:%d;pointer-events:none;',
            esc_attr($deco['left'] ?? 10),
            esc_attr($deco['top'] ?? 10),
            absint($deco['size'] ?? 150),
            (absint($deco['opacity'] ?? 100) / 100),
            intval($deco['zindex'] ?? 1)
        );
    ?>
        <img src="<?php echo esc_url($deco['image']); ?>" alt="" style="<?php echo $style; ?>">
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
