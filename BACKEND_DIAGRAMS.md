# Backend Architecture Diagrams

## System Architecture

```mermaid
graph TB
    subgraph Frontend["Frontend (React + Vite)"]
        FE["http://localhost:5173"]
    end
    
    subgraph Middleware["Middleware Layer"]
        CORS["CORS Handler"]
        AUTH["Auth Guard<br/>Sanctum"]
        RATE["Rate Limiter"]
        VALIDATE["Validation"]
    end
    
    subgraph Backend["Laravel Backend<br/>http://localhost:8000"]
        ROUTER["Router<br/>api.php"]
        CTRLS["Controllers<br/>18 Controllers"]
        MODELS["Models<br/>11 Eloquent Models"]
        HELPERS["Helpers & Traits"]
    end
    
    subgraph Database["PostgreSQL<br/>Render Managed"]
        DB["25 Tables<br/>Normalized Schema<br/>With Indexes"]
    end
    
    subgraph Cache["Cache & Session"]
        FILE["File-Based Storage<br/>storage/framework/"]
    end
    
    FE -->|HTTP Requests<br/>JSON| CORS
    CORS -->|CORS Check| RATE
    RATE -->|Rate Limit| AUTH
    AUTH -->|Tokenable| VALIDATE
    VALIDATE -->|Valid Request| ROUTER
    ROUTER -->|Route Match| CTRLS
    CTRLS -->|Eloquent ORM| MODELS
    MODELS -->|Query Builder| DB
    MODELS -->|Store/Retrieve| FILE
    CTRLS -->|JSON Response| FE
    
    style FE fill:#61dafb,color:#000
    style Backend fill:#ff2d20,color:#fff
    style Database fill:#336791,color:#fff
    style Cache fill:#4db33d,color:#fff
```

## Data Flow - Login

```mermaid
sequenceDiagram
    actor User
    participant Frontend
    participant Backend
    participant Database
    
    User->>Frontend: Enter email/password
    Frontend->>Frontend: Validate form
    Frontend->>Backend: POST /api/login (unsafe: throttle 5/min)
    Backend->>Backend: Verify CORS origin
    Backend->>Backend: Validate input
    Backend->>Database: Query: SELECT * FROM users WHERE email=?
    Database-->>Backend: User record (if exists)
    Backend->>Backend: Hash::check(password)
    alt Password Valid
        Backend->>Backend: Generate Sanctum token
        Backend-->>Frontend: 200 OK + token
        Frontend->>Frontend: Store token in localStorage
        Frontend->>Frontend: Update auth state
        Frontend->>User: Redirect to dashboard
    else Invalid
        Backend-->>Frontend: 401 Unauthorized
        Frontend->>User: Show error message
    end
```

## Data Flow - Fetch Articles

```mermaid
sequenceDiagram
    actor User
    participant Frontend
    participant Backend
    participant Database
    
    User->>Frontend: View homepage
    Frontend->>Frontend: Load & parse token
    Frontend->>Backend: GET /api/articles/public (public - no token)
    Backend->>Backend: Verify CORS origin
    Backend->>Database: SELECT * FROM articles WHERE status='published'<br/>+ eager load author, categories, tags
    Database-->>Backend: Article rows + relations
    Backend->>Backend: Format for API (append image URLs, counts)
    Backend->>Frontend: 200 OK + paginated JSON
    Frontend->>Frontend: Parse response
    Frontend->>User: Render article list on page
```

## Controller Responsibility Map

```mermaid
graph LR
    subgraph Auth["Authentication<br/>AuthController"]
        A1["loginApi()"]
        A2["registerApi()"]
    end
    
    subgraph Content["Content Management<br/>ArticleController"]
        C1["publicIndex()"]
        C2["show()"]
        C3["store()"]
        C4["update()"]
        C5["destroy()"]
    end
    
    subgraph Taxonomy["Taxonomy<br/>CategoryController<br/>TagController"]
        T1["index()"]
        T2["show()"]
    end
    
    subgraph Contact["Contact<br/>ContactController"]
        CO1["sendFeedback()"]
        CO2["subscribe()"]
        CO3["requestCoverage()"]
    end
    
    subgraph Users["User Management<br/>UserController"]
        U1["me()"]
        U2["update()"]
    end
    
    subgraph Other["Other<br/>DraftController<br/>AuthorController"]
        O1["All CRUD ops"]
    end
    
    style Auth fill:#ff6b6b
    style Content fill:#4ecdc4
    style Taxonomy fill:#45b7d1
    style Contact fill:#f9ca24
    style Users fill:#6c5ce7
    style Other fill:#a29bfe
```

## Database Entity Relationship

```mermaid
erDiagram
    USERS ||--o| AUTHOR : has
    USERS ||--o{ ARTICLE_INTERACTION : makes
    
    ARTICLES ||--o| AUTHOR : "written by"
    ARTICLES ||--o{ ARTICLE_CATEGORY : "belongs to"
    ARTICLES ||--o{ ARTICLE_TAG : "tagged with"
    ARTICLES ||--o{ ARTICLE_INTERACTION : receives
    
    CATEGORIES ||--o{ ARTICLE_CATEGORY : contains
    TAGS ||--o{ ARTICLE_TAG : contains
    
    AUTHOR ||--o{ ARTICLES : writes
    AUTHOR ||--o| USERS : "linked to"
    
    DRAFTS ||--o| AUTHOR : "author"
    
    PERSONAL_ACCESS_TOKENS ||--o| USERS : "for"
    
    USERS {
        int id PK
        string name
        string email UK
        string password
        string role
        datetime email_verified_at
        datetime created_at
    }
    
    ARTICLES {
        int id PK
        string title
        string slug UK
        text excerpt
        text content
        string featured_image
        string status "draft/published"
        int author_id FK
        datetime published_at
        datetime created_at
    }
    
    CATEGORIES {
        int id PK
        string name
        string slug UK
        text description
        datetime created_at
    }
    
    TAGS {
        int id PK
        string name
        string slug UK
    }
    
    AUTHOR {
        int id PK
        int user_id FK
        text bio
        datetime created_at
    }
    
    DRAFTS {
        int id PK
        string title
        text content
        int author_id FK
        datetime created_at
    }
    
    ARTICLE_INTERACTION {
        int id PK
        int article_id FK
        int user_id FK
        string type "liked/viewed/etc"
        datetime created_at
    }
    
    ARTICLE_CATEGORY {
        int article_id FK
        int category_id FK
    }
    
    ARTICLE_TAG {
        int article_id FK
        int tag_id FK
    }
    
    PERSONAL_ACCESS_TOKENS {
        int id PK
        int user_id FK
        string name
        text token UK
        text abilities
        datetime created_at
    }
```

## API Route Hierarchy

```mermaid
graph TB
    ROOT["API Root<br/>/api"]
    
    AUTH["Authentication<br/>Throttle: 5/min<br/>Public"]
    AUTH -->|POST| LOGIN["/login"]
    AUTH -->|POST| REGISTER["/register"]
    AUTH -->|GET| VERIFY["/email/verify/{id}/{hash}"]
    
    CONTENT["Content - Public Read"]
    CONTENT -->|GET| ARTICLES["/articles/public<br/>Paginated"]
    CONTENT -->|GET| ARTICLE_ID["/articles/{id}"]
    CONTENT -->|GET| SEARCH["/articles/search?q=<br/>Min 3 chars"]
    CONTENT -->|GET| CATEGORIES["/categories<br/>14 categories"]
    CONTENT -->|GET| TAGS["/tags"]
    CONTENT -->|GET| AUTHORS["/authors"]
    
    CONTACT["Contact - Rate Limited"]
    CONTACT -->|Throttle: 5/min| JOIN["/contact/join-herald"]
    CONTACT -->|Throttle: 10/min| FEEDBACK["/contact/feedback"]
    CONTACT -->|Throttle: 10/min| SUBSCRIBE["/contact/subscribe"]
    CONTACT -->|Throttle: 5/min| COVERAGE["/contact/request-coverage"]
    
    PROTECTED["Protected - Auth Required"]
    PROTECTED -->|GET/PUT| ME["/me<br/>Current User"]
    PROTECTED -->|GET/POST<br/>PUT/DELETE| DRAFTS["/drafts<br/>User Drafts"]
    PROTECTED -->|POST<br/>PUT/DELETE| MYARTICLES["/articles<br/>User Articles"]
    PROTECTED -->|POST| TEAM["/team-members/update"]
    
    ADMIN["Admin Only"]
    ADMIN -->|GET| USERS["/users"]
    ADMIN -->|GET| LOGS["/logs"]
    ADMIN -->|GET/POST<br/>PUT/DELETE| STAFF["/staff"]
    
    ROOT --> AUTH
    ROOT --> CONTENT
    ROOT --> CONTACT
    ROOT --> PROTECTED
    ROOT --> ADMIN
    
    style ROOT fill:#ff2d20,color:#fff
    style AUTH fill:#ff6b6b,color:#fff
    style CONTENT fill:#4ecdc4,color:#fff
    style CONTACT fill:#f9ca24,color:#000
    style PROTECTED fill:#a29bfe,color:#fff
    style ADMIN fill:#ff7675,color:#fff
```

## Request Processing Pipeline

```mermaid
graph TD
    REQ["Incoming HTTP Request"]
    
    REQ -->|Middleware Stack| KERNEL["HTTP Kernel"]
    KERNEL -->|Check| CORS_MW["CORS Middleware"]
    CORS_MW -->|Validate Origin| CORS_OK{CORS Valid?}
    
    CORS_OK -->|No| REJECT["❌ CORS Error 403"]
    CORS_OK -->|Yes| RATE_MW["Rate Limiter"]
    
    RATE_MW -->|Check| RATE_OK{Within Limits?}
    RATE_OK -->|No| RATE_LIMIT["❌ Too Many Requests 429"]
    RATE_OK -->|Yes| ROUTER["Router Matching"]
    
    ROUTER -->|Match Route| ROUTE_OK{Route Found?}
    ROUTE_OK -->|No| NOT_FOUND["❌ Not Found 404"]
    ROUTE_OK -->|Yes| AUTH_MW["Auth Middleware<br/>auth:sanctum"]
    
    AUTH_MW -->|Route Protected?| AUTH_NEEDED{Requires Auth?}
    AUTH_NEEDED -->|Yes| TOKEN_VALID{Token Valid?}
    TOKEN_VALID -->|No| UNAUTH["❌ Unauthorized 401"]
    TOKEN_VALID -->|Yes| DISPATCH["✓ Dispatch to Controller"]
    AUTH_NEEDED -->|No| DISPATCH
    
    DISPATCH -->|Execute| CONTROLLER["Controller Method"]
    CONTROLLER -->|Eloquent Query| MODEL["Model Query Builder"]
    MODEL -->|Raw SQL| DATABASE["📊 Database"]
    
    DATABASE -->|Results| MODEL
    MODEL -->|Collection| CONTROLLER
    CONTROLLER -->|Format| RESPONSE["JSON Response"]
    
    RESPONSE -->|Transform| HTTP_RESP["HTTP Response<br/>+ CORS Headers"]
    HTTP_RESP -->|Send| CLIENT["Frontend Client"]
    
    style REQ fill:#61dafb,color:#000
    style KERNEL fill:#ff2d20,color:#fff
    style DISPATCH fill:#4ecdc4,color:#000
    style CLIENT fill:#61dafb,color:#000
    style REJECT fill:#d63031,color:#fff
    style NOT_FOUND fill:#d63031,color:#fff
    style RATE_LIMIT fill:#d63031,color:#fff
    style UNAUTH fill:#d63031,color:#fff
```

## Authentication Flow - Token Based

```mermaid
graph LR
    subgraph Client["Frontend<br/>localStorage"]
        TOKEN["🔑 Auth Token<br/>JWT"]
    end
    
    subgraph Backend["Backend<br/>Session"]
        SANCTUM["Sanctum Guard"]
        PAT["Personal Access<br/>Tokens Table"]
    end
    
    subgraph Database["PostgreSQL"]
        TOKENS_TABLE["personal_access_tokens<br/>token UNIQUE<br/>tokenable_id<br/>abilities"]
    end
    
    STEP1["1. Login POST /api/login"]
    STEP2["2. Verify Credentials"]
    STEP3["3. Create Token"]
    STEP4["4. Return Token"]
    STEP5["5. Store in localStorage"]
    STEP6["6. Send Authorization Header"]
    STEP7["7. Validate Token"]
    STEP8["8. Check Abilities"]
    STEP9["9. Grant Access"]
    
    STEP1 --> STEP2
    STEP2 --> STEP3 --> STEP4 --> Client
    Client --> STEP5 --> STEP6 --> Backend
    STEP6 --> STEP7 --> PAT --> TOKENS_TABLE
    TOKENS_TABLE --> STEP8 --> STEP9
    
    style Client fill:#61dafb,color:#000
    style Backend fill:#ff2d20,color:#fff
    style Database fill:#336791,color:#fff
```

## File Structure - Key Paths

```
backend/
├── 📁 app/Http/Controllers/        ← Request handlers (18 files)
│   ├── ArticleController.php       
│   ├── AuthController.php
│   └── ... (18 total)
│
├── 📁 app/Models/                  ← Database models (11 files)
│   ├── Article.php
│   ├── User.php
│   └── ... (11 total)
│
├── 📁 routes/
│   └── api.php                     ← 80+ API endpoints (503 lines)
│
├── 📁 config/
│   ├── cors.php                    ← CORS configuration
│   ├── auth.php                    ← Authentication setup
│   ├── session.php                 ← Session config
│   └── database.php                ← DB connection
│
├── 📁 database/migrations/         ← 25 migrations
│   ├── create_users_table.php
│   ├── create_articles_table.php
│   └── ... (create schema)
│
├── 📁 storage/
│   ├── logs/                       ← laravel.log
│   └── framework/                  ← Sessions & cache
│
├── .env                            ← Local environment
├── Procfile                        ← Render deployment
├── docker-compose.yml              ← Local Docker setup
└── composer.json                   ← Dependencies
```

---

Created: February 10, 2026  
Framework: Laravel 12  
Database: PostgreSQL / Render  
Status: Production Ready
