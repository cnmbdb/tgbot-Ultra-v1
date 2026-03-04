'use client';

import { useEffect, useState } from 'react';

interface ApiLog {
  id: number;
  api_path: string;
  method: string;
  source_ip: string;
  bot_id: string | null;
  status_code: number | null;
  created_at: string;
}

export default function TrxWalletSecretsPage() {
  const [logs, setLogs] = useState<ApiLog[]>([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    const load = async () => {
      setLoading(true);
      try {
        // 通过日志来审计“出款相关接口”的调用情况（私钥字段会被脱敏存储）
        const res = await fetch(`/api/admin/logs?page=1&pageSize=20&apiPath=/api/tron/`);
        const data = await res.json();
        if (data.code === 200) setLogs(data.data.logs || []);
      } finally {
        setLoading(false);
      }
    };
    load();
  }, []);

  return (
    <div>
      <h1 style={{ margin: '8px 0 16px', fontSize: 22, color: '#111827' }}>TRX出款钱包私钥信息</h1>
      <div style={{ fontSize: 13, color: '#6b7280', marginBottom: 10, lineHeight: 1.6 }}>
        为了安全，API-web <b>不会在后台明文展示/存储私钥</b>。机器人调用 TRX 出款类接口时，私钥字段会在日志里自动脱敏（显示为 <code style={code}>***HIDDEN***</code>）。
        <br />
        下面展示的是“出款相关接口”的最近调用记录，用于审计与排错。
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
                <th style={th}>时间</th>
                <th style={th}>API</th>
                <th style={th}>来源IP</th>
                <th style={th}>bot_id</th>
                <th style={th}>状态码</th>
              </tr>
            </thead>
            <tbody>
              {logs.map((l) => (
                <tr key={l.id}>
                  <td style={td}>{new Date(l.created_at).toLocaleString('zh-CN')}</td>
                  <td style={td}>
                    <code style={code}>{l.api_path}</code>
                  </td>
                  <td style={td}>{l.source_ip}</td>
                  <td style={td}>{l.bot_id || '-'}</td>
                  <td style={td}>{l.status_code ?? '-'}</td>
                </tr>
              ))}
              {logs.length === 0 && (
                <tr>
                  <td style={td} colSpan={5}>
                    暂无数据（触发一次 TRX 出款相关接口调用后会出现）
                  </td>
                </tr>
              )}
            </tbody>
          </table>
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
  background: '#f3f4f6',
  padding: '2px 6px',
  borderRadius: 6,
};

