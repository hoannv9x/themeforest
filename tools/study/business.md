# English Learning Platform Roadmap

## Vision

Xây dựng nền tảng học tiếng Anh cho:

* Học sinh lớp 1 → lớp 12
* Sinh viên
* Người đi làm

Hỗ trợ:

* Listening
* Speaking
* Reading
* Writing

Mô hình:

* Subscription
* AI-assisted learning
* Gamification
* Multi-tenant ready (giai đoạn sau)

---

# Technology Stack

## Frontend

* Next.js (React)
* TypeScript
* TailwindCSS
* TanStack Query

## Backend

* Laravel
* PostgreSQL
* Redis
* Queue Workers

## Storage

* S3 Compatible Storage

## Infrastructure

* Docker
* CI/CD
* Monitoring

---

# Phase 0 - Product Discovery

## Goal

Xác định chính xác sản phẩm cần giải quyết vấn đề gì.

## Deliverables

### Market Research

Nghiên cứu:

* Duolingo
* Elsa Speak
* Cambly
* BBC Learning English
* IELTS Online Platforms

### Target Users

#### Group A

Học sinh:

* Lớp 1 → 5
* Lớp 6 → 9
* Lớp 10 → 12

#### Group B

Người đi làm:

* Giao tiếp
* Business English
* Phỏng vấn

### Monetization

* Free
* Premium Monthly
* Premium Yearly

---

# Phase 1 - MVP Foundation

## Goal

Ra mắt phiên bản đầu tiên trong 2-3 tháng.

## Features

### Authentication

* Register
* Login
* Forgot Password
* Social Login

### User Profile

* Avatar
* Learning Goal
* Level

### Course System

Tables:

* courses
* modules
* lessons

### Lesson Types

* Video
* Text
* Audio

### Quiz System

* Single Choice
* Multiple Choice
* Fill In Blank

### Progress Tracking

* Lesson Completed
* Quiz Score

### Admin

* Course Management
* Lesson Management
* User Management

---

# Phase 2 - Learning Experience

## Goal

Tăng khả năng giữ chân học viên.

## Vocabulary Module

### Vocabulary

* Word
* IPA
* Meaning
* Example

### Flashcards

* Flip Card
* Review Card

### Spaced Repetition

SM-2 Algorithm

---

## Grammar Module

### Topics

* Tenses
* Passive Voice
* Relative Clause
* Conditionals

### Practice

* MCQ
* Fill In Blank

---

## Reading Module

### Reading Passage

* Beginner
* Intermediate
* Advanced

### Reading Questions

* Main Idea
* Vocabulary
* Detail

---

## Listening Module

### Audio Lesson

### Subtitle

### Dictation Exercise

---

# Phase 3 - Gamification

## Goal

Tăng retention.

## Features

### XP System

* Complete Lesson
* Complete Quiz

### Daily Streak

* 1 Day
* 7 Days
* 30 Days

### Achievement

* First Lesson
* 100 Lessons
* 1000 XP

### Leaderboard

* Weekly
* Monthly

---

# Phase 4 - AI Writing

## Goal

AI hỗ trợ kỹ năng viết.

## Features

### Writing Assignment

User submits essay.

### AI Review

Check:

* Grammar
* Vocabulary
* Clarity
* Structure

### Feedback

Provide:

* Mistakes
* Suggestions
* Improved Version

---

# Phase 5 - AI Speaking

## Goal

AI luyện phát âm.

## Features

### Speech To Text

User records voice.

### Pronunciation Score

Analyze:

* Accuracy
* Fluency

### AI Feedback

Feedback:

* Word Level
* Sentence Level

### Speaking Practice

* Repeat Sentence
* Read Aloud
* Conversation

---

# Phase 6 - Subscription & Payment

## Goal

Bắt đầu kiếm tiền.

## Features

### Plans

* Free
* Premium Monthly
* Premium Yearly

### Payment

* Stripe
* PayPal

### Entitlement System

* Feature Access
* Course Access

---

# Phase 7 - Mobile Application

## Goal

Mở rộng người dùng.

## Apps

### Student App

### Parent App

### Teacher App

---

# Phase 8 - Teacher Platform

## Goal

Cho phép giáo viên tạo nội dung.

## Features

### Teacher Dashboard

### Course Builder

### Quiz Builder

### Assignment Builder

### Student Tracking

---

# Phase 9 - Multi-Tenant SaaS

## Goal

Bán cho trường học và trung tâm.

## Tenant Features

### School

### English Center

### Corporate Training

---

## Tenant Isolation

Tables:

* tenants
* tenant_users

All business tables:

* tenant_id

---

## Custom Branding

### Logo

### Theme

### Domain

Example:

school-a.platform.com

school-b.platform.com

---

# Phase 10 - Enterprise Scale

## Goal

100,000+ Users

## Architecture

### Services

* User Service
* Learning Service
* AI Service
* Notification Service
* Billing Service

### Infrastructure

* Kubernetes
* Redis Cluster
* CDN
* Object Storage
* Search Engine

---

# Success Metrics

## MVP

100 Active Users

## Product-Market Fit

1000 Paying Users

## Growth

10000 Monthly Active Users

## Scale

100000 Monthly Active Users

---

# Database Preparation

Ngay từ đầu nên thêm:

tenant_id nullable

vào các bảng chính:

* users
* courses
* lessons
* quizzes

Để tương lai chuyển sang multi-tenant mà không cần redesign toàn bộ hệ thống.
