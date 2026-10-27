# Team AI Workspace

Team AI Workspace adapts the team asset management idea in [Tencent TeamAI CLI](https://github.com/Tencent/teamai-cli) to AI Bijan's tenant-based Laravel platform. TeamAI CLI is MIT-licensed; this integration is an independent implementation and does not bundle its source code.

## What is available

- Connect a GitHub repository by owner, repository, and branch; a fine-grained read-only GitHub token is optional for private repositories.
- Sync text assets from categorized folders: `skills/`, `rules/`, `instructions/`, `claudemd/`, `agents/`, `hooks/`, `workflows/`, `.teamai/workflows/`, `mcp/`, `.teamai/mcp/`, `culture/`, `culture.md`, `learnings/`, `wiki/`, `teamwiki/`, `docs/`, `models/`, `packages/`, and `teamai.yaml`.
- Browse imported assets by category in `/app/team-ai`.
- Existing AI Bijan modules remain responsible for agent execution, MCP tools and approvals, workflows, and AI model usage.

GitHub access tokens are encrypted at rest and never returned by the API. Repository URLs are not accepted from clients; requests go only to `api.github.com`. Sync is bounded to 50 files, 64 KB per file, and 1 MB total. `.env` files, credential directories, key files, and GitHub CI workflows are excluded. Credential-like values in imported config files are replaced with `[REDACTED]`.

## Configuration and authorization

The page and API reuse `agent_studio.view` and `agent_studio.manage` permissions. Repository and asset records are tenant-scoped. Run database migrations after deployment.

## Current boundaries

- The first implementation supports GitHub repositories only. It imports the listed text assets into AI Bijan; it does not install or push them into local Claude Code, Cursor, Copilot CLI, or other developer harnesses.
- `env/` is deliberately not imported because environment files may contain live credentials. Manage secrets separately and do not commit them to the team repository.
- Imported hooks, workflows, MCP configuration, models, and team context are stored and browsable only. They are not executed or activated automatically. Connect them through the existing Agent Studio, MCP Studio and Workflow modules after review.
- The `learnings` and `wiki` categories are asset folders, not semantic memory, codebase graph, or an automatically generated team wiki.
- Team usage can be reviewed using existing AI Gateway metering. TeamAI-compatible session analytics and scorers are not included yet.
- Sync should be run manually after repository changes; scheduled/background sync, branch webhooks, conflict review, audit history, and multi-provider Git hosting are future work.

Do not grant write or administration scopes to a GitHub token. Review imported MCP definitions and agent instructions before enabling them.
