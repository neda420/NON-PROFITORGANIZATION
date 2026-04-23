# Contributing to Helping Paws

Thank you for contributing! Please read this guide before opening an issue or pull request.

---

## Branching Strategy

| Branch | Purpose |
|---|---|
| `main` | Production-ready code. Direct pushes are blocked. |
| `develop` | Integration branch. Merge feature branches here first. |
| `feature/<short-name>` | New features or enhancements. |
| `fix/<short-name>` | Bug fixes. |
| `hotfix/<short-name>` | Critical production fixes. Branch from `main`. |
| `chore/<short-name>` | Tooling, CI, documentation updates. |

Branch names must be lowercase and use hyphens (no underscores or spaces).

---

## Commit Message Convention

Format: `<type>(<scope>): <subject>`

| Type | When to use |
|---|---|
| `feat` | New feature |
| `fix` | Bug fix |
| `security` | Security hardening |
| `docs` | Documentation only |
| `refactor` | Code restructure without behaviour change |
| `test` | Adding or updating tests |
| `chore` | CI, tooling, dependency updates |
| `style` | Code formatting (no logic change) |

Examples:
```
feat(donor): add password strength indicator to registration form
fix(admin): prevent CSRF bypass on delete endpoint
security(auth): enforce session regeneration on login
docs(readme): add Docker quick-start section
```

---

## Pull Request Process

1. **Branch** from `develop` (or `main` for hotfixes).
2. **Open a draft PR** as soon as you push your first commit.
3. **Fill in the PR template** completely.
4. **Ensure CI passes** (PHP lint, security checks, Docker build).
5. **Request a review** from at least one team member.
6. **Address all review comments** before marking as ready.
7. **Squash-merge** into `develop` (or `main` for hotfixes) after approval.

---

## Code Review Checklist

Reviewers should verify:

### Security
- [ ] No hardcoded credentials or secrets
- [ ] All user input is validated and sanitised
- [ ] All DB queries use prepared statements
- [ ] CSRF token verified on state-changing endpoints
- [ ] All output is HTML-escaped before rendering
- [ ] Auth guards present on all protected pages
- [ ] No debug output (`var_dump`, `print_r`) in production code

### Code Quality
- [ ] New PHP files include `declare(strict_types=1)`
- [ ] Centralised helpers used (`getDbConnection()`, `e()`, `verifyCsrfToken()`, etc.)
- [ ] No duplicate DB connection code
- [ ] Error paths redirect with a flash message, not raw `die()`
- [ ] Functions/methods are focused and reasonably short

### Testing
- [ ] Relevant manual test steps described in the PR description
- [ ] No existing behaviour broken

### Documentation
- [ ] `README.md` updated if setup/config steps changed
- [ ] Migration file added for any schema change

---

## Local Development Setup

See the [README](README.md#local-development-without-docker) for full setup instructions.

---

## Reporting Security Vulnerabilities

**Do not open a public issue for security vulnerabilities.**  
Email the maintainer directly and allow 48 hours for an initial response before any public disclosure.
