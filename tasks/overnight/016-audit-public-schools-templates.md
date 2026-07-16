# Audit the public Schools archive and single-school templates

## Objective
Audit staging public Schools archive and single-school templates for draft safety, rendering errors, accessible layout, and data display completeness.

## Files likely involved
- `wordpress/wp-content/themes/southforsyth/archive.php`
- `wordpress/wp-content/themes/southforsyth/single.php`
- `wordpress/wp-content/themes/southforsyth/template-parts/components/school-card.php`
- `wordpress/wp-content/themes/southforsyth/template-parts/components/post-meta.php`
- `wordpress/wp-content/themes/southforsyth/assets/css/main.css`

## Permitted changes
- Fix template rendering defects.
- Improve display of existing sourced metadata.
- Improve empty-state copy that does not invent facts.

## Forbidden changes
- Do not publish school drafts.
- Do not expose draft posts anonymously.
- Do not invent school descriptions, rankings, or recommendations.
- Do not deploy.

## Required verification
- `scripts/verify-theme.sh`
- Staging anonymous archive request.
- Staging authorized draft preview request if credentials are available.
- Confirm draft schools are not public.

## Success criteria
- Public archive loads without PHP errors.
- Anonymous users cannot see draft school singles.
- Authorized previews render sourced fields cleanly.

## Stop conditions
- Draft content becomes publicly visible.
- Template changes require content-model decisions.
- Verification requires production-only access.

## Required final report
- Include URLs checked, HTTP status codes, screenshots if available, and any unresolved design/content issues.
