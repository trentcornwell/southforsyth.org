<?php

/**
 * "Find My Schools" address-to-school lookup.
 *
 * Server-rendered form + results container; all lookup logic happens via
 * assets/js/find-my-schools.js calling the REST endpoint in
 * inc/school-locator.php. The submit button starts disabled and JS
 * enables it (see the script) -- deliberate: this form must never
 * fall back to a plain GET/POST submission, which would put the
 * visitor's address in a URL (and likely a web server access log). If
 * JS doesn't load, the form simply doesn't submit, rather than silently
 * doing something privacy-unsafe.
 *
 * Usage: get_template_part('template-parts/components/find-my-schools');
 */
?>
<section class="section section--soft find-my-schools" aria-labelledby="find-my-schools-title" data-find-schools>
    <div class="container">
        <header class="section-header">
            <h2 id="find-my-schools-title">Find My Schools</h2>
            <p class="section-subtitle">Enter your home address to check the public schools currently assigned to that location. Attendance boundaries can change, and Forsyth County Schools is the final authority.</p>
        </header>

        <form class="stack find-my-schools__form" data-find-schools-form novalidate>
            <div class="cluster">
                <div class="stack" style="flex: 2 1 260px;">
                    <label for="sf_fms_street">Street address</label>
                    <input type="text" id="sf_fms_street" name="street" autocomplete="street-address" required>
                </div>
                <div class="stack" style="flex: 1 1 160px;">
                    <label for="sf_fms_city">City</label>
                    <input type="text" id="sf_fms_city" name="city" autocomplete="address-level2">
                </div>
            </div>
            <div class="cluster">
                <div class="stack" style="flex: 0 1 120px;">
                    <label for="sf_fms_state">State</label>
                    <input type="text" id="sf_fms_state" name="state" value="GA" maxlength="2" autocomplete="address-level1">
                </div>
                <div class="stack" style="flex: 0 1 160px;">
                    <label for="sf_fms_zip">ZIP code</label>
                    <input type="text" id="sf_fms_zip" name="zip" autocomplete="postal-code" inputmode="numeric" maxlength="10">
                </div>
            </div>

            <!-- Honeypot: visually hidden, not type="hidden" -- see suggestion-form.php for the same pattern. -->
            <div class="visually-hidden">
                <label for="sf_fms_hp">Leave this field blank</label>
                <input type="text" id="sf_fms_hp" name="sf_hp_website" tabindex="-1" autocomplete="off">
            </div>

            <button type="submit" class="btn btn-primary" data-find-schools-submit disabled>Find my schools</button>

            <p class="find-my-schools__privacy">
                <strong>Privacy:</strong> your address is used only to look up your school assignment and is never saved or logged.
                Results come directly from Forsyth County Schools' official records; attendance boundaries change over time, so always confirm with the district before enrolling.
            </p>
        </form>

        <div class="find-my-schools__results" data-find-schools-results aria-live="polite"></div>
    </div>
</section>
