# 🏢 PELAN IMPLEMENTASI — VIRTUAL OFFICE MODULE

**Tarikh:** 2026-10-19  
**Prioriti:** 🔴 TINGGI (Task #1)  
**Architecture:** Module dalam `ai-bijan-saas/app/Modules/VirtualOffice/`  
**Estimasi:** 6-8 minggu  

---

## 📋 RINGKASAN PRODUK

Bijan Virtual Office ialah SaaS multi-tenant di mana setiap perniagaan mendapat **pasukan agent AI** yang:
- Bekerja ikut jadual (scheduled tasks)
- Melapor di dashboard & WhatsApp/Telegram
- Hanya bertanya pemilik untuk keputusan penting
- Mengurus integrasi (WhatsApp, Google Drive, Shopee, TikTok, dll)

### Business Model (4 Tier)

| Pelan | Harga/Bulan | Agent | Kredit AI | Sambungan | Untuk |
|-------|-------------|-------|-----------|-----------|-------|
| **Cuba** | RM 0 (14 hari) | 3 | Kecil, sekali | WA atau Telegram | Rasa produk |
| **Asas** | RM 99 | 3 | Termasuk | 3 | Perniagaan seorang |
| **Bisnes** | RM 249 | 8 | Lebih besar | Tanpa had | PKS 2-20 orang |
| **Agensi** | RM 599 | 8/klien, 5 klien | Dikongsi | Tanpa had | Agensi urus banyak jenama |

---

## 🏗️ STRUKTUR KOD

```
ai-bijan-saas/
└── app/
    └── Modules/
        └── VirtualOffice/
            ├── Config/
            │   └── config.php           # Module configuration
            ├── Database/
            │   ├── migrations/          # 8-10 migration files
            │   └── seeders/             # Agent templates, settings
            ├── Models/
            │   ├── VirtualOffice.php    # Main VO model per tenant
            │   ├── Agent.php            # AI agent definition
            │   ├── AgentSchedule.php    # Scheduled tasks
            │   ├── AgentMemory.php      # Persistent memory store
            │   ├── Connector.php        # API connector config
            │   ├── ApprovalRequest.php  # Peti Keputusan items
            │   └── TokenUsage.php       # Credit tracking
            ├── Http/
            │   ├── Controllers/
            │   │   ├── DashboardController.php
            │   │   ├── AgentsController.php
            │   │   ├── ConnectorsController.php
            │   │   ├── ApprovalsController.php
            │   │   └── WebhookController.php  # Handle incoming webhooks
            │   └── Middleware/
            │       └── VerifyConnectorAuth.php
            ├── Services/
            │   ├── AgentRuntimeService.php    # Execute agent jobs
            │   ├── AIGatewayService.php       # Route to OpenAI/Anthropic/Gemini
            │   ├── ConnectorService.php       # Manage external APIs
            │   ├── SchedulerService.php       # Agent scheduling
            │   ├── MemoryService.php          # Agent memory management
            │   ├── ApprovalService.php        # Peti Keputusan logic
            │   └── ContentGeneratorService.php # Higgsfield integration
            ├── Jobs/
            │   ├── RunAgentJob.php            # Dispatch agent execution
            │   ├── ProcessWebhookJob.php      # Handle webhook events
            │   ├── GenerateContentJob.php     # Image/video generation
            │   └── SendNotificationJob.php    # WA/Telegram/email alerts
            ├── Routes/
            │   └── api.php                    # Module API routes
            ├── Events/
            │   ├── AgentTaskCompleted.php
            │   ├── ApprovalRequested.php
            │   └── ConnectorConnected.php
            ├── Listeners/
            │   └── ...                         # Event listeners
            └── VirtualOfficeServiceProvider.php # Register module
```

---

## 📊 DATABASE SCHEMA

### Core Tables

```sql
-- 1. virtual_offices (1 per tenant)
virtual_offices:
  - id (PK)
  - tenant_id (FK → tenants)
  - name
  - status (active/inactive/suspended)
  - plan_tier (cuba/asas/bisnes/agensi)
  - max_agents (integer)
  - ai_credit_balance (decimal)
  - ai_credit_limit (decimal)
  - settings (JSON: thresholds, preferences)
  - created_at, updated_at

-- 2. agents
agents:
  - id (PK)
  - virtual_office_id (FK → virtual_offices)
  - name
  - role (CMO/CFO/COO/CSO/Assistant/Specialist)
  - system_prompt (text)
  - model_provider (openai/anthropic/google/higgsfield)
  - model_name (gpt-4/claude-sonnet/gemini-pro/etc)
  - max_tokens_per_day (integer)
  - tools_allowed (JSON: array of tool names)
  - status (active/paused/disabled)
  - created_at, updated_at

-- 3. agent_schedules
agent_schedules:
  - id (PK)
  - agent_id (FK → agents)
  - cron_expression
  - trigger_type (cron/event/webhook)
  - payload (JSON: task parameters)
  - last_run_at
  - next_run_at
  - status (active/paused/completed)
  - created_at, updated_at

-- 4. agent_memories
agent_memories:
  - id (PK)
  - agent_id (FK → agents)
  - type (personal/shared/context/decision)
  - key (string: identifier)
  - value (text: content)
  - metadata (JSON: tags, source, timestamp)
  - expires_at (nullable)
  - created_at, updated_at

-- 5. connectors
connectors:
  - id (PK)
  - virtual_office_id (FK → virtual_offices)
  - provider (whatsapp/telegram/google_drive/shopee/tiktok/toyyibpay/meta)
  - status (connected/disconnected/error)
  - oauth_token (encrypted)
  - oauth_refresh_token (encrypted)
  - token_expires_at
  - config (JSON: provider-specific settings)
  - last_sync_at
  - created_at, updated_at

-- 6. approval_requests (Peti Keputusan)
approval_requests:
  - id (PK)
  - virtual_office_id (FK → virtual_offices)
  - agent_id (FK → agents)
  - request_type (send_message/create_invoice/update_price/post_content/etc)
  - risk_level (low/medium/high/critical)
  - context (JSON: what triggered this)
  - proposed_action (JSON: what agent wants to do)
  - status (pending/approved/rejected/modified)
  - approved_by (FK → users, nullable)
  - approved_at
  - modifications (JSON: if owner changed the action)
  - created_at, updated_at

-- 7. token_usages
token_usages:
  - id (PK)
  - virtual_office_id (FK → virtual_offices)
  - agent_id (FK → agents, nullable)
  - provider (openai/anthropic/google/higgsfield)
  - model_used
  - prompt_tokens
  - completion_tokens
  - total_tokens
  - cost_usd (decimal)
  - purpose (task/webhook/notification/content_generation)
  - reference_id (string: links to related record)
  - created_at

-- 8. content_generations (Higgsfield)
content_generations:
  - id (PK)
  - virtual_office_id (FK → virtual_offices)
  - agent_id (FK → agents)
  - type (image/video/social_post/ad_creative)
  - prompt
  - status (queued/processing/completed/failed)
  - output_url (string: generated file URL)
  - metadata (JSON: dimensions, format, etc.)
  - error_message (nullable)
  - created_at, updated_at
```

---

## 🔧 IMPLEMENTATION STEPS

### Minggu 1: Foundation & Database

**Hari 1-2: Setup Module Structure**
```bash
# Create module directory structure
mkdir -p app/Modules/VirtualOffice/{Config,Database/Migrations,Database/Seeders,Models,Http/{Controllers,Middleware},Services,Jobs,Routes,Events,Listeners}

# Create service provider
touch app/Modules/VirtualOffice/VirtualOfficeServiceProvider.php
```

**Hari 3-4: Create Migrations**
- Migration 1: `virtual_offices` table
- Migration 2: `agents` table
- Migration 3: `agent_schedules` table
- Migration 4: `agent_memories` table
- Migration 5: `connectors` table
- Migration 6: `approval_requests` table
- Migration 7: `token_usages` table
- Migration 8: `content_generations` table

**Hari 5: Create Models**
- All Eloquent models with relationships
- Casts for JSON fields
- Accessors/mutators
- Scopes for common queries

### Minggu 2: AI Gateway & Agent Runtime

**Hari 1-2: AIGatewayService**
```php
// Pseudo-code structure
class AIGatewayService
{
    public function call(Agent $agent, string $prompt, array $context): Response
    {
        // 1. Check credit balance
        // 2. Select provider based on agent config & fallback chain
        // 3. Build message with agent memory + context
        // 4. Call provider API (OpenAI/Anthropic/Gemini)
        // 5. Log token usage
        // 6. Return response
    }
    
    private function selectProvider(Agent $agent): Provider
    {
        // Primary → Fallback 1 → Fallback 2
        // Based on agent.model_provider preference
    }
}
```

**Hari 3-4: AgentRuntimeService & RunAgentJob**
```php
class RunAgentJob implements ShouldQueue
{
    public function handle(AgentRuntimeService $runtime)
    {
        // 1. Load agent context
        // 2. Load agent memory (personal + shared)
        // 3. Execute agent's task/tools
        // 4. If risky action → create ApprovalRequest
        // 5. If safe action → execute directly
        // 6. Save results to memory
        // 7. Log token usage
        // 8. Send notification if configured
    }
}
```

**Hari 5: Agent Memory System**
- MemoryService for CRUD operations
- Personal memory (agent-specific)
- Shared memory (cross-agent knowledge)
- Context memory (recent decisions, tasks)
- Decision memory (approved/rejected actions)

### Minggu 3: Connector System

**Hari 1-2: Base Connector Architecture**
```php
interface ConnectorInterface
{
    public function connect(array $credentials): bool;
    public function disconnect(): bool;
    public function validateConnection(): bool;
    public function getWebhookUrl(): string;
    public function handleWebhook(array $payload): void;
    public function listActions(): array;
    public function executeAction(string $action, array $params): mixed;
}
```

**Hari 3-4: Implement First Connectors**
- WhatsApp Business Platform (Cloud API)
- Telegram Bot API
- Google Drive & Sheets (OAuth)

**Hari 5: Webhook Handler**
- WebhookController to route incoming events
- Parse provider-specific payloads
- Dispatch to appropriate Jobs
- Update connector last_sync_at

### Minggu 4: Peti Keputusan (Approval Inbox)

**Hari 1-2: ApprovalService**
```php
class ApprovalService
{
    public function requestApproval(Agent $agent, string $type, array $action): ApprovalRequest
    {
        // Create approval request with risk assessment
        // Notify owner via dashboard + WhatsApp/email
        // Set timeout for auto-rejection
    }
    
    public function approve(ApprovalRequest $request, User $owner, ?array $modifications = null): bool
    {
        // Mark as approved
        // If modified, save modifications
        // Trigger the actual action
        // Log in audit trail
    }
}
```

**Hari 3-4: Approval UI (Filament)**
- Admin panel page for pending approvals
- List view with filters (status, risk level, date)
- Detail view with context + proposed action
- Approve/Reject/Modify buttons
- History view (past approvals)

**Hari 5: Notification Integration**
- Send approval requests via WhatsApp/Telegram/email
- Allow quick approve/reject via message reply
- Dashboard notification badge

### Minggu 5: Scheduling System

**Hari 1-2: SchedulerService**
```php
class SchedulerService
{
    public function scheduleAgent(Agent $agent, string $cron, array $payload): AgentSchedule
    {
        // Create schedule entry
        // Calculate next_run_at from cron
        // Register with Laravel scheduler
    }
    
    public function checkDueSchedules(): void
    {
        // Find schedules where next_run_at <= now
        // Dispatch RunAgentJob for each
        // Update next_run_at
    }
}
```

**Hari 3-4: Laravel Scheduler Integration**
- Add module commands to `app/Console/Kernel.php`
- `php artisan schedule:run` every minute via cron
- Queue background job dispatching

**Hari 5: Event-Based Triggers**
- Connectors trigger agent runs on webhook events
- Events: new message, order received, stock low, etc.
- Event subscribers in `Events/` directory

### Minggu 6: Higgsfield Integration (Content Creator)

**Hari 1-2: Research Higgsfield API**
- Understand image generation endpoints
- Understand video generation endpoints
- Authentication method
- Rate limits & pricing

**Hari 3-4: ContentGeneratorService**
```php
class ContentGeneratorService
{
    public function generateImage(Agent $agent, string $prompt, array $options): ContentGeneration
    {
        // Create ContentGeneration record (status: queued)
        // Dispatch GenerateContentJob
        // Return ID for tracking
    }
    
    public function generateVideo(Agent $agent, string $prompt, array $options): ContentGeneration
    {
        // Similar to image but for video
        // May take longer, use queue
    }
}
```

**Hari 5: Content Approval Flow**
- Generated content goes to Peti Keputusan
- Owner approves before posting
- Store generated files in Cloudflare R2 / AWS S3

### Minggu 7-8: Billing, Testing & Polish

**Hari 1-2: Token Usage & Billing**
- Track all AI calls in `token_usages` table
- Daily credit deduction per tenant
- Alert when balance < threshold
- Block when balance = 0 (except critical notifications)

**Hari 3-4: Testing**
- Unit tests for services
- Feature tests for API endpoints
- Integration tests for connectors
- Test credit deduction flow
- Test approval workflow

**Hari 5: Documentation**
- Inline code documentation
- API documentation (OpenAPI spec)
- Setup guide for new connectors
- Owner guide for configuring agents

**Hari 6-7: Bug fixes & optimization**
- Fix any issues found during testing
- Optimize database queries
- Add indexes for performance

---

## 🔌 CONNECTOR ROADMAP

### Fasa 1 (Minggu 3-4):
| Connector | Status | Complexity | Notes |
|-----------|--------|------------|-------|
| **WhatsApp** | Planned | High | Meta Business verification needed |
| **Telegram** | Planned | Low | Simple bot API |
| **Google Drive** | Planned | Medium | OAuth, limited scopes |

### Fasa 2 (Minggu 5-6):
| Connector | Status | Complexity | Notes |
|-----------|--------|------------|-------|
| **Shopee** | Planned | High | Partner registration needed |
| **TikTok Shop** | Planned | High | Audit application needed |
| **Meta (FB/IG)** | Planned | High | App Review needed |

### Fasa 3 (Minggu 7-8):
| Connector | Status | Complexity | Notes |
|-----------|--------|------------|-------|
| **ToyyibPay** | Planned | Medium | Payment link generation |
| **Billplz** | Planned | Medium | Alternative payment gateway |
| **Gmail** | Planned | High | OAuth, Gmail API scopes |

---

## 💰 COST TRACKING

### Per-Agent Token Tracking
```php
// Example: CMO agent uses GPT-4 for marketing analysis
$usage = [
    'provider' => 'openai',
    'model' => 'gpt-4o',
    'prompt_tokens' => 2500,
    'completion_tokens' => 800,
    'total_tokens' => 3300,
    'cost_usd' => 0.0165, // ~$0.005/1K prompt + $0.015/1K completion
];

// Deduct from tenant credit
$tenant->decrement('ai_credit_balance', $usage['cost_usd']);
```

### Pricing vs Cost Analysis (Estimated)
| Pelan | Harga/Bulan | Est. AI Cost/Bulan | Margin |
|-------|-------------|-------------------|--------|
| Cuba | RM 0 | RM 0 (trial only) | - |
| Asas | RM 99 (~$21) | RM 30-50 | 50-75% |
| Bisnes | RM 249 (~$53) | RM 80-120 | 50-70% |
| Agensi | RM 599 (~$128) | RM 200-300 | 50-65% |

*Margin boleh dioptimumkan dengan BYOK (Bring Your Own Key)*

---

## ⚡ PERFORMANCE CONSIDERATIONS

### Queue Strategy
- **High priority:** Approval notifications (immediate)
- **Normal priority:** Agent scheduled tasks (Horizon queue)
- **Low priority:** Content generation, analytics (delayed queue)

### Memory Management
- Personal memory: Keep last 100 entries per agent
- Shared memory: TTL-based expiry (7-30 days)
- Context memory: Rotate daily
- Decision memory: Archive monthly, keep 6 months

### Caching
- Cache connector validation results (5 min TTL)
- Cache agent config (1 hour TTL)
- Cache memory lookups (Redis, 10 min TTL)

---

## 🎯 SUCCESS CRITERIA

### Minimum Viable Product (MVP) - Minggu 4
- [ ] Agent can be created & configured
- [ ] AI Gateway works with at least 1 provider
- [ ] Agent can execute simple tasks (report generation)
- [ ] Token usage is tracked correctly
- [ ] Basic dashboard shows agent status

### Production Ready - Minggu 8
- [ ] All 4 connectors working (WA, TG, GD, Shopee)
- [ ] Peti Keputusan approval flow tested
- [ ] Credit billing accurate & automated
- [ ] Scheduling works for 10+ concurrent agents
- [ ] Error handling & retry logic robust
- [ ] All tests passing (>80% coverage)
- [ ] Documentation complete

---

## 📝 NOTES PENTING

1. **ERPNext bukan dependency** — Semua ini berdiri sendiri dalam Laravel
2. **AI tidak tulis terus ke DB** — Tindakan berisiko melalui Peti Keputusan
3. **Multi-tenant isolation** — Semua query scoped ke tenant_id
4. **BYOK option** — Pelanggan boleh guna API key sendiri untuk kurangkan kos
5. **Malaysia-first** — Integrasi tempatan (ToyyibPay, e-Invois) perlu dipertimbangkan

---

*Dokumen ini adalah pelan pelaksanaan, bukan bukti setiap fungsi telah dibina.*
*Status dikemas kini secara berkala semasa pembangunan.*
