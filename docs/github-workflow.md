# GitHub Workflow - What Actually Works

## Push to GitHub ✅
```bash
# Add SSH remote (works)
git remote set-url origin git@github.com:bonzysalesman/pmp-exam-prep.git

# Push branches (works)
git push -u origin branch-name
git push origin --all
```

## Pull Request Creation ❌
```bash
# GitHub CLI - FAILS (insufficient token permissions)
gh pr create --title "Title" --body "Body"
# Error: GraphQL: Resource not accessible by personal access token (createPullRequest)

# Manual PR creation - WORKS
# Go to: https://github.com/bonzysalesman/pmp-exam-prep/compare/branch-name
```

## Repository Creation ❌
```bash
# GitHub CLI - FAILS (insufficient token permissions)  
gh repo create repo-name --public
# Error: GraphQL: Resource not accessible by personal access token (createRepository)

# Manual creation - WORKS
# Go to: https://github.com/new
```

## Working Authentication Status
```bash
gh auth status
# Shows: Token scopes: 'gist', 'read:org', 'repo'
# Missing: workflow, admin:repo_hook permissions for PR/repo creation
```

## Recommended Workflow
1. **Code & commit locally**
2. **Push via SSH** (works reliably)
3. **Create PRs manually** via GitHub web interface
4. **Use GitHub CLI only for** read operations (status, list, view)

## Token Fix (if needed)
```bash
gh auth login --scopes repo,workflow,admin:repo_hook
```
