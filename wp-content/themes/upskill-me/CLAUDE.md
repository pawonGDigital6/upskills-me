# UpSkill Me — Project Instructions

WordPress LMS site built from Figma, page by page, as an ACF block theme. All theme work happens
here. `tools/ACF-BLOCK-WORKFLOW.md`, `tools/figma-to-wp-prompt.md` and `tools/PROJECT-CONTEXT.md`
(WordPress root) are the architectural contract; this file records the decisions taken on top.

Figma: **UpSkill Me (Working File)** — `YDUfoAlbZX0PaG73CVDizt`. Only the **Mobile & Sitemap**
canvas is current (1440 desktop, 390 mobile frames). Every other canvas is superseded.

The designer's HTML build (`E:\digital-six\up-skills\resources\designer-provided-claude-template`)
is a copy and interaction reference only — its tokens, breakpoints and markup are not carried over.

---

## 1. Standing rules

- **Do not run build/watch commands** unless the developer asks. They run `npm run start` and
  `npm run start:global` themselves (restart both after adding a block or touching build config).
- **Tailwind first** in block markup. SCSS only for state-driven chrome (header, mega menu, drawer,
  footer in `src/global/scss/layout/`), per-block states in a block's `style.scss`, and plugin
  markup (LearnDash, WooCommerce, CF7) in `src/global/scss/plugins/` — one partial per screen or
  component, reading the design tokens, never repeating hex values.
- **Tokens come from Figma's variables** (`src/global/tailwind/design-system.css`). A value that
  differs between 390 and 1440 is a `clamp()` between the two; do not add one-off tokens for single
  measurements — use Tailwind's native scale or an arbitrary value with the Figma source in a comment.
- **Headings** take their highlighted phrase as `<em>` (`Get a <em>head start.</em>`); force line
  breaks with `<br>`. `upskill_heading()` allows only br/em/strong/span.
- **Reuse modules.** Same structure, different content → reuse the block. Small differences (a
  colour, an optional element) → an ACF option. A section whose layout differs gets its own
  purpose-built block, never a layout option (the user's rule; heroes included: page-hero /
  heading-hero / ladder-hero / hub-hero; an optional element on the same layout stays a toggle,
  e.g. page-hero "Show chips"). Same module + same content on several
  pages → a synced pattern.
  LearnDash/plugin-dependent modules wait until the plugin is installed and assessed.
- **Interactions.** Keep the established hover, focus and animation behaviour (button arrow nudge
  and press, card lifts, image zooms, scroll reveals, mega-panel slide, the designer's category
  row hover with its photo, the How It Works step flight). Scroll reveal is a CSS animation
  (`.reveal` + `.is-in`, `--i` staggers by 90ms) so hover transitions cannot cancel it. The one exception: buttons carrying the play triangle (`.btn--play`)
  do not nudge their icon. Change interactions only when explicitly asked.
- **Hold plugin-dependent work.** Anything that needs LearnDash/WooCommerce is not developed or
  customised until the plugin is installed.
- **Image standard (every module).** Export from the Figma original (never the designer HTML or a
  screenshot), crop to the design's frame/ratio as Figma frames it, and size to 2x the largest
  display width (enough for 3x on the 390 frame). WebP, quality ~84, effort 6, smart subsampling,
  light sharpen after downscale (sharp: lanczos3 + sharpen σ0.6); never upscale a small source.
  Upload to the Media Library with real alt text (empty only when decorative). Render with
  `upskill_image( $id, 'full', [ 'sizes' => … ] )` so the srcset is the proportional set, and
  write `sizes` from the actual layout breakpoints. For `object-cover` images, `sizes` is the width
  the photo is *drawn* at (box height × image ratio when that is wider than the box), not the box
  width. WordPress's `sizes="auto"` is switched off (functions.php) because it uses the box width
  and served soft images; images above the fold load `eager`. Before handing over, check every
  image's chosen srcset candidate against its drawn width at 1x/2x/3x.
- **Fluid layout first.** Avoid `position: absolute`/`fixed` and fixed heights unless genuinely
  necessary. Stack layers in a one-cell grid (`grid grid-cols-1 grid-rows-1` + `col-start-1
  row-start-1`), size media with `aspect-*` per breakpoint, give bars height through padding, and
  use `writing-mode` for vertical labels so text always sizes its own box. (Hero showcase is the
  reference implementation.)
- **Classes never contain `>`** (e.g. `[&>article]:flex-1`): WordPress's texturize turns it into
  `&#8243;` and breaks the attribute. Use `*:` or a class on the child.
- **Change only what is asked.** Do not refactor, remove or "optimise" existing code, files or
  animations unless the instruction says so.
- **QA gate.** A page, block or template is not done until the `figma-qa` skill has passed:
  `cd tools/qa && node run.mjs <page>` (visual diff vs Figma at 1440/390, style audit, designer-template
  interactions, overflow/images/console at 320–1920), every flagged diff image looked at, findings fixed
  or accepted with a reason, re-run clean. Add new pages to `tools/qa/pages.json` first. When a shared
  file changes, also run `node run.mjs home --checks=visual,template`.
- **Do not regress the Home page.** Changing a shared Home module for another page needs approval.
- **Parallel work with the developer** (`docs/collaboration-workflow.md`): same folder and database,
  so separation is by file ownership. Before editing any existing tracked file, check `git status`,
  `git diff` and `git log -1 -- <file>`; never revert or reformat the developer's changes. LMS work
  goes into new files (`learndash/`, `woocommerce/` overrides, `scss/plugins/<plugin>/`, `inc/lms-*`,
  new blocks). Shared files get one-line additions only. Write only the ACF JSON groups being
  changed (never regenerate all). Never re-run page-content seeds on existing pages. Don't commit
  unless asked; list changed files at the end of each task.
- **Keep `docs/` current**: every finished LMS feature gets a six-point entry in
  `docs/lms-progress-log.md` (plus any admin/database setting changed), and its status in
  `docs/lms-requirements-and-implementation.md`.

## 2. Architecture

```
src/blocks/<name>/        block.json, render.php, index.js, style.scss, editor.scss, view.js (optional)
src/global/tailwind/      design-system.css (tokens + components), tailwind.css, tailwind-editor.css
src/global/scss/          layout/ (chrome, block options), plugins/ (third-party markup)
src/global/js/components/ announcement, navigation (sticky, mega, drawer), search, disclosure, reveal, deferred-video, video-player, library
inc/                      fonts, template-helpers, taxonomies, post-types, options, nav-menu, acf-register-blocks, contact-form-7, learndash, woocommerce
acf-json/                 version-controlled field groups
images/, images/icons/    theme imagery and currentColor SVG icons
fonts/                    Lay Grotesk (TRIAL — 69 glyphs) + Geist fallback
```

- Blocks auto-register from `build/blocks/*`; namespace `acf-block/<name>`, category `acf-blocks`,
  `blockVersion: 3` with fields in the expanded editor. Front-end block assets are dequeued on pages
  without the block, so anything shared belongs in `src/global`.
- ACF: groups `group_upskill_block_<block>`, fields `field_upskill_<block>_<name>`. One **Content**
  tab per block plus the shared **Block Options** tab (`group_677e01009aea0`). Section headers are a
  seamless clone of `group_upskill_component_section_header` (eyebrow_icon, eyebrow, heading, intro,
  button) rendered by `upskill_section_header()`.
- ACF field groups also have database copies. When ACF lists "Sync available" for a group, sync it
  before editing that group in the admin: editing the stale copy drops the newer fields from the
  block form, and re-saving a page then loses their content (Industries hero, 28 Oct).
- Global content: Site Settings → Header / Footer (`upskill_option()`). Menus: `primary` (mega panels
  from item Description + "Panel image"), five footer columns (column title = menu name), `footer-legal`.

## 3. Data model

- **Training taxonomies** `industry`, `training_category`, `cohort` (Level) are registered on
  LearnDash's `sfwd-courses`. Until LearnDash exists a temporary **Training** admin menu exposes them.
  Term fields: landing page (term links resolve to it via `term_link`), industry card style/icon/tags,
  cohort card image/tagline. Counts come from `upskill_term_session_count()` — hidden while 0.
- **FAQ** post type (not public) + `faq_category`.
- **Resources**: blogs are core posts (category = topic chip; ACF "Display title" gives the article
  hero its highlighted phrase). `case_study` (/case-study/slug/, ACF industry) and `guide`
  (/guide/slug/) are public CPTs; `testimonial` (not public, ACF name + rating) feeds the carousel.
  Listings are pages under Resources (Blogs, Case Studies, Guides) using the Resource Library block.
- Back-office spec vocabularies (jurisdiction, org type, compliance/performance) are deferred to the
  LearnDash phase.

## 4. Pending LearnDash / client

- **Free Sessions** (26 Oct): the block lists LearnDash **Sample** sessions (`inc/lms-free-sessions.php`:
  chosen ones first, topped up automatically; cover = session featured image, else the course's).
  On Home after the hero (anchor `free-sessions`, the target of the `#free-sessions` links) and in
  patterns #944–946.
- **Account entry points** are held: the header account icon, the drawer sign-in buttons and the
  footer Account column render only when their option or menu location is set, and stay empty
  until LearnDash provides the screens. Since 24 Oct the header account icon always opens the
  account menu, signed in or out (signed out: Learner / Organisation sign in + Find Training) (Figma 49398:107717,
  `template-parts/account/header-menu.php`, rows per role from `upskill_account_menu_items()`,
  hover/click/tap in navigation.js `accountMenu()`). The drawer shows dashboard + Log out when signed in.
- **Session counts** ("76 sessions") render only above zero, i.e. once courses exist.
- Lay Grotesk must be licensed before launch (trial build lacks ' & $ % – etc.). Interim: the
  theme serves auto-hinted WOFF2 conversions of the trial OTFs (see inc/fonts.php) because the
  unhinted OTFs drew weight 500 noticeably heavier than Figma at 13–16px. Replace both with the
  licensed webfont kit.
- **Client queries (2026-10-12) — do not implement until answered:**
  - Commercial model: spec = 30-day trial, annual admin fee, monthly fee from per-video prices; Figma =
    one-off price per learner per level × seats ("subscriptions" label, no renewal shown). One-off works
    with the installed stack. Recurring + seats needs WooCommerce Subscriptions (US$279/yr) — Group
    Registration only integrates with it; MemberDash (in the LD download) recurs but has no seats/cart.
  - Payment gateway: not named in any resource (spec only says "credit card").
  - GST: tax off in WooCommerce until confirmed (prices incl./excl., ABN-registered for GST).
  - Course vs session: client videos are single sessions (OH002, GG001, GG200); Figma splits a course
    into 6 sessions. Levels (Team Member/Manager/Director): separate courses vs one course.
  - Spec fields not in the design: Compliance/Performance type, discipline, org type, jurisdiction,
    subscriber-specific content, 2FA, subscriber details (ABN etc.).
- **LMS stack installed 2026-10-12**: LearnDash LMS 5.2.1 (core already includes the WooCommerce
  integration, Reports/ProPanel, Notifications, Course Reviews and Course Grid — do not install those
  add-ons separately; the WooCommerce add-on fatals as a duplicate), WooCommerce 11.2.1 (AUD, AU:QLD,
  0 decimals, accounts required at checkout, coming-soon off, Terms/Returns = the site's pages) and
  LearnDash Group Registration 4.4.2.1 (purchase with N seats → group; [wdm_group_users] on
  /groups-dashboard/). LearnDash: course archive off (/courses/ stays the library), LD course
  categories/tags off, Groups not public, native gateways off. `inc/learndash.php` and
  `inc/woocommerce.php` remove plugin CSS/JS (and the orphaned jQuery) on views without LMS/store
  content. Licence keys (LearnDash, Group Registration) are entered by the developer.
- **LMS module 1 (2026-10-12) — course & session model:** LearnDash course = training; LearnDash
  lesson = session (Custom Labels Lesson→Session). Video Progression (auto-complete on watch) and
  Materials are native; ACF `group_upskill_session` adds session code, version, format, duration,
  key points. Courses are "closed" (access via purchase/enrolment). Sample: three courses from the
  client videos (OH002, GG001, GG200), one session each.
- **LMS D1 (2026-10-12) — session page:** `single-sfwd-lessons.php` + LearnDash overrides
  `learndash/ld30/lesson.php` (layout; keeps LD hooks, progression message, completed alert, Mark
  Complete form) and `learndash/ld30/modules/lesson-video.php` (adds an `<!--upskill-session-text-->`
  marker so video and text render apart). Styles `scss/plugins/learndash/_session.scss`. LD30 reads
  theme overrides only from `learndash/ld30/`. Logged-in QA uses the scratchpad
  `qa-session-fixture.php` (up/down) + `sbs-auth.mjs`.

## 5. Progress

- **Home** (`49325:14379` / `49343:29333`) — built. Blocks: `hero`, `free-sessions`,
  `industry-showcase`, `category-list`, `how-it-works`, `feature-media`, `cohort-cards`, `faqs`. Global:
  announcement bar, header + mega panels, mobile drawer, search dialog, CTA band, footer.
- **Why UpSkill Me, About, Resources, Blogs, Blog detail, Case Studies** — built (non-LMS sections).
  Heroes (one block per design since 28 Oct, no layout switch): `page-hero` (copy beside a photo
  or illustration — About, Resources, Blogs, Guides; **Show chips** puts up to four link chips
  over the photo — Resources), `heading-hero` (eyebrow, headline, intro; centred — Case Studies,
  or left on a panel — FAQs), `ladder-hero` (Why: two badges, first left/purple, second
  right/green, and the ladder), `hub-hero` (Industries, Categories). One purpose-built block per card section (2026-10-20; card-grid retired):
  `feature-grid`, `principles`, `mission-pillars`, `expertise-highlights`, `impact-highlights`,
  `training-needs`, `textured-cards`, `resource-links`, `icon-rows`, `outcome-columns`. White cards
  that turn dark on hover share the `feature-card` classes (`scss/components/_feature-card.scss`).
  Every element carries a BEM class (`<block>__<element>`), styling stays in utilities. `video-feature`,
  `media-story`, `resource-library` (grid, split row, guides, featured, paginated
  listing; template-parts/cards/resource.php + guide.php), `testimonials` (scroll-snap carousel),
  `key-takeaways` (article content). `how-it-works` gained an optional image (single track + photo);
  Home output is unchanged. `single-post.php` is the Blog detail (contents list with scroll-spy,
  share links, related posts). Held for LearnDash: "Related Training" links on blog cards/articles.
- **FAQs, Guides, Case Study detail, Contact, 404, Privacy, Terms, Returns** — built 2026-10-07,
  awaiting review. New blocks: `statement` (centred two-tone statement + details row), `contact-hero`
  (photo card + Contact Form 7 form card), `location-map` (Google embed). Options added: FAQs "Grouped
  by topic" (grouped.php, phone chips + search in view.js), media-story social links + "boxed" (the FAQs hero is `heading-hero`, left). Templates:
  `single-case_study.php` (hero image field, related case studies via template-parts/related-posts),
  `template-policy.php` (native editor content, "Policy navigation" from its headings, texturize off so
  hyphens stay hyphens), `404.php` (no CTA band). CF7: inc/contact-form-7.php (no plugin CSS/autop,
  `<i data-icon>` placeholders) + scss/plugins/_contact-form-7.scss. Held: case study "Industry Training
  Resources" (LearnDash sessions). Nine FAQ answers are placeholders awaiting approved copy.
- **Case study / guide content (2026-10-23):** `upskill_contain_editor_text()` (inc/article.php) wraps
  each run of editor text in the reading container (680 centred, `.article-body`) and leaves ACF blocks
  full width (Key Takeaways stays with the text); used by `single-case_study.php` and `single-guide.php`.
  `single-guide.php` has no Figma: Blog Detail hero (chip, title, excerpt, featured image) + article
  body, "In this article" when the guide has h2s; `.type-guide .article-body` adds h3/h4 and numbered lists.
- **Industry Hub + Categories (2026-10-24):** pages /industries/ (27) and /categories/ (28), Figma
  49325:14553 / 49325:14595. Hero: block `hub-hero` (since 28 Oct; it was a page-hero option):
  two **Tags** (count, title, icon, photo tile; first left, second right, from 1200) and three
  **Photo cards** (the second is wide, first on phones). Home's `industry-showcase` / `category-list` content copied (Home is on hold, so not
  turned into synced patterns); new thin block `training-library` (section header over
  template-parts/library/browser.php, one per page); pattern #1018; `media-story` gained **List style:
  Points** (Categories "Training for what matters": white rounded panel at every width, lead-over-text
  points, 16px intro via `section-header--base`); Why page's `testimonials`.
- **Industry Resources (2026-10-23):** block `industry-resources` (Figma 49325:16856, also Industry Hub and
  Categories), placed through the synced pattern "Industry Training Resources" (#1018) on case study 383.
  Sessions = chosen courses, else `upskill_library_industry_courses()` for the case study's Industry /
  the industry page, topped up with the newest; cards are `cards/training.php` with `compact`. Synced
  patterns (`core/block`) count as full-width sections in `upskill_contain_editor_text()`.
- **Training library pagination + video transport (2026-10-28):** "Load more" replaced by numbered
  pages (Figma 49325:14592 / 49347:88528), `template-parts/library/pagination.php`: `pg` parameter
  (page one has none; any filter, sort or search change drops it), six per page
  (`upskill_library_per_page()`), a page past the end shows the last page, relative `?…#results` links
  so it stays on /courses/, term pages (`all=1` kept) and the Training Library block; library.js loads
  them in place (history, scroll to the results top, focus the first card). Phones list first, current
  and last. The video card transport bar is shared: `template-parts/video/controls.php` +
  `scss/components/_video-card.scss` + `js/components/video-player.js` (Free Sessions and every free
  training card, incl. Industry Resources and Course Completed; one plays at a time; re-run on AJAX
  results). Free Sessions lost its own view.js; its phone-only "bar after start" rule stays in its
  style.scss. Video Feature's script/style are scoped to `.block-video-feature`. The LearnDash
  session player keeps LearnDash's own controls (Video Progression drives it).
  Training card videos: poster = course image, else `images/course-poster-placeholder.webp`;
  `.video-card__scrim` is a dark fade under the bar so the controls read over light frames.
