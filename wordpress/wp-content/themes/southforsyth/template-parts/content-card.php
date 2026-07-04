<article class="card">
    <?php if (has_post_thumbnail()) : ?>
        <a href="<?php the_permalink(); ?>" class="card__media">
            <?php the_post_thumbnail('southforsyth-card', array('loading' => 'lazy', 'decoding' => 'async')); ?>
        </a>
    <?php endif; ?>
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html(get_the_date()); ?></p>
        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p><?php echo esc_html(southforsyth_get_excerpt()); ?></p>
    </div>
</article>