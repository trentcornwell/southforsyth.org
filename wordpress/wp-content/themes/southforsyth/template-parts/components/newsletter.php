<?php

/**
 * Newsletter signup component.
 * Visual only for now — no email provider is connected yet, so the
 * fields are marked disabled rather than pretending to submit anywhere.
 * TODO: once a provider (e.g. Mailchimp, Buttondown) is chosen, replace
 * the disabled attributes and add a real form action/handler here.
 */
?>
<section class="section section--accent" id="newsletter">
    <div class="container">
        <div class="newsletter">
            <div>
                <p class="eyebrow">Stay connected</p>
                <h2>Get the South Forsyth Weekly</h2>
                <p>Weekend events, local guides, new restaurants, family activities, and community updates.</p>
            </div>
            <div>
                <form class="newsletter__form" aria-describedby="newsletter-note">
                    <label class="visually-hidden" for="newsletter-email">Email address</label>
                    <input id="newsletter-email" type="email" placeholder="Enter your email" disabled>
                    <button class="btn btn-primary" type="submit" disabled>Subscribe</button>
                </form>
                <p id="newsletter-note" class="newsletter__note">Signup coming soon.</p>
            </div>
        </div>
    </div>
</section>
