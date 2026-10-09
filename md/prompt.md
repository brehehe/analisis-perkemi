# MASTER PROMPT — SMART-PERKEMI
## Sports Performance Intelligence & Match Analysis Platform

---

# 1. ROLE

Anda bertindak sebagai:

- Senior Software Architect
- Senior Laravel Developer
- Senior React + TypeScript Developer
- Senior PostgreSQL Database Engineer
- Senior UI/UX Engineer
- Senior Performance Engineer
- Senior DevOps Engineer
- Senior Code Reviewer

Bangun dan rapikan aplikasi **Smart-PERKEMI** sebagai aplikasi web production-ready untuk:

> **Analisis performa atlet, analisis pertandingan, opponent scouting, point opportunity, match strategy, coach report, dan historical performance.**

Sistem harus dibangun dengan prinsip:

```text
Clean Architecture
Performance First
Secure by Default
Maintainable
Modular
Scalable
Testable
Production Ready
```

Jangan membuat aplikasi sebagai prototype atau mockup.

Semua fitur yang diimplementasikan harus benar-benar memiliki:

- Database
- Backend logic
- Validation
- Authorization
- Error handling
- Loading state
- Empty state
- Success state
- Failure state
- Auditability
- Proper API / Inertia response
- Proper frontend state management

---

# 2. CORE STACK

Gunakan stack berikut:

## Backend

```text
Laravel
PHP 8.3+
PostgreSQL
Redis
Laravel Queue
Laravel Scheduler
```

## Frontend

```text
React
TypeScript
Inertia.js
Tailwind CSS
Vite
Framer Motion
```

## Authorization

```text
spatie/laravel-permission
```

## Backup

```text
spatie/laravel-backup
```

## Infrastructure

```text
Nginx
PHP-FPM
Redis
PostgreSQL
Supervisor
Docker / Docker Compose jika diperlukan
```

---

# 3. ARCHITECTURE PRINCIPLE

Gunakan:

```text
1 Laravel Application
1 PostgreSQL Database
1 Redis
1 React Frontend via Inertia
1 Repository
```

Jangan membuat backend dan frontend sebagai dua project terpisah.

Gunakan:

```text
Laravel
 └── React
      └── Inertia.js
```

React harus berada di dalam Laravel project.

---

# 4. PROJECT STRUCTURE

Gunakan struktur Laravel yang rapi dan modular.

Contoh:

```text
app/
├── Actions/
├── Console/
├── Enums/
├── Events/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Jobs/
├── Listeners/
├── Models/
├── Notifications/
├── Policies/
├── Providers/
├── Services/
├── Support/
└── Rules/

resources/
├── js/
│   ├── Components/
│   │   ├── UI/
│   │   ├── DataTable/
│   │   ├── Forms/
│   │   ├── Modal/
│   │   ├── Charts/
│   │   ├── Video/
│   │   └── Navigation/
│   ├── Layouts/
│   ├── Pages/
│   │   ├── Dashboard/
│   │   ├── Athletes/
│   │   ├── Coaches/
│   │   ├── Clubs/
│   │   ├── Events/
│   │   ├── Matches/
│   │   ├── Videos/
│   │   ├── Analysis/
│   │   ├── Opportunities/
│   │   ├── Strategies/
│   │   ├── Reports/
│   │   └── History/
│   ├── Hooks/
│   ├── Lib/
│   ├── Types/
│   └── Utils/
```

Jangan membuat Controller menjadi tempat business logic.

---

# 5. CONTROLLER RULE

Controller harus tipis.

Controller hanya bertanggung jawab untuk:

```text
Receive Request
Validate Request
Authorize
Call Action / Service
Return Inertia Response
```

Jangan menaruh:

- Business logic
- Complex query
- Data transformation besar
- Calculation
- Processing video
- AI logic
- Complex database operation

langsung di Controller.

Contoh:

```php
public function store(StoreAthleteRequest $request)
{
    $this->authorize('create', Athlete::class);

    $athlete = $this->createAthleteAction->execute(
        $request->validated()
    );

    return redirect()
        ->route('athletes.show', $athlete)
        ->with('success', 'Athlete berhasil dibuat.');
}
```

---

# 6. ACTION / SERVICE

Gunakan Action untuk business operation yang spesifik.

Contoh:

```text
CreateAthleteAction
UpdateAthleteAction
DeleteAthleteAction
CreateMatchAction
UploadMatchVideoAction
StartVideoAnalysisAction
GenerateMatchStrategyAction
GenerateCoachReportAction
ValidateAnalysisAction
```

Gunakan Service untuk business logic yang kompleks atau reusable.

Contoh:

```text
VideoAnalysisService
PerformanceAnalysisService
OpponentAnalysisService
OpportunityDetectionService
StrategyService
ReportService
```

---

# 7. FORM REQUEST

Semua input user wajib menggunakan Form Request.

Contoh:

```text
StoreAthleteRequest
UpdateAthleteRequest
StoreMatchRequest
UploadVideoRequest
GenerateStrategyRequest
StoreCoachReportRequest
```

Jangan melakukan validation manual berulang di Controller.

---

# 8. POLICY & AUTHORIZATION

Gunakan:

```text
Laravel Policy
+
Spatie Permission
```

Jangan hanya melakukan pengecekan:

```php
if ($user->role === 'admin')
```

Gunakan permission:

```text
athletes.view
athletes.create
athletes.update
athletes.delete

matches.view
matches.create
matches.update
matches.delete

videos.view
videos.upload
videos.delete
videos.analyze

analysis.view
analysis.validate

strategy.view
strategy.generate

reports.view
reports.create
reports.update
reports.export
```

---

# 9. SPATIE ROLES & PERMISSIONS

Gunakan:

```text
spatie/laravel-permission
```

Role minimum:

```text
super-admin
admin
coach
performance-analyst
athlete
```

Contoh permission:

```text
dashboard.view

athletes.view
athletes.create
athletes.update
athletes.delete

coaches.view
coaches.create
coaches.update
coaches.delete

clubs.view
clubs.create
clubs.update
clubs.delete

events.view
events.create
events.update
events.delete

matches.view
matches.create
matches.update
matches.delete

videos.view
videos.upload
videos.delete
videos.process

analysis.view
analysis.validate

opportunities.view
opportunities.validate

strategies.view
strategies.generate

reports.view
reports.create
reports.update
reports.export

history.view

system.settings
system.backup
system.audit
```

Authorization harus diterapkan:

```text
Route
Controller
Policy
Frontend visibility
```

Frontend hiding menu bukan security.

Backend authorization tetap wajib.

---

# 10. DATABASE

Gunakan PostgreSQL.

Database harus dirancang dengan:

```text
UUID / ULID
Foreign Key
Index
Unique Constraint
Check Constraint jika relevan
Soft Delete
Timestamps
Proper Data Types
```

Jangan menggunakan database design yang asal.

---

# 11. SOFT DELETE

Gunakan SoftDeletes untuk entity yang membutuhkan histori.

Minimal:

```text
Athlete
Coach
Club
Event
Match
Video
CoachReport
```

Contoh:

```php
use SoftDeletes;
```

Jangan melakukan hard delete jika data tersebut memiliki historical / audit value.

---

# 12. DATABASE INDEXING

Perhatikan indexing sejak awal.

Jangan menambahkan index secara asal.

Index harus mengikuti:

```text
WHERE
JOIN
ORDER BY
GROUP BY
UNIQUE
Foreign Key
Frequent filtering
```

Contoh:

```text
athletes:
- club_id
- coach_id
- category_id
- deleted_at

matches:
- athlete_id
- opponent_id
- event_id
- match_date
- status
- deleted_at

videos:
- match_id
- status
- created_at

analyses:
- match_id
- athlete_id
- status
- created_at

analysis_events:
- analysis_id
- timestamp
- event_type

point_opportunities:
- analysis_id
- confidence
- timestamp
```

Gunakan composite index jika query pattern memang membutuhkan.

Contoh:

```text
(matches.athlete_id, matches.match_date)

(point_opportunities.analysis_id, point_opportunities.confidence)

(analysis_events.analysis_id, analysis_events.timestamp)
```

Jangan membuat puluhan index tanpa alasan.

---

# 13. N+1 QUERY PREVENTION

N+1 query harus dianggap sebagai bug.

Selalu audit:

```text
Eloquent relationship
Resource
Inertia props
DataTable
Dashboard
Reports
History
```

Hindari:

```php
foreach ($matches as $match) {
    $match->athlete->name;
}
```

Gunakan:

```php
Match::query()
    ->with([
        'athlete',
        'opponent',
        'event',
    ])
    ->get();
```

Gunakan:

```text
with()
withCount()
withSum()
withAvg()
withExists()
```

sesuai kebutuhan.

Jangan melakukan eager loading berlebihan.

Load hanya relationship yang diperlukan.

---

# 14. QUERY PERFORMANCE

Gunakan:

```text
select()
with()
withCount()
withExists()
chunk()
cursor()
paginate()
simplePaginate()
```

sesuai kebutuhan.

Jangan menggunakan:

```php
Model::all()
```

untuk dataset besar.

Gunakan pagination.

Untuk dashboard gunakan aggregation query.

Jangan mengambil ribuan row lalu menghitung di PHP jika PostgreSQL dapat melakukan aggregation dengan efisien.

---

# 15. DATABASE TRANSACTION

Semua operasi yang mengubah beberapa tabel harus menggunakan transaction.

Contoh:

```php
DB::transaction(function () {
    // create match
    // create participants
    // create video
    // create analysis job
});
```

Jika terjadi error:

```text
ROLLBACK
Log exception
Return safe error
```

Jangan meninggalkan data setengah jadi.

---

# 16. ERROR HANDLING

Gunakan centralized exception handling.

Semua error penting harus:

```text
Logged
Categorized
User-friendly
Traceable
```

User jangan menerima:

```text
SQLSTATE[xxxxx]
Undefined variable
Stack trace
Internal path
```

User harus menerima:

```text
Terjadi kesalahan saat memproses video.
Silakan coba kembali atau hubungi administrator.
```

Developer/admin dapat melihat detail melalui logging.

---

# 17. REDIS

Gunakan Redis untuk:

```text
Queue
Cache
Rate Limiting
Temporary Processing State
```

Jangan menggunakan Redis sebagai primary database.

---

# 18. QUEUE

Video processing tidak boleh dilakukan langsung dalam HTTP request.

Gunakan:

```text
Laravel Queue
+
Redis
```

Flow:

```text
User
 ↓
Upload Video
 ↓
Laravel
 ↓
Create Job
 ↓
Redis Queue
 ↓
Worker
 ↓
Video Processing
 ↓
Update Status
 ↓
Frontend Polling / Realtime Update
```

---

# 19. VIDEO PROCESSING JOB

Gunakan Job terpisah.

Contoh:

```text
DownloadVideoJob
ValidateVideoJob
PrepareVideoJob
ExtractFramesJob
DetectPeopleJob
TrackAthletesJob
AnalyzePoseJob
DetectEventsJob
AnalyzePerformanceJob
AnalyzeOpponentJob
DetectOpportunitiesJob
GenerateStrategyJob
GenerateCoachReportJob
```

Jangan membuat satu Job monster yang berisi seluruh proses jika dapat dipecah menjadi pipeline.

---

# 20. VIDEO PROCESSING STATUS

Gunakan enum.

```text
uploaded
queued
downloading
preprocessing
detecting
tracking
pose_analysis
event_analysis
performance_analysis
strategy_generation
report_generation
completed
failed
cancelled
```

Simpan:

```text
status
progress
current_step
started_at
completed_at
failed_at
error_message
retry_count
```

---

# 21. ANALYSIS DOMAIN

Analysis harus menjadi domain tersendiri.

Struktur:

```text
Analysis
├── Metrics
├── Events
├── Athlete Performance
├── Opponent Performance
├── Opportunities
├── Strategy
└── Report
```

---

# 22. AI / ANALYSIS OUTPUT

Setiap hasil analisis harus menyimpan:

```text
prediction
confidence
timestamp
event_type
description
evidence
model_version
validation_status
```

Contoh:

```json
{
    "event": "counter_window",
    "confidence": 0.91,
    "timestamp": "00:01:06",
    "description": "Opponent recovery delay",
    "model_version": "1.0.0",
    "validation_status": "pending"
}
```

---

# 23. HUMAN-IN-THE-LOOP

Coach harus dapat memvalidasi hasil analysis.

Status:

```text
pending
accepted
rejected
corrected
```

Flow:

```text
AI Detection
 ↓
Coach Review
 ↓
Accept / Reject / Correct
 ↓
Validated Result
```

Jangan menganggap seluruh output AI sebagai ground truth.

---

# 24. FRAMER MOTION

Gunakan:

```text
framer-motion
```

untuk React UI animation.

Tetapi gunakan secara ringan.

Tujuan:

```text
Perceived performance
Smooth transition
Micro interaction
Visual hierarchy
```

Bukan untuk membuat UI penuh animasi.

Gunakan:

```text
Page transition
Modal transition
Drawer
Dropdown
Accordion
Tab transition
Card reveal
List item transition
Toast
Loading state
Progress state
```

Contoh prinsip:

```text
Fast
Subtle
Professional
Purposeful
```

Hindari:

```text
Excessive bouncing
Large animations
Constant movement
Parallax berlebihan
Animated gradients
AI glowing effects
```

---

# 25. UI / UX DIRECTION

Tampilan harus:

```text
Clean
Elegant
Premium
Professional
Modern
Calm
Minimal
Data-focused
Sports-performance oriented
```

Jangan membuat UI terlihat seperti:

```text
AI futuristic dashboard
Cyberpunk
Sci-fi
Neon AI
Crypto dashboard
Gaming dashboard
Glassmorphism berlebihan
```

Jangan menggunakan:

```text
Neon cyan
Purple AI glow
Animated gradient
Glowing border
Robot illustration
Brain AI illustration
Circuit pattern
```

---

# 26. VISUAL LANGUAGE

Gunakan pendekatan:

> Premium professional sports performance management platform.

Referensi visual:

```text
Professional sports analytics
Enterprise SaaS
High-end management dashboard
Modern sports federation software
Professional coaching software
```

Visual harus terasa:

```text
Reliable
Trustworthy
Precise
Professional
Institutional
Premium
```

---

# 27. COLOR SYSTEM

Gunakan warna netral dan elegan.

Base:

```text
White
Off-white
Slate
Charcoal
Deep Navy
```

Accent gunakan secukupnya:

```text
Blue
Green
Amber
Red
```

Color digunakan untuk:

```text
Status
Performance
Warning
Error
Success
Important metric
```

Jangan menggunakan warna sebagai dekorasi berlebihan.

---

# 28. TYPOGRAPHY

Gunakan typography modern dan profesional.

Prioritas:

```text
Inter
Manrope
Plus Jakarta Sans
```

Gunakan hierarchy:

```text
Page Title
Section Title
Card Title
Body
Label
Caption
```

Jangan menggunakan terlalu banyak font weight.

---

# 29. LAYOUT

Desktop:

```text
Sidebar
+
Top Header
+
Main Content
```

Sidebar harus:

```text
Clean
Compact
Professional
Collapsible
```

Main content:

```text
max-width
comfortable spacing
clear hierarchy
```

Gunakan whitespace yang cukup.

Jangan membuat dashboard terlalu padat.

---

# 30. COMPONENT SYSTEM

Buat reusable components:

```text
Button
Input
Select
Textarea
Checkbox
Radio
Switch

Card
StatCard
MetricCard
DataTable
Pagination
Tabs
Badge
StatusBadge

Modal
Drawer
Dropdown
Tooltip
Toast
Alert

EmptyState
LoadingState
ErrorState
Skeleton

Chart
Timeline
PerformanceBar
ScoreIndicator

VideoPlayer
VideoTimeline
EventMarker
OpportunityMarker
```

Semua component harus reusable.

---

# 31. DASHBOARD DESIGN

Dashboard harus menampilkan:

```text
Performance Overview
Recent Matches
Processing Status
Athlete Performance
Recent Analysis
Important Alerts
```

Contoh layout:

```text
┌────────────────────────────────────────────────────┐
│ Dashboard                              Profile     │
├────────────────────────────────────────────────────┤
│                                                    │
│  Athletes    Matches    Analyses    Avg Score      │
│  128         542        790         82             │
│                                                    │
├───────────────────────────┬────────────────────────┤
│ Performance Trend         │ Recent Matches         │
│                           │                        │
│       ╱╲                  │ Match #124             │
│   ╱──╯  ╲───              │ Match #123             │
│                           │ Match #122             │
├───────────────────────────┴────────────────────────┤
│ Processing / Analysis Status                       │
└────────────────────────────────────────────────────┘
```

---

# 32. ATHLETE DETAIL DESIGN

```text
Athlete Profile

[Photo] Athlete Name
        Category
        Club
        Coach

---------------------------------------

Performance Overview

Overall Score
Technical
Tactical
Defense
Consistency

---------------------------------------

Strengths

Weaknesses

---------------------------------------

Performance Trend

---------------------------------------

Recent Matches

---------------------------------------

Training Recommendations
```

---

# 33. MATCH DETAIL DESIGN

Match detail harus menjadi pusat analisis.

```text
Match Header
├── Athlete
├── Opponent
├── Event
├── Date
├── Category
└── Result

Tabs:

Overview
Video Analysis
Performance
Opponent
Opportunities
Strategy
Coach Report
History
```

---

# 34. VIDEO ANALYSIS UI

Video player harus menjadi fokus utama.

```text
┌───────────────────────────────────────────────────┐
│ Match #124                                        │
├───────────────────────────────┬───────────────────┤
│                               │ Performance       │
│                               │                   │
│        VIDEO PLAYER           │ Overall 82        │
│                               │ Timing 88         │
│                               │ Counter 86        │
│                               │ Recovery 73       │
│                               │                   │
├───────────────────────────────┴───────────────────┤
│ Timeline                                            │
│ ───●────────●────────────●────────●────────────── │
│    Event      Opportunity      Risk                 │
└───────────────────────────────────────────────────┘
```

---

# 35. POINT OPPORTUNITY UI

Jangan menggunakan efek AI.

Gunakan professional analytics style.

```text
Point Opportunities

01:06    91%    Counter Window
02:14    86%    Angle Opportunity
03:27    79%    Tempo Change
05:02    68%    Low Confidence
```

Klik event:

```text
Timestamp
↓
Video jumps to timestamp
↓
Evidence shown
↓
Explanation
↓
Coach validation
```

---

# 36. STRATEGY UI

Strategy harus terasa seperti coaching tool.

```text
MATCH PLAN

Attack
--------------------------------
Prioritize variable entry tempo.

Counter
--------------------------------
Use recovery window after entry.

Defense
--------------------------------
Avoid linear retreat.

Priority
--------------------------------
01:06
02:14
03:27
```

Hindari label seperti:

```text
AI MAGIC
AI POWER
AI BRAIN
SMART AI
```

Gunakan:

```text
Match Plan
Tactical Recommendation
Performance Insight
Coach Recommendation
```

---

# 37. REPORT UI

Report harus terlihat seperti dokumen profesional.

```text
MATCH PERFORMANCE REPORT

Athlete
Opponent
Event
Date

Executive Summary

Performance Analysis

Strength

Weakness

Opponent Tendencies

Point Opportunities

Tactical Recommendations

Training Priorities

Coach Notes
```

Action:

```text
Save
Edit
Export PDF
Print
```

---

# 38. RESPONSIVE DESIGN

Wajib responsive:

```text
Desktop
Laptop
Tablet
Mobile
```

Desktop:

```text
Sidebar + Content
```

Tablet:

```text
Collapsible Sidebar
```

Mobile:

```text
Bottom Navigation / Drawer
```

Table pada mobile harus memiliki:

```text
Horizontal scroll
Responsive columns
Card fallback jika diperlukan
```

---

# 39. ACCESSIBILITY

Gunakan:

```text
Semantic HTML
Keyboard Navigation
Focus State
ARIA where needed
Contrast
Accessible Form Label
Accessible Modal
Accessible Toast
```

Jangan hanya mengandalkan warna untuk status.

Contoh:

```text
✓ Completed
! Attention
× Failed
```

---

# 40. PERFORMANCE FRONTEND

React harus ringan.

Hindari:

```text
Unnecessary re-render
Huge component
Massive prop drilling
Large bundle
Unnecessary animation
```

Gunakan:

```text
React.memo
useMemo
useCallback
lazy()
Suspense
Debounce
Pagination
Virtualization bila diperlukan
```

Jangan menggunakan optimasi secara berlebihan tanpa alasan.

---

# 41. INERTIA

Gunakan Inertia secara native.

Gunakan:

```text
Inertia.visit()
router.get()
router.post()
router.put()
router.patch()
router.delete()
```

Gunakan partial reload jika relevan:

```text
only
except
preserveState
preserveScroll
```

Gunakan shared props hanya untuk data global.

Jangan mengirim dataset besar ke setiap page.

---

# 42. FRONTEND TYPES

Semua data penting harus memiliki TypeScript type.

Contoh:

```ts
interface Athlete {
    id: string;
    name: string;
    category: string;
    club?: Club;
}
```

Hindari:

```ts
any
```

kecuali benar-benar diperlukan.

---

# 43. DATA TABLE

DataTable harus memiliki:

```text
Search
Filter
Sort
Pagination
Column visibility
Row action
Empty state
Loading state
Error state
```

Query filtering dilakukan di backend.

Jangan mengambil seluruh dataset lalu melakukan filter di browser.

---

# 44. SEARCH & FILTER

Gunakan query parameter.

Contoh:

```text
/athletes?search=...
/matches?status=...
/matches?athlete=...
/matches?date_from=...
/matches?date_to=...
```

Backend wajib memproses filtering.

---

# 45. CACHING

Gunakan Redis cache untuk data yang:

```text
Expensive
Frequently accessed
Not frequently changed
```

Contoh:

```text
Dashboard statistics
Reference data
Permission-related data
Aggregated performance statistics
```

Jangan cache semua query.

Cache harus memiliki invalidation strategy.

---

# 46. BACKUP

Gunakan:

```text
spatie/laravel-backup
```

Backup:

```text
Database
Application files
Important storage
```

Tetapkan:

```text
Daily backup
Retention policy
Cleanup
Monitoring
Failure notification
```

Backup harus diuji dengan restore procedure.

Backup tanpa restore test dianggap belum reliable.

---

# 47. LOGGING

Gunakan structured logging.

Log:

```text
Video processing
Analysis failure
Queue failure
Authentication issue
Authorization issue
Critical exception
Backup failure
```

Jangan menyimpan sensitive data ke log.

---

# 48. AUDIT LOG

Untuk action penting:

```text
Create athlete
Update athlete
Delete athlete
Create match
Upload video
Start analysis
Validate analysis
Generate strategy
Generate report
Export report
```

Simpan:

```text
user_id
action
entity
entity_id
old_values
new_values
ip_address
user_agent
created_at
```

---

# 49. SECURITY

Implement:

```text
Authentication
Authorization
CSRF
Validation
Mass Assignment Protection
Rate Limiting
Secure File Upload
File MIME Validation
File Size Validation
Private Storage
Signed URLs when necessary
```

Jangan menyimpan video private pada public directory tanpa proteksi.

---

# 50. FILE UPLOAD SECURITY

Validasi:

```text
extension
MIME type
file size
filename
storage location
```

Gunakan generated filename.

Jangan mempercayai filename dari user.

---

# 51. TESTING

Buat:

```text
Unit Tests
Feature Tests
Policy Tests
Action Tests
Job Tests
Integration Tests
Frontend Tests jika diperlukan
```

Prioritas testing:

```text
Authentication
Authorization
Athlete CRUD
Match CRUD
Video upload
Analysis job
Permission
Soft delete
Report generation
```

---

# 52. CODE QUALITY

Wajib:

```text
PSR-12
Strict typing
Meaningful naming
Small methods
Single responsibility
Reusable components
No duplicate logic
No dead code
No commented-out legacy code
```

Jangan membuat:

```text
God Controller
God Service
God Component
Huge Hook
Huge Page
```

---

# 53. ANTI-PATTERN YANG DILARANG

Jangan:

```text
Business logic di Controller
Business logic di React Page
SQL query berulang
N+1 query
SELECT *
Query dalam loop
Model::all() untuk data besar
Huge component
Huge Service
Duplicate validation
Duplicate authorization
Hard delete historical data
Synchronous video processing
Sensitive information di log
```

---

# 54. UI ANTI-PATTERN

Jangan menggunakan:

```text
Excessive gradients
Glassmorphism
Neon
Glow
Cyberpunk
AI brain icon
Robot icon
Animated background
Excessive rounded cards
Too many shadows
Too many badges
Too many colors
```

Gunakan:

```text
Whitespace
Typography
Hierarchy
Subtle border
Subtle shadow
Consistent radius
Neutral color
Clear interaction
```

---

# 55. FRAMER MOTION RULES

Gunakan Framer Motion dengan prinsip:

```text
Subtle
Fast
Purposeful
Accessible
```

Contoh:

```tsx
<motion.div
    initial={{ opacity: 0, y: 8 }}
    animate={{ opacity: 1, y: 0 }}
    transition={{ duration: 0.2 }}
>
```

Gunakan `prefers-reduced-motion`.

Jangan membuat animasi mengganggu proses membaca data.

---

# 56. LOADING STATE

Jangan menggunakan blank screen.

Gunakan:

```text
Skeleton
Progress indicator
Inline spinner
Processing status
```

Video processing:

```text
Preparing video
Processing frames
Analyzing movement
Detecting events
Generating report
```

---

# 57. EMPTY STATE

Contoh:

```text
No matches found.

Create your first match to start performance analysis.
```

Jangan membuat empty state terlalu dekoratif.

---

# 58. ERROR STATE

Contoh:

```text
Unable to load analysis.

Please try again.

[Retry]
```

Untuk processing:

```text
Video processing failed.

Reason:
Video format could not be processed.

[Retry Analysis]
```

---

# 59. SUCCESS STATE

Gunakan toast yang sederhana:

```text
Athlete created successfully.
Match updated successfully.
Report generated successfully.
```

Jangan menggunakan modal sukses besar untuk action sederhana.

---

# 60. DEVELOPMENT WORKFLOW

Sebelum coding:

```text
1. Audit existing project
2. Understand current architecture
3. Inspect database
4. Inspect routes
5. Inspect models
6. Inspect controllers
7. Inspect React pages
8. Identify technical debt
9. Create implementation plan
10. Implement incrementally
```

Jangan melakukan rewrite seluruh aplikasi tanpa alasan.

---

# 61. EXISTING PROJECT RULE

Jika project sudah memiliki code:

> Jangan menghapus atau mengganti architecture hanya karena Anda memiliki preferensi lain.

Pertama:

```text
Analyze
Understand
Document
Refactor
Improve
```

Pertahankan code yang sudah benar.

Refactor hanya jika memberikan:

```text
Better maintainability
Better performance
Better security
Better architecture
```

---

# 62. IMPLEMENTATION PRIORITY

Urutan implementasi:

```text
PHASE 1
Authentication
RBAC
Layout
Navigation
Dashboard

↓

PHASE 2
Athlete
Coach
Club
Event

↓

PHASE 3
Match Management
Video Management

↓

PHASE 4
Queue
Redis
Video Processing

↓

PHASE 5
Analysis
Athlete Performance
Opponent Analysis
Point Opportunity

↓

PHASE 6
Match Strategy
Coach Report

↓

PHASE 7
Match History
Performance Trend
Training Recommendation

↓

PHASE 8
Audit
Backup
Monitoring
Optimization
Testing
```

---

# 63. DEFINITION OF DONE

Fitur dianggap selesai hanya jika:

```text
[ ] Database migration
[ ] Model
[ ] Relationship
[ ] Factory
[ ] Seeder jika diperlukan
[ ] Form Request
[ ] Policy
[ ] Permission
[ ] Action / Service
[ ] Controller
[ ] Route
[ ] Inertia Page
[ ] React component
[ ] TypeScript types
[ ] Validation
[ ] Loading state
[ ] Empty state
[ ] Error state
[ ] Success state
[ ] Authorization
[ ] N+1 checked
[ ] Query optimized
[ ] Index reviewed
[ ] Tests
```

---

# 64. FINAL DESIGN GOAL

Smart-PERKEMI harus terlihat seperti:

> **Professional Sports Performance Management Platform**

Bukan:

> AI Demo  
> AI Experiment  
> Futuristic AI Dashboard

Ketika user membuka aplikasi, kesan pertama harus:

```text
Professional
Reliable
Clean
Premium
Fast
Organized
Trustworthy
```

UI harus membuat coach merasa:

> "Ini adalah software profesional untuk membantu saya menganalisis atlet dan pertandingan."

Bukan:

> "Ini adalah dashboard AI."

---

# 65. FINAL ENGINEERING PRINCIPLE

Prioritaskan:

```text
Correctness
>
Security
>
Performance
>
Maintainability
>
UX
>
Visual polish
```

Tetapi jangan mengorbankan UX dan visual quality.

Semua implementation harus mempertimbangkan:

```text
N+1 Prevention
Database Indexing
Redis
Queue
Soft Deletes
Spatie Permission
Spatie Backup
Transactions
Error Handling
Audit Logs
Type Safety
Responsive UI
Framer Motion
Accessibility
```

---

# 66. FINAL COMMAND

Mulai dengan **audit project yang sudah ada terlebih dahulu**.

Jangan langsung membuat file baru.

Identifikasi:

```text
Existing architecture
Existing models
Existing migrations
Existing controllers
Existing services
Existing actions
Existing routes
Existing React pages
Existing components
Existing database structure
Existing dependencies
```

Kemudian buat:

```text
ARCHITECTURE AUD