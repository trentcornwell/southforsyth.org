<?php

/**
 * Generic card grid partial for homepage sections.
 * Expected variables: $title, $intro, $cards, $id.
 */

if (! isset($cards) || empty($cards)) {
    return;
}
?>
<section class="section" <?php echo $id ? ' id="' . esc_attr($id) . '"' : ''; ?>>
    <div class="container">
        <div class="section-heading">
            <?php if (! empty($title)) : ?>
                <h2><?php echo esc_html($title); ?></h2>
            <?php endif; ?>
            <?php if (! empty($intro)) : ?>
                <p><?php echo esc_html($intro); ?></p>
            <?php endif; ?>
        </div>
        <div class="card-grid">
            <?php foreach ($cards as $card) : ?>
                <article class="card">
                    <div class="card__body">
                        <p class="eyebrow"><?php echo esc_html($card['eyebrow'] ?? 'Local guide'); ?></p>
                        <h3><?php echo esc_html($card['title'] ?? ''); ?></h3>
                        <p><?php echo esc_html($card['description'] ?? ''); ?></p>
                        <?php if (! empty($card['link'])) : ?>
                            <a class="text-link" href="<?php echo esc_url($card['link']); ?>">Learn more</a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>