'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';

export default function AdminLoginPage() {
  const router = useRouter();
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const res = await fetch('/api/admin/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ password }),
      });

      const data = await res.json();

      if (!res.ok || data.code !== 200) {
        setError(data.message || '密码错误');
        return;
      }

      // 登录成功，跳转到安全后台首页
      router.push('/secure-admin-8f3k9q');
    } catch (err) {
      console.error('Login failed', err);
      setError('登录失败，请稍后重试');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div
      style={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: '#f5f5f5',
        fontFamily: 'Arial, sans-serif',
      }}
    >
      <div
        style={{
          width: '100%',
          maxWidth: '360px',
          background: '#fff',
          padding: '24px 28px',
          borderRadius: '8px',
          boxShadow: '0 4px 10px rgba(0,0,0,0.05)',
        }}
      >
        <h1 style={{ margin: '0 0 8px', fontSize: '22px', textAlign: 'center' }}>后台登录</h1>
        <p style={{ margin: '0 0 24px', fontSize: '13px', color: '#777', textAlign: 'center' }}>
          请输入管理密码继续访问
        </p>

        <form onSubmit={handleSubmit}>
          <div style={{ marginBottom: '16px' }}>
            <label
              htmlFor="password"
              style={{ display: 'block', marginBottom: '6px', fontSize: '13px', color: '#555' }}
            >
              管理密码
            </label>
            <input
              id="password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="请输入管理员密码"
              autoComplete="current-password"
              style={{
                width: '100%',
                padding: '8px 10px',
                fontSize: '14px',
                borderRadius: '4px',
                border: '1px solid #ddd',
                outline: 'none',
              }}
            />
          </div>

          {error && (
            <div
              style={{
                marginBottom: '12px',
                fontSize: '12px',
                color: '#a94442',
                background: '#f2dede',
                borderRadius: '4px',
                padding: '6px 8px',
              }}
            >
              {error}
            </div>
          )}

          <button
            type="submit"
            disabled={loading}
            style={{
              width: '100%',
              padding: '9px 0',
              fontSize: '14px',
              fontWeight: 'bold',
              borderRadius: '4px',
              border: 'none',
              cursor: loading ? 'default' : 'pointer',
              background: loading ? '#999' : '#0070f3',
              color: '#fff',
            }}
          >
            {loading ? '登录中…' : '登录'}
          </button>
        </form>

        <div style={{ marginTop: '16px', textAlign: 'center' }}>
          <a
            href="/"
            style={{ fontSize: '12px', color: '#0070f3', textDecoration: 'none' }}
          >
            ← 返回首页
          </a>
        </div>
      </div>
    </div>
  );
}

