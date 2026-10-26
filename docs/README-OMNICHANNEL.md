# B-Omnichannel — Unified Customer Communication Platform

## Gambaran Keseluruhan

B-Omnichannel adalah modul platform komunikasi pelanggan yang menyatukan semua saluran komunikasi dalam satu dashboard terpadu. Modul ini menghubungkan WhatsApp Business API, Email, Facebook Messenger, Instagram, Telegram, Live Chat, SMS, dan Twitter DM ke dalam satu sistem conversation management.

## Ciri-ciri Utama

### 1. Multi-Channel Integration
- **WhatsApp Business API** — Integrasi dengan WhatsApp Cloud API
- **Email** — SMTP/IMAP integration (SendGrid, Mailgun, custom SMTP)
- **Facebook Messenger** — Page integration dengan webhook
- **Instagram** — Direct messaging melalui Facebook Graph API
- **Telegram** — Bot API integration
- **Live Chat** — Widget web chat real-time
- **SMS** — Twilio/SMS gateway integration
- **Twitter DM** — Direct message integration

### 2. Unified Conversation Management
- Semua conversations dari pelbagai channel dalam satu view
- Thread-based conversation dengan context history
- Real-time message streaming
- Message threading dan replies
- Internal notes dan annotations

### 3. Smart Agent Assignment
- **Round Robin** — Distribution secara berkala
- **Least Busy** — Agent dengan conversation paling sedikit
- **Skill-Based** — Assignment berdasarkan kemahiran agent
- **Priority Routing** — Urgent conversations ke senior agents

### 4. Automation & Chatbot
- Auto-reply untuk messages luar waktu pejabat
- Quick replies dan templates
- Chatbot dengan NLP integration
- Escalation rules ke human agents
- Automated follow-ups

### 5. Analytics & Reporting
- Dashboard stats (total conversations, response times, satisfaction)
- Channel-wise performance metrics
- Agent performance tracking
- Conversation trends dan forecasting
- Message volume analysis
- SLA compliance monitoring

## Struktur Database

### Tables

#### `omnichannel_channels`
Saluran komunikasi yang terhubung ke sistem.

```sql
- id (bigint, primary key)
- tenant_id (bigint, foreign key)
- type (enum: whatsapp, email, facebook_messenger, instagram, telegram, live_chat, sms, twitter_dm)
- name (string)
- connection_status (enum: connected, disconnected, connecting, error)
- config (json) — settings per channel type
- webhook_url (string) — URL untuk receive inbound messages
- webhook_secret (string) — secret untuk verify webhook signature
- phone_number (string) — WhatsApp number / phone
- email_address (string) — Email address
- page_id (string) — Facebook page ID
- bot_token (string) — Telegram bot token
- rate_limit_per_minute (int) — Rate limiting
- is_primary (boolean) — Primary channel untuk tenant
- created_at, updated_at
```

#### `omnichannel_conversations`
Conversation/thread antara contact dan agent.

```sql
- id (bigint, primary key)
- tenant_id (bigint, foreign key)
- channel_id (bigint, foreign key)
- contact_id (bigint, foreign key)
- external_id (string) — External conversation ID dari channel
- subject (string) — Subject/conversation title
- status (enum: open, pending, archived)
- priority (enum: low, medium, high, urgent)
- assigned_agent_id (bigint) — Agent yang handle
- first_response_at (timestamp) — First agent response time
- last_message_at (timestamp) — Last message timestamp
- message_count (int) — Total messages
- resolution_time (int) — Time to resolve (minutes)
- tags (json) — Tags untuk categorization
- customer_satisfaction (enum: none, positive, neutral, negative)
- created_at, updated_at
```

#### `omnichannel_messages`
Messages dalam conversation.

```sql
- id (bigint, primary key)
- tenant_id (bigint, foreign key)
- conversation_id (bigint, foreign key)
- sender_type (enum: contact, agent, bot, system)
- sender_id (bigint) — User/bot yang send
- direction (enum: inbound, outbound)
- type (enum: text, image, video, audio, document, sticker, location, template, interactive)
- content (text) — Message content/body
- metadata (json) — Attachments, quick replies, etc.
- external_message_id (string) — ID dari channel provider
- delivery_status (enum: sent, delivered, read, failed)
- is_note (boolean) — Internal note (tidak visible ke customer)
- created_at, updated_at
```

#### `omnichannel_contacts`
Contact/customer information.

```sql
- id (bigint, primary key)
- tenant_id (bigint, foreign key)
- crm_contact_id (bigint, foreign key, nullable)
- first_name (string)
- last_name (string)
- phone (string) — Primary phone number
- email (string) — Primary email
- social_identifiers (json) — Social media IDs per channel
- avatar_url (string) — Profile picture
- company (string) — Company/organization
- tags (json) — Contact tags
- custom_fields (json) — Custom data
- conversation_count (int) — Total conversations
- last_conversation_at (timestamp) — Last conversation date
- opt_in_status (enum: opted_in, opted_out, unknown)
- created_at, updated_at
```

## API Endpoints

### Channels Management

#### List Channels
```http
GET /api/v1/omnichannel/channels
Authorization: Bearer {token}
Permission: omnichannel.view
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "tenant_id": 1,
      "type": "whatsapp",
      "name": "WhatsApp Business",
      "connection_status": "connected",
      "phone_number": "+60123456789",
      "webhook_url": "https://aibijan.com/api/v1/webhooks/whatsapp",
      "is_primary": true,
      "rate_limit_per_minute": 100
    }
  ]
}
```

#### Create Channel
```http
POST /api/v1/omnichannel/channels
Authorization: Bearer {token}
Permission: omnichannel.channels
Content-Type: application/json

{
  "type": "whatsapp",
  "name": "WhatsApp Support",
  "phone_number": "+60123456789",
  "access_token": "EAAB...",
  "webhook_verify_token": "my_verify_token"
}
```

#### Connect Channel
```http
POST /api/v1/omnichannel/channels/{id}/connect
Authorization: Bearer {token}
Permission: omnichannel.channels
```

#### Disconnect Channel
```http
POST /api/v1/omnichannel/channels/{id}/disconnect
Authorization: Bearer {token}
Permission: omnichannel.channels
```

#### Test Connection
```http
POST /api/v1/omnichannel/channels/{id}/test
Authorization: Bearer {token}
Permission: omnichannel.channels
```

### Conversations Management

#### List Conversations
```http
GET /api/v1/omnichannel/conversations?status=open&channel_id=1&page=1
Authorization: Bearer {token}
Permission: omnichannel.view
```

**Query Parameters:**
- `status` — Filter by status (open/pending/archived)
- `channel_id` — Filter by channel
- `assigned_to` — Filter by agent ID
- `priority` — Filter by priority (low/medium/high/urgent)
- `search` — Search in conversations
- `page` — Pagination

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "channel_id": 1,
      "contact_id": 5,
      "subject": "Question about product",
      "status": "open",
      "priority": "high",
      "assigned_agent_id": 3,
      "message_count": 12,
      "last_message_at": "2026-10-20T10:30:00Z",
      "first_response_at": "2026-10-20T10:35:00Z",
      "tags": ["product-inquiry", "premium"],
      "contact": {
        "id": 5,
        "first_name": "Ahmad",
        "last_name": "Ali",
        "phone": "+60123456789"
      },
      "agent": {
        "id": 3,
        "name": "Sarah Lee"
      }
    }
  ],
  "meta": {
    "total": 150,
    "per_page": 20,
    "current_page": 1,
    "last_page": 8
  }
}
```

#### Get Conversation Detail
```http
GET /api/v1/omnichannel/conversations/{id}
Authorization: Bearer {token}
Permission: omnichannel.view
```

#### Update Conversation Status
```http
PATCH /api/v1/omnichannel/conversations/{id}
Authorization: Bearer {token}
Permission: omnichannel.manage
Content-Type: application/json

{
  "status": "pending",
  "priority": "urgent",
  "tags": ["follow-up"]
}
```

#### Assign Agent
```http
POST /api/v1/omnichannel/conversations/{id}/assign
Authorization: Bearer {token}
Permission: omnichannel.manage
Content-Type: application/json

{
  "agent_id": 3
}
```

#### Add Note
```http
POST /api/v1/omnichannel/conversations/{id}/notes
Authorization: Bearer {token}
Permission: omnichannel.manage
Content-Type: application/json

{
  "note": "Customer requested callback tomorrow at 3pm"
}
```

#### Send Message
```http
POST /api/v1/omnichannel/conversations/{id}/send
Authorization: Bearer {token}
Permission: omnichannel.manage
Content-Type: application/json

{
  "content": "Thank you for your inquiry. Our team will assist you shortly.",
  "type": "text",
  "metadata": {
    "quick_reply": true
  }
}
```

#### Upload Media
```http
POST /api/v1/omnichannel/conversations/{id}/upload
Authorization: Bearer {token}
Permission: omnichannel.manage
Content-Type: multipart/form-data

{
  "file": {uploaded file},
  "type": "image" // or: image, video, audio, document
}
```

#### Get Messages
```http
GET /api/v1/omnichannel/conversations/{id}/messages?limit=50&before=timestamp
Authorization: Bearer {token}
Permission: omnichannel.view
```

### Statistics & Analytics

#### Dashboard Stats
```http
GET /api/v1/omnichannel/conversations/analytics/dashboard
Authorization: Bearer {token}
Permission: omnichannel.reports
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_conversations": 1250,
    "open_conversations": 45,
    "archived_conversations": 1200,
    "avg_first_response_minutes": 8.5,
    "avg_resolution_hours": 2.3,
    "satisfaction_score": 4.5,
    "conversations_today": 32,
    "messages_today": 187
  }
}
```

#### Channel Stats
```http
GET /api/v1/omnichannel/channels/stats
Authorization: Bearer {token}
Permission: omnichannel.reports
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "channel_type": "whatsapp",
      "total_conversations": 500,
      "active_conversations": 20,
      "avg_response_minutes": 5.2,
      "satisfaction_score": 4.7
    },
    {
      "channel_type": "email",
      "total_conversations": 350,
      "active_conversations": 15,
      "avg_response_minutes": 45.0,
      "satisfaction_score": 4.2
    }
  ]
}
```

#### Agent Performance
```http
GET /api/v1/omnichannel/conversations/analytics/agent-performance?date_from=2026-10-01&date_to=2026-10-20
Authorization: Bearer {token}
Permission: omnichannel.reports
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "agent_id": 3,
      "agent_name": "Sarah Lee",
      "total_conversations": 125,
      "resolved_conversations": 110,
      "avg_response_minutes": 6.5,
      "avg_resolution_hours": 1.8,
      "satisfaction_score": 4.8,
      "customer_rating_count": 45
    }
  ]
}
```

#### Conversation Trends
```http
GET /api/v1/omnichannel/conversations/analytics/trends?period=7days
Authorization: Bearer {token}
Permission: omnichannel.reports
```

### Webhooks (Public)

#### WhatsApp Webhook
```http
POST /api/v1/webhooks/whatsapp
Content-Type: application/json

{
  "object": "whatsapp_business_account",
  "entry": [
    {
      "id": "WA_ID",
      "changes": [
        {
          "value": {
            "messaging_product": "whatsapp",
            "contacts": [{ "wa_id": "+60123456789" }],
            "messages": [{
              "from": "+60123456789",
              "id": "wamid.ID",
              "timestamp": "1634567890",
              "text": { "body": "Hello, I need help" },
              "type": "text"
            }]
          }
        }
      ]
    }
  ]
}
```

#### Facebook Webhook
```http
POST /api/v1/webhooks/facebook
Content-Type: application/json

{
  "object": "instagram",
  "entry": [{
    "id": "PAGE_ID",
    "changes": [{
      "field": "messages",
      "value": {
        "messaging": [{
          "sender": { "id": "USER_ID" },
          "message": {
            "text": "Hello"
          }
        }]
      }
    }]
  }]
}
```

#### Telegram Webhook
```http
POST /api/v1/webhooks/telegram
Content-Type: application/json

{
  "update_id": 123456789,
  "message": {
    "message_id": 1,
    "from": {
      "id": 123456789,
      "is_bot": false,
      "first_name": "John",
      "username": "john_doe"
    },
    "chat": {
      "id": 123456789,
      "first_name": "John",
      "type": "private"
    },
    "date": 1634567890,
    "text": "Hello"
  }
}
```

## Configuration

### config/omnichannel.php

```php
return [
    // WhatsApp Business API
    'whatsapp' => [
        'api_version' => 'v17.0',
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
        'webhook_url' => env('WHATSAPP_WEBHOOK_URL'),
        'max_message_length' => 4000,
        'rate_limit_per_minute' => 100,
    ],

    // Email (SMTP)
    'email' => [
        'driver' => env('MAIL_MAILER', 'smtp'),
        'host' => env('MAIL_HOST', 'smtp.gmail.com'),
        'port' => env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@aibijan.com'),
        'from_name' => env('MAIL_FROM_NAME', 'AI-Bijan Support'),
    ],

    // Facebook Messenger
    'facebook' => [
        'app_id' => env('FACEBOOK_APP_ID'),
        'app_secret' => env('FACEBOOK_APP_SECRET'),
        'access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN'),
        'webhook_verify_token' => env('FACEBOOK_WEBHOOK_VERIFY_TOKEN'),
        'version' => 'v17.0',
    ],

    // Instagram
    'instagram' => [
        'app_id' => env('INSTAGRAM_APP_ID'),
        'app_secret' => env('INSTAGRAM_APP_SECRET'),
        'access_token' => env('INSTAGRAM_ACCESS_TOKEN'),
        'webhook_verify_token' => env('INSTAGRAM_WEBHOOK_VERIFY_TOKEN'),
    ],

    // Telegram
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
        'allowed_chats' => [], // Empty = allow all
    ],

    // Live Chat
    'live_chat' => [
        'widget_url' => env('LIVE_CHAT_WIDGET_URL', '/live-chat/widget'),
        'websocket_url' => env('LIVE_CHAT_WS_URL', 'ws://localhost:6001'),
        'auto_assign' => true,
        'buffer_time_seconds' => 30, // Time before reassign if no response
    ],

    // SMS
    'sms' => [
        'driver' => env('SMS_DRIVER', 'twilio'),
        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from_number' => env('TWILIO_FROM_NUMBER'),
        ],
    ],

    // Conversation Settings
    'conversation' => [
        'auto_close_after_hours' => 48, // Auto-close after 48h of inactivity
        'buffer_time_seconds' => 60, // Buffer before new conversation
        'max_messages_per_page' => 50,
        'enable_tags' => true,
        'enable_sla' => true,
    ],

    // Agent Settings
    'agent' => [
        'assignment_strategy' => 'round_robin', // round_robin, least_busy, skill_based
        'max_concurrent_conversations' => 10,
        'auto_assign_timeout' => 300, // Seconds before auto-assign
        'working_hours' => [
            'start' => '09:00',
            'end' => '18:00',
            'timezone' => 'Asia/Kuala_Lumpur',
        ],
    ],

    // Automation & Chatbot
    'automation' => [
        'enabled' => true,
        'auto_reply_enabled' => true,
        'business_hours_only' => true,
        'escalation_threshold_minutes' => 15, // Escalate if no response in 15min
        'chatbot_provider' => 'default', // default, dialogflow, watson
    ],
];
```

## Integration Points

### B-CRM Integration
- **Contact Sync** — Contacts dari Omnichannel sync ke CRM
- **Lead Tracking** — Conversations track sebagai lead interactions
- **Deal Influence** — Conversation sentiment影响 deal probability

### B-Helpdesk Integration
- **Ticket Creation** — Conversations boleh convert ke helpdesk tickets
- **Context Transfer** — Full conversation history transfer ke ticket
- **SLA Tracking** — SLA policies shared dengan Helpdesk module

### B-Marketing Integration
- **Campaign Tracking** — Messages relate ke marketing campaigns
- **Opt-in Management** — Consent tracking untuk marketing messages
- **Segment Updates** — Conversation behavior update marketing segments

### B-Analytics Integration
- **Data Export** — Omnichannel data export ke analytics engine
- **Custom Dashboards** — BI tools access ke conversation data
- **Predictive Analytics** — Forecast conversation volumes

## Workflow Example

### 1. Customer sends message via WhatsApp
```
Customer → WhatsApp → Webhook → InboundWebhookService
→ Create/Find Contact → Create Conversation → Assign Agent
→ Notify Agent (in-app/email)
```

### 2. Agent responds
```
Agent → API `/conversations/{id}/send` → MessageService
→ Save message (outbound) → Update conversation
→ Send via channel adapter → Customer
```

### 3. Auto-reply (outside business hours)
```
Inbound message → Check business hours
→ Outside hours → Auto-reply with expected response time
→ Mark conversation as pending
```

### 4. Escalation
```
No response > 15 minutes → Escalate to senior agent
→ Update priority to high
→ Notify team lead
```

### 5. Resolution
```
Agent marks resolved → Send satisfaction survey
→ Customer rates → Update satisfaction score
→ After 48h inactivity → Auto-archive
```

## Architecture

```
┌─────────────────────────────────────────────────┐
│              Customer Channels                  │
│  WhatsApp | Email | FB | IG | Telegram | SMS   │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│           Webhook Receiver                      │
│         WebhookController                       │
│   - Verify signatures                           │
│   - Route to channel handler                    │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│         InboundWebhookService                   │
│   - Process per channel type                    │
│   - Extract message content                     │
│   - Identify contact                            │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│          ConversationService                    │
│   - Create/find conversation                    │
│   - Assign agent (round-robin/least-busy)       │
│   - Apply automation rules                      │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│           Agent Dashboard                       │
│   - Unified inbox                               │
│   - Conversation management                     │
│   - Quick replies & templates                   │
│   - Internal notes                              │
└──────────────┬──────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────┐
│          MessageService                         │
│   - Send outbound messages                      │
│   - Channel adapter routing                     │
│   - Track delivery status                       │
└─────────────────────────────────────────────────┘
```

## Testing

### Unit Tests
```bash
php artisan test --filter=OmnichannelTest
```

### Channel Connection Test
```bash
curl -X POST http://localhost/api/v1/omnichannel/channels/{id}/test \
  -H "Authorization: Bearer {token}"
```

### Webhook Test (WhatsApp)
```bash
curl -X POST http://localhost/api/v1/webhooks/whatsapp \
  -H "Content-Type: application/json" \
  -d '{
    "object": "whatsapp_business_account",
    "entry": [{
      "changes": [{
        "value": {
          "messaging_product": "whatsapp",
          "contacts": [{"wa_id": "+60123456789"}],
          "messages": [{
            "from": "+60123456789",
            "id": "wamid.test",
            "timestamp": "1634567890",
            "text": { "body": "Test message" },
            "type": "text"
          }]
        }
      }]
    }]
  }'
```

## Security Considerations

1. **Webhook Verification** — All webhooks verified menggunakan signature/secrets
2. **Rate Limiting** — Per-channel rate limits untuk prevent abuse
3. **Data Encryption** — Sensitive credentials (tokens, passwords) encrypted di database
4. **Access Control** — Permission-based access ke conversations
5. **GDPR Compliance** — Opt-in/opt-out management, data retention policies
6. **Audit Trail** — All agent actions logged dengan IP dan user agent

## Future Enhancements

- [ ] AI-powered auto-response suggestions
- [ ] Voice call integration
- [ ] Video message support
- [ ] Multi-language auto-detection and translation
- [ ] Advanced chatbot builder dengan visual flow editor
- [ ] WhatsApp template message approval workflow
- [ ] Broadcast/mass messaging campaigns
- [ ] Customer self-service portal
- [ ] Integration with third-party CRMs (Salesforce, HubSpot)
- [ ] Real-time translation for cross-language conversations
