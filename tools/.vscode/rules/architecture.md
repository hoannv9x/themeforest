# Technical Design Document: Learning SaaS Platform Modernization

## 1. Executive Summary
Chiến lược này tập trung vào việc chuyển đổi ứng dụng từ giai đoạn MVP (Phase 1) sang một nền tảng học tập chuẩn doanh nghiệp (SaaS) có khả năng mở rộng cao. Chúng tôi sẽ áp dụng triết lý **Mobile-First** để tối ưu hóa trải nghiệm trên thiết bị di động, đồng thời xây dựng một **Design System** dựa trên token để đảm bảo tính nhất quán. Kiến trúc kỹ thuật sẽ tận dụng tối đa sức mạnh của **Nuxt 4** và **Laravel 12**, tập trung vào hiệu suất (Core Web Vitals) và khả năng tiếp cận (Accessibility WCAG 2.1 AA).

---

## 2. Responsive Strategy
Chúng tôi sẽ sử dụng chiến lược **Mobile-First**, xây dựng từ màn hình nhỏ nhất (320px) và mở rộng dần.

- **Breakpoint Strategy**:
    - `xs`: 320px (Mobile portrait - base)
    - `sm`: 640px (Mobile landscape)
    - `md`: 768px (Tablets)
    - `lg`: 1024px (Laptops/Small Desktops)
    - `xl`: 1280px (Desktop standard)
    - `2xl`: 1536px (Large screens)
- **Layout System**: Chuyển đổi từ Fixed layouts sang **Fluid Grids**. Sử dụng `grid-template-columns: repeat(auto-fit, minmax(300px, 1fr))` cho các danh sách khóa học.
- **Dynamic Viewport**: Sử dụng `min-h-dvh` (Dynamic Viewport Height) thay vì `100vh` để tránh lỗi bị che khuất bởi thanh công cụ trình duyệt trên mobile.
- **Safe Areas**: Áp dụng `padding-bottom: env(safe-area-inset-bottom)` cho thanh điều hướng dưới cùng (Bottom Nav) trên các thiết bị có tai thỏ/notch.

---

## 3. Performance Blueprint
Mục tiêu là đạt điểm Lighthouse > 90 trên mọi thiết bị.

- **Targets**:
    - **LCP**: < 2.0s (Sử dụng `<NuxtImg>` với thuộc tính `preload` cho ảnh bìa khóa học).
    - **INP**: < 100ms (Tối ưu hóa event handlers, sử dụng `v-memo` cho các danh sách lớn).
    - **CLS**: < 0.1 (Luôn chỉ định kích thước ảnh và sử dụng Skeleton Screens).
- **Techniques**:
    - **Code Splitting**: Tự động hóa bởi Nuxt 4.
    - **Asset Optimization**: Chuyển đổi toàn bộ ảnh sang định dạng WebP/AVIF qua Nuxt Image module.
    - **GPU Compositing**: Sử dụng `will-change: transform` cho các hiệu ứng transition của sidebar và modal để mượt mà ở 60fps.

---

## 4. Design System Specification (Design Tokens)
Xây dựng kiến trúc token để hỗ trợ Dark Mode và khả năng tùy biến (Theming).

- **Color Palette**:
    - `Primary`: Indigo (600 cho Light, 400 cho Dark).
    - `Neutral`: Slate (Dòng màu xám chuyên nghiệp cho Dashboard).
    - `Success`: Emerald, `Error`: Rose.
- **Spacing Scale**: Hệ số 4 (4px, 8px, 12px, 16px, 24px, 32px, 48px, 64px).
- **Typography**:
    - Font: **Inter** (Enterprise standard).
    - Base: 16px. Scale: 1.250 (Major Third).
    - Sử dụng `text-balance` cho tiêu đề để tránh mồ côi chữ trên mobile.
- **Elevation**: Hệ thống 4 cấp độ shadow (sm, md, lg, xl) với độ mờ (blur) tăng dần và độ đục (opacity) giảm dần.

---

## 5. UX/UI Pattern Library Plan
- **Navigation**:
    - Desktop: Sidebar cố định bên trái (Collapsible).
    - Mobile: Bottom Navigation (Home, My Courses, Profile) + Hamburger Menu cho các cài đặt phụ.
- **Feedback & Affordance**:
    - **Skeleton Screens**: Thay thế loading spinners cho Dashboard và Course List.
    - **Micro-interactions**: Hiệu ứng `scale-95` khi nhấn nút (active state) để tạo cảm giác xúc giác (tactile feeling).
- **Accessibility (WCAG 2.1 AA)**:
    - Tỷ lệ tương phản tối thiểu 4.5:1 cho văn bản.
    - Thêm `aria-label` cho toàn bộ các nút icon.
    - Hỗ trợ điều hướng bàn phím (`focus-visible` với vòng outline rõ ràng).

---

## 6. Technical Architecture
- **Frontend (Nuxt 4)**:
    - Cấu trúc: Layer-based architecture (Shared, Features, Entities).
    - CSS: Utility-first (Tailwind) kết hợp với **CSS Variables** cho Design Tokens.
    - State: Pinia (đã triển khai) + Persisted State cho Auth.
- **Backend (Laravel 12)**:
    - API: RESTful với JSON Resources.
    - Caching: Redis cho danh sách khóa học và thông tin profile.
    - Media: Sử dụng S3/Object Storage thay vì local storage.

---

## 7. Phased Rollout Plan (Lộ trình triển khai)

| Phase | Milestone | Mô tả | Ưu tiên |
| :--- | :--- | :--- | :--- |
| **P1** | **Foundation** | Triển khai Design Tokens, Layout responsive mới và Dark mode. | P0 |
| **P2** | **UX/A11y** | Nâng cấp Forms, Skeleton loaders và tuân thủ WCAG. | P1 |
| **P3** | **Performance** | Tối ưu hóa Nuxt Image, Lazy loading và Service Workers. | P1 |
| **P4** | **Polish** | Thêm Micro-interactions, Motion design và Offline support. | P2 |

---

## 8. Quality Checklist (QA & Testing)
- [ ] **Cross-browser**: Chrome, Safari (iOS), Firefox, Edge.
- [ ] **Responsive**: Kiểm tra trên iPhone SE (320px), iPad Air và Desktop 4K.
- [ ] **Accessibility**: Đạt điểm 100 trên Lighthouse A11y audit.
- [ ] **Performance**: LCP < 2.5s trên mạng 4G chậm.
- [ ] **Form UX**: Tất cả input đều có nhãn (label) và thông báo lỗi inline rõ ràng.

---

### Implementation Example: Fluid Typography (Design Token)
Trong [tailwind.config.js](file:///c:/laragon/www/themeforest/tools/study/fe/tailwind.config.js):
```javascript
theme: {
  extend: {
    fontSize: {
      'fluid-h1': 'clamp(2rem, 5vw + 1rem, 3.75rem)',
      'fluid-base': 'clamp(1rem, 0.5vw + 0.8rem, 1.125rem)',
    },
    spacing: {
      'safe-bottom': 'env(safe-area-inset-bottom)',
    }
  }
}
```

Tài liệu này đóng vai trò là kim chỉ nam cho đội ngũ kỹ thuật trong 6-12 tháng tới để đưa ứng dụng lên tiêu chuẩn sản phẩm thương mại cao cấp.