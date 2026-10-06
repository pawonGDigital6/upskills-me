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
- **Reuse modules.** Same structure, different content → reuse the block. Medium differences → add an
  ACF option. Same module + same content on several pages → a synced pattern.
  LearnDash/plugin-dependent modules wait until the plugin is installed and assessed.
- **Interactions.** Keep the established hover, focus and animation behaviour (button arrow nudge
  and press, card lifts, image zooms, scroll reveals, mega-panel slide, the designer's category
  row hover with its photo, the How It Works step flight). Scroll reveal is a CSS animation
  (`.reveal` + `.is-in`, `--i` staggers by 90ms) so hover transitions cannot cancel it. The one exception: buttons carrying the play triangle (`.btn--play`)
  do not nudge their icon. Change interactions only when explicitly asked.
- **Hold plugin-dependent work.** Anything that needs LearnDash/WooCommerce is not developed or
  customised until the plugin is installed. Free Sessions is held off the Home page as built.
- **Image standard (every module).** Export from the Figma original (never the designer HTML or a
  screenshot), crop to the design's frame/ratio as Figma frames it, and size to 2x the largest
  display width (enough for 3x on the 390 frame). WebP, quality ~84, effort 6, smart subsampling,
  light sharpen after downscale (sharp: lanczos3 + sharpen σ0.6); never upscale a small source.
  Upload to the Media Library with real alt text (empty only when decorative). Render with
  `upskill_image( $id, 'full', [ 'sizes' => … ] )` so the srcset is the proportional set, and
  write `sizes` from the actual layout breakpoints.
- **Change only what is asked.** Do not refactor, remove or "optimise" existing code, files or
  animations unless the instruction says so.
- **Do not regress the Home page.** Changing a shared Home module for another page needs approval.

## 2. Architecture

```
src/blocks/<name>/        block.json, render.php, index.js, style.scss, editor.scss, view.js (optional)
src/global/tailwind/      design-system.css (tokens + components), tailwind.css, tailwind-editor.css
src/global/scss/          layout/ (chrome, block options), plugins/ (third-party markup)
src/global/js/components/ announcement, navigation (sticky, mega, drawer), search, disclosure, reveal, deferred-video
inc/                      fonts, template-helpers, taxonomies, post-types, options, nav-menu, acf-register-blocks
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
- Global content: Site Settings → Header / Footer (`upskill_option()`). Menus: `primary` (mega panels
  from item Description + "Panel image"), five footer columns (column title = menu name), `footer-legal`.

## 3. Data model

- **Training taxonomies** `industry`, `training_category`, `cohort` (Level) are registered on
  LearnDash's `sfwd-courses`. Until LearnDash exists a temporary **Training** admin menu exposes them.
  Term fields: landing page (term links resolve to it via `term_link`), industry card style/icon/tags,
  cohort card image/tagline. Counts come from `upskill_term_session_count()` — hidden while 0.
- **FAQ** post type (not public) + `faq_category`.
- Back-office spec vocabularies (jurisdiction, org type, compliance/performance) are deferred to the
  LearnDash phase.

## 4. Pending LearnDash / client

- **Free Sessions** (block + player) is held: built, but not placed on the Home page and not to be
  developed further until LearnDash, when the sessions become LearnDash **sample lessons**.
  The `#free-sessions` links (announcement bar, menu promo, CTA band, How It Works, footer) land
  on the Home page without a target until then.
- **Account entry points** are held: the header account icon, the drawer sign-in buttons and the
  footer Account column render only when their option or menu location is set, and stay empty
  until LearnDash provides the screens.
- **Session counts** ("76 sessions") render only above zero, i.e. once courses exist.
- Lay Grotesk must be licensed before launch (trial build lacks ' & $ % – etc.).
- Commercial model (one-off tier × seats vs monthly subscription) decides WooCommerce Subscriptions.

## 5. Progress

- **Home** (`49325:14379` / `49343:29333`) — built. Blocks: `hero`, `free-sessions`,
  `industry-cards`, `category-list`, `how-it-works`, `feature-media`, `cohort-cards`, `faqs`. Global:
  announcement bar, header + mega panels, mobile drawer, search dialog, CTA band, footer.
