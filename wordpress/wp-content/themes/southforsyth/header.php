<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <?php get_template_part('template-parts/header/site-header'); ?>

    <?php if (! is_front_page()) : ?>
        <div class="container">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <ol class="breadcrumbs__list">
                    <li class="breadcrumbs__item"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
                    <?php if (is_single()) : ?>
                        <li class="breadcrumbs__item"><span><?php the_title(); ?></span></li>
                    <?php elseif (is_page()) : ?>
                        <li class="breadcrumbs__item"><span><?php the_title(); ?></span></li>
                    <?php endif; ?>
                </ol>
            </nav>
        </div>
    <?php endif; ?>