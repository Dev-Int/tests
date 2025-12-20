# Workflow - Git & GitHub

Git workflow, GitHub conventions, and pull request process

---

## Git Configuration

**Main branch**: `develop` (NOT main/master)

All PRs target `develop`

---

## GitHub Issues - Conventions

### Title Format

```
[scope] Description
```

**Rules**:
- Infinitive: "Add", "Refactor", "Fix"
- Max 80 characters
- Single scope

### Scopes

**Modules**: `[Admin]` `[Inventory]` `[Shared]`

**Entities**: `[Article]` `[Supplier]` `[Tax]` `[Unit]` `[FamilyLog]` `[ZoneStorage]` `[Company]`

**Technical**: `[DDD]` `[Tests]` `[DX]` `[UX]` `[chore]`

### Labels

**Type labels**:

| Label           | Usage                                       |
|-----------------|---------------------------------------------|
| `backend`       | PHP code, use cases, repositories, entities |
| `frontend`      | Twig templates, LiveComponents, JavaScript  |
| `quality`       | Refactoring, code improvement, tech debt    |
| `use case`      | New business use case                       |
| `bug`           | Bug fix                                     |
| `documentation` | Add/update docs                             |
| `dependencies`  | composer.json, package.json updates         |
| `DX`            | Dev tools, workflow, make scripts, CI/CD    |
| `UX`            | UI, ergonomics, navigation                  |

**Status labels**: `Backlog`, `good first issue`, `help wanted`

### Sub-issues (Complex tasks)

For issues affecting > 5 files:

```markdown
## Sub-issues
- [ ] #XX - [scope] Subtask 1
- [ ] #YY - [scope] Subtask 2
```

**Benefits**: Smaller PRs, easier review, visible progress, simpler rollback

### Example

**Simple feature**:
```
Title: [Supplier] Add unique email validation
Labels: backend, quality
Priority: Medium

Acceptance criteria:
- [ ] Validation in Repository
- [ ] Clear error message
- [ ] Unit + functional tests
```

**Complex feature with sub-issues**:
```
Title: [Article] Add photo management
Labels: backend, frontend, use case

Sub-issues:
- [ ] #160 - [Article] Add photo field in Entity
- [ ] #161 - [Article] Create UploadArticlePhoto use case
- [ ] #162 - [Article] Upload form in CreateArticle
```

---

## Creating Issues via CLI

```bash
gh issue create \
  --title "[{scope}] {title}" \
  --body "{description}" \
  --label "{labels},Backlog"
```

---

## Commit Conventions

### Format

Follow repository commit style (check `git log` for recent commits)

### Git Safety Protocol

**See**: CLAUDE.md for complete Git Safety Protocol

**Key rules**:
- NEVER force push to main/master
- NEVER skip hooks unless explicitly requested
- Avoid `--amend` unless conditions met (see CLAUDE.md)

**Commit message format**:
```bash
git commit -m "$(cat <<'EOF'
feat(scope): ticket-number description

Detailed explanation if needed.

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
EOF
)"
```

---

## Pull Requests

### Creating PRs

**Prerequisites**:
1. Understand changes:
   ```bash
   git status
   git diff
   git log origin/develop..HEAD
   git diff develop...HEAD
   ```

2. Draft PR summary analyzing ALL commits (not just latest)

3. Create PR:
   ```bash
   # Push if needed
   git push -u origin branch-name

   # Create PR
   gh pr create --title "Title" --body "$(cat <<'EOF'
   ## Summary
   - Bullet point 1
   - Bullet point 2

   ## Test plan
   - [ ] Test item 1
   - [ ] Test item 2

   🤖 Generated with [Claude Code](https://claude.com/claude-code)
   EOF
   )"
   ```

### PR Template

```markdown
## Summary
[2-3 bullet points of what changed]

## Test plan
- [ ] Test item 1
- [ ] Test item 2
- [ ] make qa passes
- [ ] Tests pass (make ta / make e2e)

🤖 Generated with [Claude Code](https://claude.com/claude-code)
```

---

## Best Practices

### Issues

- Always use `[scope]` prefix
- Concise but descriptive (max 80 chars)
- Infinitive verb (Add, Refactor, Fix)
- Minimum 1 type label, max 3-4 labels
- Create sub-issues if > 5 files modified

### Commits

- Check `git log` for repository style
- Focus on "why" not "what"
- Do NOT commit secrets (.env, credentials.json)

### PRs

- Analyze FULL branch history (not just last commit)
- Clear summary with context
- Actionable test plan
- Return PR URL when done
