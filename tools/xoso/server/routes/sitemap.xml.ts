import { defineEventHandler } from 'h3';

export default defineEventHandler((event) => {
  // Lấy base URL từ cấu hình Nuxt
  const config = useRuntimeConfig();
  const siteUrl = config.public.siteUrl || 'http://localhost:3000';

  // Các route được cho phép (không bị chặn trong robots.txt)
  const routes = [
    { url: '/', changefreq: 'daily', priority: 1.0 },
    { url: '/vip', changefreq: 'weekly', priority: 0.8 },
    { url: '/results', changefreq: 'daily', priority: 0.9 },
  ];

  let sitemap = `<?xml version="1.0" encoding="UTF-8"?>\n`;
  sitemap += `<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n`;

  for (const route of routes) {
    sitemap += `  <url>\n`;
    sitemap += `    <loc>${siteUrl}${route.url}</loc>\n`;
    sitemap += `    <changefreq>${route.changefreq}</changefreq>\n`;
    sitemap += `    <priority>${route.priority}</priority>\n`;
    sitemap += `  </url>\n`;
  }

  sitemap += `</urlset>`;

  // Đặt header trả về là XML
  event.node.res.setHeader('Content-Type', 'application/xml');
  return sitemap;
});
