# Verify the community suggestion workflow

## Objective
Verify on staging that public suggestions are created as pending moderation items and cannot directly change published content.

## Files likely involved
- `wordpress/wp-content/themes/southforsyth/inc/community/class-suggestion-handler.php`
- `wordpress/wp-content/themes/southforsyth/inc/community/class-suggestion-moderation.php`
- `wordpress/wp-content/themes/southforsyth/template-parts/components/suggestion-form.php`
- `wordpress/wp-content/themes/southforsyth/single.php`

## Permitted changes
- Fix workflow defects discovered on staging.
- Improve validation, nonce handling, or moderation reporting.
- Add documentation for moderator flow.

## Forbidden changes
- Do not weaken nonce or capability checks.
- Do not allow anonymous users to moderate suggestions.
- Do not auto-apply suggestions without moderator approval.
- Do not test against production.

## Required verification
- `scripts/verify-theme.sh`
- Staging anonymous form submission.
- Staging authenticated moderation check if credentials are available.
- Anonymous moderation request denial.

## Success criteria
- Suggestions are pending `sf_suggestion` posts.
- Target content is unchanged until approved.
- Anonymous moderation requests fail.

## Stop conditions
- Suggestion submission mutates target content directly.
- Anonymous moderation succeeds.
- Staging credentials or safe test content are unavailable.

## Required final report
- Include request paths, status codes, created suggestion IDs, moderation results, and cleanup notes.
