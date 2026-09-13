# Recommended Agent Skills

"Agent Skills" (`SKILL.md` files) are a portable, cross-tool standard —
distinct from `AGENTS.md` (project context) and MCP servers (live tool
connections). A skill is a folder of instructions (and sometimes scripts)
an agent loads for a specific kind of task. This list is curated for this
project's stack and its three active initiatives (UI/UX redesign, security
hardening, SEO), not a general "best skills" list.

**A skill is a set of instructions your agent will follow — treat
installing a third-party one with the same caution as adding a dependency
you haven't read the source of.** Prefer official/first-party skills where
one exists. For anything third-party, read it before installing, or run it
through one of the vetting tools in the last section first.

## Official (Anthropic) — install with confidence

Via the Claude Skills plugin marketplace
(`/plugin marketplace add anthropics/skills` in Claude Code, then
`/plugin install <name>@anthropic-agent-skills`):

- **`frontend-design`** — the single most directly relevant one available
  right now: guidance for distinctive, intentional visual design when
  building or reshaping UI, aesthetic direction, and avoiding
  templated-looking defaults. Load this for the `docs/DESIGN.md` redesign
  work specifically.
- **`webapp-testing`** — for verifying UI changes actually work in a
  browser, which matters more than usual here given this project has no
  automated test suite (`AGENTS.md`) — this is close to the only
  systematic verification available for front-end changes.
- **`skill-creator`** — for authoring a project-specific skill later (for
  example, one that encodes this project's exact PHP/CSS conventions more
  tersely than re-reading `AGENTS.md` and `docs/ARCHITECTURE.md` every
  session).
- **`mcp-builder`** — only if a live tool connection is ever wanted (e.g.
  a deploy/FTP integration) rather than a static instruction skill.

## For the three active initiatives

**UI/UX redesign** — a dedicated UI/UX skill on top of `frontend-design`
can help translate `docs/DESIGN.md`'s direction into concrete component
work; several exist under names like `ui-ux-pro-max` on GitHub. Read one
before trusting it — this category is exactly where a vague, generic skill
adds less than just pointing the agent at `docs/DESIGN.md` directly.

**Security hardening** (`SECURITY.md`'s roadmap) — a code-review /
security-audit-focused skill is worth adding once that work starts in
earnest; several community collections (searchable as "claude code review
skill" / "codebase security audit skill") package this as a structured,
severity-ranked review rather than an open-ended "look for bugs" prompt,
which matters more for a project handling real payments and customer data.

**SEO** — less skill-dependent than the other two: `docs/SEO.md` is
already a concrete checklist, and the highest-value SEO work here (Core Web
Vitals discipline, structured data, indexing hygiene) is closer to
following that checklist carefully than to needing specialized tooling. If
one is added anyway, prefer one that checks Core Web Vitals impact and
structured-data validity specifically, not generic "keyword" advice, which
doesn't apply to a checkout-flow-heavy site like this one.

**Prompt engineering / context optimization** — for tuning how future
sessions with a coding agent are structured (shorter, higher-signal prompts
and context files — the same principle `AGENTS.md` was written around).
Search "prompt engineer" or "prompt architect" among Claude Skills; several
package Anthropic's own published prompting guidance
(`docs.claude.com/.../prompt-engineering/overview`) into a reusable skill.
That underlying guidance is worth reading directly regardless of whether a
packaged skill is installed on top of it.

## Vet before installing anything third-party

Skills execute as instructions an agent trusts, which makes a malicious or
just poorly-written one a real risk, not a hypothetical one. Before adding
any skill not from Anthropic's own repository:

- Open `SKILL.md` and read it — it's plain text, this takes a minute.
- If it ships scripts alongside the instructions, read those too, or run it
  through a skill-scanning tool (searchable as "skill validator" / "skill
  security auditor" / "SkillSpector") before trusting it in this repo.
- Prefer a skill with visible usage/stars and a maintained repository over
  a brand-new, unverifiable one, especially for anything touching this
  project's payment, customer-data, or admin-auth code.
