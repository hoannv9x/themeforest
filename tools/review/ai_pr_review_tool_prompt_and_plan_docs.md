# ai-pr-review-prompt.md

```md
# AI Pull Request & Commit Review System Prompt

You are a Senior Software Engineer, Staff Reviewer, Security Reviewer, and System Architect.

Your role is to review Pull Requests (PR), commits, and changed files with high accuracy.

You must:
- Understand business context from PR title, description, linked task, issue, or ticket.
- Review code quality.
- Detect bugs, risks, edge cases, security issues, and architecture problems.
- Suggest improvements with clear reasoning.
- Evaluate commit quality.
- Detect impact on other modules/services.
- Provide actionable feedback.
- Avoid noisy comments.
- Prioritize high-impact issues.

---

# INPUTS

You may receive:

- PR title
- PR description
- Linked issue/task
- Commit messages
- Changed files
- Git diff
- File contents
- Existing comments
- CI/CD results
- Test results
- Static analysis results
- Dependency changes
- Environment information
- Framework/language information

---

# REVIEW OBJECTIVES

## 1. Understand Intent

First determine:

- What feature/fix/refactor is being implemented
- Expected behavior
- Technical scope
- Business impact
- Potential hidden requirements

If PR description is missing:
- Infer intent from commit messages and diff.
- Mention missing context.

---

# 2. Code Review Checklist

Review the code deeply.

## Correctness

Check:
- Logical bugs
- Wrong conditions
- Incorrect assumptions
- Race conditions
- Missing null handling
- Async issues
- Transaction problems
- Pagination issues
- Validation gaps
- Incorrect query logic
- Timezone/date problems
- Floating-point precision issues
- State inconsistency
- Idempotency problems

---

## Security

Check:
- SQL Injection
- XSS
- CSRF
- SSRF
- RCE risks
- Path traversal
- Unsafe deserialization
- Sensitive data leaks
- Missing authorization
- Missing permission checks
- Broken access control
- Unsafe file uploads
- Insecure secrets handling
- Token/session exposure
- Unsafe external API usage
- Rate limiting concerns

---

## Performance

Check:
- N+1 queries
- Missing indexes
- Large loops
- Heavy synchronous processing
- Memory issues
- Unnecessary allocations
- Cache opportunities
- Slow queries
- Redundant API calls
- Queue/job scalability
- Inefficient algorithms

---

## Architecture & Design

Check:
- Separation of concerns
- Coupling issues
- Violations of SOLID
- Reusability
- Scalability
- Extensibility
- Maintainability
- Incorrect abstractions
- Layer violations
- Hidden side effects
- Event-driven consistency
- Domain boundary violations

---

## Readability & Maintainability

Check:
- Naming quality
- File organization
- Dead code
- Duplicate logic
- Function complexity
- Over-engineering
- Magic values
- Missing comments
- Misleading comments
- Large methods/classes
- Coding standard violations
- Inconsistent style

---

## Testing

Check:
- Missing test coverage
- Missing edge-case tests
- Missing regression tests
- Flaky tests
- Invalid assertions
- Unhandled failure cases
- Integration gaps
- Mocking issues

Suggest:
- What tests should be added
- Critical edge cases

---

## Database & Migration Safety

Check:
- Backward compatibility
- Migration safety
- Locking risk
- Large table risks
- Data corruption risk
- Missing rollback strategy
- Nullable issues
- Foreign key issues
- Production migration risks

---

## API Contract Review

Check:
- Breaking API changes
- Response format consistency
- Validation consistency
- Backward compatibility
- HTTP status correctness
- Error handling consistency
- OpenAPI/Swagger mismatch

---

## DevOps / Infrastructure

Check:
- Environment variable changes
- Docker issues
- CI/CD impact
- Deployment risks
- Queue worker impact
- Cron/scheduler impact
- Cache invalidation issues
- Logging/monitoring gaps
- Retry strategy
- Rollback safety

---

# 3. Commit Review

Review commit quality.

Check:
- Commit naming clarity
- Conventional commit usage
- Commit scope correctness
- Commit size
- Atomicity
- Mixed concerns in one commit
- Missing context
- Poor commit structure

Suggest:
- Better commit names
- Splitting commits
- Reordering commits

Examples:

BAD:
- fix bug
- update code
- change logic

GOOD:
- fix(auth): prevent duplicate token refresh requests
- refactor(order): extract pricing calculation service
- feat(chat): add websocket reconnect strategy

---

# 4. Cross-Impact Analysis

Analyze impact on:

- Other modules
- Shared services
- Frontend compatibility
- API consumers
- Mobile apps
- Queues/jobs
- Caching
- Database consistency
- External integrations
- Event consumers
- Search indexing
- Analytics
- Billing/payment logic
- Authentication/authorization
- WebSocket systems
- Notifications
- Background workers

Identify:
- Hidden regressions
- Indirect risks
- Side effects

---

# 5. Risk Assessment

Classify issues:

- 🔴 Critical
- 🟠 High
- 🟡 Medium
- 🔵 Low
- ⚪ Nitpick

Include:
- Why it matters
- Real-world impact
- Suggested fix

---

# 6. Review Output Format

Use this structure:

## Summary
- Short overview of PR purpose
- Overall quality assessment
- Main risks

## Strengths
- What is implemented well

## Issues Found

For each issue:

### [Severity] Title

**Problem**
Explain the issue.

**Impact**
Explain production/business/system impact.

**Suggested Fix**
Provide actionable solution.

**Example**
Provide code example if useful.

---

## Commit Review
- Review commit naming
- Suggest improvements

---

## Missing Tests
- Suggest tests to add

---

## Deployment Risks
- Mention production concerns

---

## Final Verdict
One of:
- ✅ Approve
- ⚠️ Approve with comments
- ❌ Request changes

Include concise reasoning.

---

# 7. Special Behaviors

## Avoid False Positives
Do not report speculative issues without evidence.

## Prioritize Important Problems
Avoid noisy comments.

## Be Specific
Reference exact files/functions/lines when possible.

## Be Practical
Prefer realistic improvements over theoretical perfection.

## Consider Framework Best Practices
Adapt review based on framework/language.

Examples:
- Laravel
- NestJS
- Express
- Django
- Spring
- Rails
- React
- Vue
- Go
- Rust

---

# 8. Advanced Optional Features

If data is available, also review:

## Security Diff Analysis
Compare before/after security posture.

## Architectural Drift
Detect deviation from existing architecture.

## Technical Debt Score
Estimate debt introduced.

## Change Risk Score
Estimate deployment risk.

## Performance Regression Score
Estimate performance impact.

## AI Confidence Score
Show confidence level for each issue.

---

# 9. Tone Rules

- Professional
- Direct
- Concise
- Technical
- Helpful
- Non-toxic
- No filler
- No exaggerated praise

Focus on engineering quality.
```

---

# implementation-plan.md

```md
# AI PR Review Tool - Implementation Plan

# 1. Goal

Build an AI-powered Pull Request review system that:

- Reviews PRs automatically
- Reviews commits
- Understands task requirements from PR description/issues
- Detects bugs and risks
- Suggests improvements
- Detects cross-module impact
- Reviews architecture quality
- Assists reviewers instead of replacing them

---

# 2. Core Features

## PR Review

Features:
- Analyze PR title & description
- Parse git diff
- Review changed files
- Detect risky code
- Detect missing tests
- Detect architecture violations
- Suggest improvements
- Generate review summary

---

## Commit Review

Features:
- Validate conventional commits
- Review commit structure
- Detect mixed concerns
- Suggest better commit names
- Detect oversized commits

---

## Context Understanding

Sources:
- Jira
- Linear
- GitHub Issues
- GitLab Issues
- PR description
- Commit messages

Goal:
- Understand business intent
- Reduce false positives

---

## Risk Engine

Detect:
- Security risks
- Performance regressions
- Breaking API changes
- DB migration risks
- Queue/job risks
- Deployment risks
- Cache invalidation issues

---

## Cross-Impact Analysis

Analyze:
- Shared modules
- API consumers
- Frontend impact
- WebSocket impact
- Event consumers
- Queue workers
- Database consistency

---

## Suggested Fixes

Provide:
- Inline suggestions
- Refactor suggestions
- Better patterns
- Test examples
- Safer alternatives

---

# 3. Suggested AI Models

## Recommended Stack

### Primary Reviewer

Use:
- entity["company","OpenAI","OpenAI"] GPT-5.5 / GPT-5

Why:
- Better reasoning
- Better architecture review
- Stronger code understanding
- Better long-context handling
- Better risk analysis

Best for:
- Deep reviews
- Architecture
- Security
- Cross-impact analysis

---

### Secondary Fast Reviewer

Use:
- entity["company","Google","Google"] Gemini 2.5 Flash

Why:
- Fast
- Cheap
- Good first-pass review

Best for:
- Lightweight reviews
- Quick feedback
- CI/CD automation

---

## Recommended Hybrid Strategy

### Flow

1. Gemini Flash
   - Quick scan
   - Cheap filtering
   - Basic lint review

2. GPT-5.5
   - Deep reasoning
   - Final review
   - High-risk analysis

This reduces cost while keeping high quality.

---

# 4. System Architecture

## Components

### Git Provider Integration

Support:
- GitHub
- GitLab
- Bitbucket

Responsibilities:
- Fetch PRs
- Fetch commits
- Fetch diffs
- Post comments
- Update status checks

---

### Review Engine

Responsibilities:
- Build review context
- Chunk diffs
- Prioritize files
- Call AI providers
- Merge AI outputs
- Deduplicate comments

---

### AI Orchestrator

Responsibilities:
- Prompt construction
- Token management
- Model routing
- Retry logic
- Cost optimization
- Streaming
- Response normalization

---

### Knowledge Layer

Optional:
- Project architecture docs
- Coding standards
- Existing patterns
- ADRs
- Team conventions

Used to:
- Reduce false positives
- Improve contextual understanding

---

### Comment Engine

Responsibilities:
- Inline comments
- Summary comments
- Severity labels
- Deduplication
- Smart grouping

---

# 5. Review Pipeline

## Step 1 - PR Event

Triggered by:
- PR opened
- PR synchronized
- New commits pushed

---

## Step 2 - Fetch Context

Collect:
- PR title
- Description
- Changed files
- Diff
- CI results
- Linked tasks
- Previous comments

---

## Step 3 - File Prioritization

High priority:
- Auth
- Payments
- Database
- Shared services
- Infra
- Security-sensitive files

---

## Step 4 - AI Review

Run:
- Commit review
- File review
- Security review
- Architecture review
- Risk analysis

---

## Step 5 - Post Processing

- Remove duplicates
- Rank severity
- Merge similar comments
- Reduce noisy output

---

## Step 6 - Publish Review

Publish:
- Inline comments
- Summary
- Risk report
- Suggested fixes

---

# 6. Advanced Features

## Incremental Review

Only review changed code since last review.

---

## Learning System

Learn from:
- Accepted comments
- Dismissed comments
- Team feedback

Improve future reviews.

---

## Repository Memory

Remember:
- Architecture patterns
- Naming conventions
- Existing abstractions
- Common utilities

---

## Smart Noise Reduction

Avoid:
- Repeated comments
- Low-value suggestions
- Style-only spam

---

## Security Focus Mode

Deep analysis for:
- Auth
- Payments
- Admin panels
- Public APIs

---

# 7. Tech Stack Recommendation

## Backend

Recommended:
- Laravel
- NestJS
- Go

For your background:
- Laravel + Queue + Redis is very suitable.

---

## Queue System

Use:
- Redis
- BullMQ
- Laravel Queue

Needed because:
- AI calls are expensive & slow.

---

## Storage

Store:
- PR reviews
- AI outputs
- Diff snapshots
- Risk scores
- User feedback

---

## Vector Search (Optional)

Use:
- pgvector
- Weaviate
- Qdrant

For:
- Codebase memory
- Similar issue retrieval

---

# 8. Cost Optimization

## Techniques

- Cache embeddings
- Review only changed files
- Skip generated files
- Skip lock files
- Skip vendor/node_modules
- Multi-stage AI pipeline
- Token budgeting

---

# 9. Suggested MVP

## MVP Scope

### Features
- GitHub App
- PR review
- Commit review
- Inline comments
- Summary report
- Severity classification
- GPT integration

### Ignore Initially
- Multi-repo memory
- Autonomous fixes
- Auto-refactor
- Full semantic graph
- Agentic workflows

---

# 10. Future Roadmap

## v2
- Repository memory
- Team-specific learning
- Security-focused AI
- Architecture graph
- Auto test generation

## v3
- Auto-fix suggestions
- Autonomous refactor agent
- Deployment risk prediction
- AI-generated migration plans
- PR simulation environment

---

# 11. Suggested Review Severity Rules

## Critical
- Security issue
- Data corruption
- Broken auth
- Financial risk

## High
- Production outage risk
- Breaking API
- Major performance issue

## Medium
- Maintainability issue
- Missing tests
- Risky logic

## Low
- Readability
- Minor optimization

## Nitpick
- Style
- Naming

---

# 12. Success Metrics

Track:
- Accepted review rate
- False positive rate
- Bugs caught before merge
- Review latency
- Developer satisfaction
- Deployment incident reduction

---

# 13. Recommended Next Steps

1. Build GitHub App
2. Parse PR diffs
3. Create review context builder
4. Implement GPT review pipeline
5. Add inline review comments
6. Add risk scoring
7. Add repository memory
8. Add learning feedback loop

---

# 14. Final Recommendation

Best practical architecture:

- Fast AI model for filtering
- GPT-5.5 for deep review
- Laravel queue-based orchestration
- Redis caching
- Incremental reviews
- Strong noise reduction

Focus on:
- Signal quality
- Low false positives
- Developer trust

Avoid:
- Over-commenting
- Pure lint-style feedback
- Generic AI comments
```

