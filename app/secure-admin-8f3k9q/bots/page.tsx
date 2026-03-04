'use client';

import { useEffect, useState } from 'react';

interface BotConnection {
  id: number;
  bot_id: string;
  bot_name: string | null;
  admin_username: string | null;
  process_name: string | null;
  source_ip: string;
  source_host: string | null;
  last_request_time: string;
  request_count: number;
  status: string;
}

export default function BotsPage() {
  const [list, setList] = useState<BotConnection[]>([]);
  const [loading, setLoading] = useState(false);
  const [page, setPage] = useState(1);
  const [total, setTotal] = useState(0);

  useEffect(() => {
    const load = async () => {
      setLoading(true);
      try {
        const res = await fetch(`/api/admin/bots?page=${page}&pageSize=20`);
        const data = await res.json();
        if (data.code === 200) {
          setList(data.data.connections || []);
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
      <h1 style={{ margin: '8px 0 16px', fontSize: 22, color: '#111827' }}>机器人列表</h1>
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
                <th style={th}>机器人ID</th>
                <th style={th}>管理员用户名</th>
                <th style={th}>机器人进程</th>
                <th style={th}>来源IP</th>
                <th style={th}>来源主机</th>
                <th style={th}>请求次数</th>
                <th style={th}>最后请求时间</th>
                <th style={th}>状态</th>
              </tr>
            </thead>
            <tbody>
              {list.map((bot) => (
                <tr key={bot.id}>
                  <td style={td}>{bot.bot_id}</td>
                  <td style={td}>{bot.admin_username || '-'}</td>
                  <td style={td}>{bot.process_name || '-'}</td>
                  <td style={td}>{bot.source_ip}</td>
                  <td style={td}>{bot.source_host || '-'}</td>
                  <td style={td}>{bot.request_count}</td>
                  <td style={td}>{new Date(bot.last_request_time).toLocaleString('zh-CN')}</td>
                  <td style={td}>
                    <span style={{ color: bot.status === 'active' ? '#34d399' : '#9aa0a6', fontWeight: 700 }}>
                      {bot.status === 'active' ? '活跃' : '非活跃'}
                    </span>
                  </td>
                </tr>
              ))}
              {list.length === 0 && (
                <tr>
                  <td style={td} colSpan={8}>
                    暂无数据（机器人开始调用 API 后会自动出现）
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

