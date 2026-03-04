'use client';

import { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';

const NAV = [
  { name: '仪表台', href: '/secure-admin-8f3k9q/dashboard' },
  { name: '服务器列表', href: '/secure-admin-8f3k9q/servers' },
  { name: '机器人列表', href: '/secure-admin-8f3k9q/bots' },
  { name: 'API用户列表', href: '/secure-admin-8f3k9q/api-users' },
  { name: 'TRX出款钱包私钥信息', href: '/secure-admin-8f3k9q/trx-wallets' },
  { name: 'TON钱包助记词信息', href: '/secure-admin-8f3k9q/ton-wallets' },
];

export function AdminShell({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const [open, setOpen] = useState(false);

  return (
    <div
      style={{
        minHeight: '100vh',
        background: '#f3f4f6',
        color: '#111827',
        fontFamily: 'system-ui, -apple-system, BlinkMacSystemFont, \"Segoe UI\", sans-serif',
      }}
    >
      <header
        style={{
          position: 'sticky',
          top: 0,
          zIndex: 50,
          height: 56,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          padding: '0 16px',
          borderBottom: '1px solid #e5e7eb',
          background: '#ffffff',
          backdropFilter: 'blur(8px)',
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
          <button
            onClick={() => setOpen(!open)}
            aria-label="打开菜单"
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              justifyContent: 'center',
              width: 40,
              height: 40,
              borderRadius: 8,
              border: '1px solid #e5e7eb',
              background: '#ffffff',
              color: '#4b5563',
              cursor: 'pointer',
            }}
          >
            ☰
          </button>
          <Link
            href="/secure-admin-8f3k9q/dashboard"
            style={{ color: '#111827', textDecoration: 'none', fontWeight: 700, fontSize: 16 }}
          >
            API-Web 后台
          </Link>
        </div>
        <Link href="/" style={{ color: '#2563eb', textDecoration: 'none', fontSize: 13 }}>
          ← 返回首页
        </Link>
      </header>

      <div style={{ display: 'flex' }}>
        {/* overlay for mobile */}
        {open && (
          <div
            onClick={() => setOpen(false)}
            style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.4)', zIndex: 40 }}
          />
        )}

        <aside
          style={{
            position: 'fixed',
            top: 56,
            left: 0,
            bottom: 0,
            width: 260,
            background: '#ffffff',
            borderRight: '1px solid #e5e7eb',
            transform: open ? 'translateX(0)' : 'translateX(-100%)',
            transition: 'transform 200ms ease',
            zIndex: 45,
            padding: 16,
            overflowY: 'auto',
          }}
        >
          <div style={{ fontSize: 13, color: '#6b7280', marginBottom: 10 }}>导航菜单</div>
          <nav style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
            {NAV.map((item) => {
              const active = pathname === item.href;
              return (
                <Link
                  key={item.href}
                  href={item.href}
                  onClick={() => setOpen(false)}
                  style={{
                    textDecoration: 'none',
                    color: active ? '#111827' : '#4b5563',
                    background: active ? '#eff6ff' : 'transparent',
                    border: '1px solid ' + (active ? '#2563eb' : '#e5e7eb'),
                    borderRadius: 10,
                    padding: '10px 12px',
                    fontSize: 14,
                    fontWeight: active ? 600 : 500,
                  }}
                >
                  {item.name}
                </Link>
              );
            })}
          </nav>
        </aside>

        {/* 内容区域 */}
        <main style={{ flex: 1, padding: 16, marginTop: 0 }}>
          <div style={{ maxWidth: 1400, margin: '0 auto' }}>{children}</div>
        </main>
      </div>

      <style jsx global>{`
        @media (min-width: 900px) {
          aside[style*='position: fixed'][style*='top: 56px'] {
            position: sticky !important;
            transform: translateX(0) !important;
            top: 56px !important;
          }
        }
      `}</style>
    </div>
  );
}

