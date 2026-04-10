<?php
$footer_names = get_theme_mod('footer_names', 'Julie & Julien');
$footer_date  = get_theme_mod('footer_date', '8 mai 2027');
?>
<footer class="site-footer">
    <p class="footer-names"><?php echo esc_html($footer_names); ?></p>
    <p><?php echo esc_html($footer_date); ?></p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
