# English Learning Platform - Detailed Roadmap

# Phase 0 - Discovery & Product Design

## Duration

2 - 4 tuần

## Business Goal

Xác định:

* Ai là khách hàng đầu tiên
* Họ sẵn sàng trả tiền cho cái gì

## Chọn thị trường ban đầu

KHÔNG làm:

* Lớp 1 → 12
* IELTS
* TOEIC
* Người đi làm

cùng lúc.

Chỉ chọn 1:

Ví dụ:

* English for Working Professionals

hoặc

* English for Grade 6-9

---

## Product Requirement Document

### User Roles

* Guest
* Student
* Teacher
* Admin

### Learning Flow

Student

→ Login

→ Chọn khóa học

→ Học bài

→ Làm quiz

→ Theo dõi tiến độ

---

## UI/UX

Thiết kế:

* Landing
* Login
* Register
* Dashboard
* Course Detail
* Lesson Detail
* Quiz Screen
* Profile

---

## Deliverables

* PRD
* Wireframe
* DB Draft

---

# Phase 1 - Core Platform

## Duration

4 - 6 tuần

## Goal

Có thể học được.

---

## Backend

### Authentication

Tables

users

```sql
id
name
email
password
role
```

Features

* Register
* Login
* Logout
* Forgot Password

---

### User Profile

Tables

user_profiles

```sql
user_id
avatar
level
goal
```

---

### Course System

Tables

courses

```sql
id
title
description
thumbnail
status
```

modules

```sql
id
course_id
title
sort_order
```

lessons

```sql
id
module_id
title
content_type
```

---

### Progress Tracking

lesson_progress

```sql
user_id
lesson_id
completed_at
```

---

## Frontend

### Pages

Landing

Dashboard

My Courses

Course Detail

Lesson Detail

Profile

---

## Infrastructure

Docker

CI/CD

Staging

Production

Monitoring

---

## Deliverables

User có thể:

* đăng ký
* mua course (fake)
* học lesson
* lưu tiến độ

---

# Phase 2 - Quiz Engine

## Duration

3 - 4 tuần

## Goal

Tạo hệ thống kiểm tra.

---

## Database

quizzes

```sql
id
lesson_id
title
```

questions

```sql
id
quiz_id
type
content
```

answers

```sql
id
question_id
content
is_correct
```

---

## Question Types

Single Choice

Multiple Choice

Fill Blank

True False

Matching

---

## Backend

Submit Quiz

Calculate Score

Store Result

---

## Frontend

Quiz Player

Review Answer

Result Screen

---

## Deliverables

Một lesson có thể kết thúc bằng quiz.

---

# Phase 3 - Vocabulary System

## Duration

3 tuần

## Goal

Xây dựng bộ từ vựng.

---

## Database

vocabularies

```sql
id
word
ipa
meaning
audio_url
```

vocabulary_examples

```sql
id
vocabulary_id
example
```

---

## Features

Word List

Flash Card

Favorite Word

Search Word

---

## Deliverables

Người học có thể tự học từ vựng.

---

# Phase 4 - Listening

## Duration

4 tuần

## Goal

Luyện nghe.

---

## Storage

Audio

Subtitle

Transcript

---

## Database

listening_lessons

```sql
id
lesson_id
audio_url
transcript
```

---

## Features

Audio Player

Transcript

Dictation

Listening Quiz

---

## Deliverables

Module luyện nghe hoàn chỉnh.

---

# Phase 5 - Reading

## Duration

3 tuần

## Goal

Luyện đọc.

---

## Database

reading_articles

```sql
id
title
content
difficulty
```

---

## Features

Reading Passage

Reading Quiz

Vocabulary Highlight

---

## Deliverables

Reading module hoàn chỉnh.

---

# Phase 6 - Gamification

## Duration

3 tuần

## Goal

Tăng retention.

---

## Database

user_xp

user_streaks

achievements

user_achievements

---

## Features

XP

Level

Badge

Daily Streak

Leaderboard

---

## Deliverables

Retention loop hoạt động.

---

# Phase 7 - Payment

## Duration

2 tuần

## Goal

Bắt đầu thu tiền.

---

## Database

plans

subscriptions

payments

invoices

---

## Features

Monthly Plan

Yearly Plan

Trial

Coupon

---

## Integrations

Stripe

PayPal

---

## Deliverables

Thanh toán production-ready.

---

# Phase 8 - AI Writing

## Duration

4 tuần

## Goal

AI chấm bài viết.

---

## Database

writing_submissions

```sql
id
user_id
lesson_id
content
```

---

## AI Flow

Submit Essay

↓

AI Review

↓

Store Feedback

↓

Show Result

---

## Features

Grammar Score

Vocabulary Score

Improvement Suggestions

---

# Phase 9 - AI Speaking

## Duration

6 - 8 tuần

## Goal

Luyện phát âm.

---

## Database

speaking_attempts

```sql
id
user_id
lesson_id
audio_url
```

---

## Flow

Record

↓

Speech To Text

↓

Pronunciation Analysis

↓

Feedback

---

## Features

Word Accuracy

Sentence Accuracy

Fluency Score

---

# Phase 10 - Teacher CMS

## Duration

4 tuần

## Goal

Teacher tự tạo nội dung.

---

## Features

Course Builder

Lesson Builder

Quiz Builder

Assignment Builder

Student Report

---

# Phase 11 - Multi Tenant

## Duration

4 tuần

## Goal

Bán cho trung tâm.

---

## Database

tenants

tenant_users

tenant_settings

---

## Features

Subdomain

Custom Logo

Custom Theme

Teacher Isolation

Student Isolation

---

# Phase 12 - Scale Architecture

## Goal

100k+ users

---

## Split Services

Auth

Learning

AI

Notification

Billing

Search

---

## Infrastructure

Redis Cluster

Queue Cluster

CDN

Object Storage

ElasticSearch

Kubernetes
