'use client';

import { useEffect, useState } from 'react';

interface ApiUserRow {
  api_user_id: string;
  request_count: string | number;
  ip_count: string | number;
  last_request_time: string;
}

export default function ApiUsersPage() {
  const [list, setList] = useState<ApiUserRow[]>([]);
  const [loading, setLoading] = useState(false);
  const [page, setPage] = useState(1);
  const [total, setTotal] = useState(0);

  useEffect(() => {
    const load = async () => {
      setLoading(true);
      try {
        const res = await fetch(`/api/admin/api-users?page=${page}&pageSize=20`);
        const data = await res.json();
        if (data.code === 200) {
          setList(data.data.users || []);
          setTotal(data.data.total || 0);
        }
      } finally {
        setLoading(false);
      }
    };
    load();
  }, [page]);

  return (
    <div>
      <h1 style={{ margin: '8px 0 16px', fontSize: 22, color: '#111827' }}>API用户列表</h1>
      <div style={{ fontSize: 13, color: '#6b7280', marginBottom: 10 }}>
        这里按 <code style={code}>bot_id</code>（或请求头 <code style={code}>x-bot-id</code>）汇总请求，bot_id 为空则归类为 unknown。
      </div>

      {loading && <div style={{ color: '#6b7280' }}>加载中…</div>}

      <div
        style={{
          padding: 16,
          border: '1px solid #e5e7eb',
          borderRadius: 12,
          background: '#ffffff',
          boxShadow: '0 1px 2px rgba(15,23,42,0.03)',
        }}
      >
        <div style={{ overflowX: 'auto' }}>
          <table style={{ width: '100%', borderCollapse: 'collapse' }}>
            <thead>
              <tr>
                <th style={th}>API用户ID</th>
                <th style={th}>请求次数</th>
                <th style={th}>来源IP数</th>
                <th style={th}>最后请求时间</th>
              </tr>
            </thead>
            <tbody>
              {list.map((row, idx) => (
                <tr key={idx}>
                  <td style={td}>
                    <code style={code}>{row.api_user_id}</code>
                  </td>
                  <td style={td}>{row.request_count}</td>
                  <td style={td}>{row.ip_count}</td>
                  <td style={td}>{new Date(row.last_request_time).toLocaleString('zh-CN')}</td>
                </tr>
              ))}
              {list.length === 0 && (
                <tr>
                  <td style={td} colSpan={4}>
                    暂无数据（等待机器人调用 API 后自动产生）
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        <div style={{ marginTop: 12, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <div style={{ fontSize: 13, color: 'rgba(230,230,230,0.75)' }}>共 {total} 条</div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <button onClick={() => setPage(Math.max(1, page - 1))} disabled={page === 1} style={btn(page === 1)}>
              上一页
            </button>
            <span style={{ fontSize: 13 }}>第 {page} 页</span>
            <button onClick={() => setPage(page + 1)} disabled={page * 20 >= total} style={btn(page * 20 >= total)}>
              下一页
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

const th: React.CSSProperties = {
  textAlign: 'left',
  fontSize: 12,
  color: '#6b7280',
  padding: '10px 10px',
  borderBottom: '1px solid #e5e7eb',
  background: '#f9fafb',
  whiteSpace: 'nowrap',
};

const td: React.CSSProperties = {
  padding: '10px 10px',
  borderBottom: '1px solid #e5e7eb',
  fontSize: 13,
  whiteSpace: 'nowrap',
};

const code: React.CSSProperties = {
  background: 'rgba(255,255,255,0.06)',
  padding: '2px 6px',
  borderRadius: 6,
};

function btn(disabled: boolean): React.CSSProperties {
  return {
    padding: '6px 10px',
    borderRadius: 8,
    border: '1px solid #e5e7eb',
    background: disabled ? '#f3f4f6' : '#2563eb',
    color: disabled ? '#9ca3af' : '#ffffff',
    cursor: disabled ? 'not-allowed' : 'pointer',
  };
}

