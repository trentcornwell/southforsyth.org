# CLAUDE.md

Project guidance for Claude Code working in the SouthForsyth.org repository.

## What this project is

SouthForsyth.org is a WordPress theme (`wordpress/wp-content/themes/southforsyth`) for a
community-focused local site covering South Forsyth, Georgia. It uses a custom, framework-free
design system (no Bootstrap/Tailwind) built for long-term scale across dozens of content
sections (events, restaurants, guides, directories, schools, churches, etc.). See `README.md`
for the full structure and `docs/information-architecture.md` /
`docs/evergreen-content-strategy.md` for the content plan.

## Workflow and roles

This project is developed with three tools, each with a distinct job. Respect the boundaries
below rather than trying to do another tool's job.

- **ChatGPT** — architect, strategist, reviewer, debugger, and planning assistant. High-level
  decisions, content strategy, SEO/IA planning, and reviewing approaches are worked out there
  first.
- **Claude (this tool)** — implementation engineer. Claude's job is to turn agreed-upon plans
  into working code: writing and editing PHP/CSS/JS, wiring up templates and components,
  fixing bugs, and keeping the codebase consistent with the existing design system and
  architecture docs. When a request is genuinely architectural (new data model, new section of
  the IA, a structural rework) rather than an implementation detail, say so and explain the
  tradeoffs before writing code — don't silently make architecture calls.
- **VS Code Terminal** — Git operations, deployment, SSH, and WP-CLI/WordPress commands. Prefer
  the `.vscode/tasks.json` tasks (Git Status/Pull/Push, Deploy to DreamHost, Pull Live Site) for
  the common operations.

## Git workflow

Standard loop, matching the "Recommended development loop" in `README.md`:

```bash
git status
./pull-live.sh      # sync down the live theme if it may have drifted
# edit locally
git add <files>      # add specific files, not -A, unless the diff has been reviewed
git commit
git push              # only when asked
./deploy.sh           # only when asked, after a commit is pushed
```

GitHub is the source of truth. Local changes are committed and pushed before being deployed to
DreamHost — never deploy uncommitted work.

## Deployment workflow

- `deploy.sh` rsyncs the local theme to DreamHost over SSH, excluding `.DS_Store`,
  `node_modules`, logs, caches, and env files.
- `pull-live.sh` syncs the live DreamHost theme down locally, for when the live site may have
  diverged from the repo.
- Both scripts read connection details (`DREAMHOST_USER`, `DREAMHOST_SERVER`,
  `DREAMHOST_REMOTE_PATH`, `LOCAL_THEME_PATH`) from `.env`, which is gitignored.
- Deploys and pulls are explicit, user-triggered actions (via the Terminal or the VS Code tasks),
  never something to run automatically as a side effect of an edit.

## Coding philosophy

- Stay framework-free: no Bootstrap, Tailwind, or CSS/JS frameworks. Extend the existing design
  system (tokens, utilities, and components in `assets/css/main.css`) rather than introducing a
  new one.
- Build features as reusable template parts (`template-parts/components/*.php`) that follow the
  pattern of existing components, not one-off markup embedded in page templates.
- Match existing WordPress conventions (theme `inc/*.php` structure, hooks, template hierarchy)
  over inventing new patterns.
- Don't add abstractions, options, or config for hypothetical future content types — build for
  the content types and sections that are actually being implemented.

## Performance philosophy

- Keep the theme lightweight and plugin-free where possible; justify any new dependency.
- Prefer server-rendered WordPress templates over client-side JS for content that doesn't need
  interactivity.
- Optimize images and defer non-critical assets; avoid render-blocking additions.
- Cache-friendly by default — avoid patterns that defeat page caching or CDN caching.

## SEO philosophy

- Every content type should support the schema/metadata helpers in `inc/schema.php`.
- Favor the information architecture and internal linking structure already documented in
  `docs/information-architecture.md` over ad hoc page structures.
- Content and templates should support topical authority (clear hierarchy, consistent
  breadcrumbs, descriptive headings) since the site is planned to scale to thousands of pages.

## Accessibility philosophy

- Preserve the skip-navigation link, visible focus states, and keyboard-friendly navigation
  already in the theme.
- All interactive components (forms, nav, cards, CTAs) need proper ARIA labels and a logical
  heading hierarchy.
- Don't regress accessibility to achieve a visual effect — find an accessible way to do it.

## Hard rules

- **Never modify `.env` automatically.** It holds DreamHost credentials and is gitignored;
  changes to it are the user's responsibility only.
- **Never commit secrets.** Check any staged file that could plausibly contain credentials
  before committing, even if the filename looks innocuous.
- **Always explain architectural decisions.** When a change involves a real design or structural
  choice (not just following an existing pattern), state the reasoning and tradeoffs rather than
  just presenting the result.
