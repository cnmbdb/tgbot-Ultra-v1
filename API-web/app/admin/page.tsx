'use client';

import { useState, useEffect } from 'react';

interface ApiLog {
  id: number;
  api_path: string;
  method: string;
  source_ip: string;
  source_host: string | null;
  bot_id: string | null;
  status_code: number | null;
  response_time: number | null;
  created_at: string;
}

interface BotConnection {
  id: number;
  bot_id: string;
  bot_name: string | null;
  source_ip: string;
  source_host: string | null;
  last_request_time: string;
  request_count: number;
  status: string;
}

interface Statistics {
  todayRequests: number;
  totalRequests: number;
  activeBots: number;
  topApis: Array<{ api_path: string; count: number }>;
}

interface Config {
  key: string;
  value: string;
  description: string;
}

export default function AdminPage() {
  const [activeTab, setActiveTab] = useState<'stats' | 'logs' | 'bots' | 'config'>('stats');
  const [logs, setLogs] = useState<ApiLog[]>([]);
  const [bots, setBots] = useState<BotConnection[]>([]);
  const [stats, setStats] = useState<Statistics | null>(null);
  const [configs, setConfigs] = useState<Config[]>([]);
  const [loading, setLoading] = useState(false);
  const [page, setPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [editingConfig, setEditingConfig] = useState<string | null>(null);
  const [editValue, setEditValue] = useState('');

  useEffect(() => {
    if (activeTab === 'stats') {
      loadStatistics();
    } else if (activeTab === 'logs') {
      loadLogs();
    } else if (activeTab === 'bots') {
      loadBots();
    } else if (activeTab === 'config') {
      loadConfigs();
    }
  }, [activeTab, page]);

  const loadStatistics = async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/admin/statistics');
      const data = await res.json();
      if (data.code === 200) {
        setStats(data.data);
      }
    } catch (error) {
      console.error('Failed to load statistics:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadLogs = async () => {
    setLoading(true);
    try {
      const res = await fetch(`/api/admin/logs?page=${page}&pageSize=20`);
      const data = await res.json();
      if (data.code === 200) {
        setLogs(data.data.logs || []);
        setTotal(data.data.total || 0);
      }
    } catch (error) {
      console.error('Failed to load logs:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadBots = async () => {
    setLoading(true);
    try {
      const res = await fetch(`/api/admin/bots?page=${page}&pageSize=20`);
      const data = await res.json();
      if (data.code === 200) {
        setBots(data.data.connections || []);
        setTotal(data.data.total || 0);
      }
    } catch (error) {
      console.error('Failed to load bots:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadConfigs = async () => {
    setLoading(true);
    try {
      // 从环境变量加载配置
      const configsData: Config[] = [
        { key: 'TRON_NETWORK', value: process.env.NEXT_PUBLIC_TRON_NETWORK || 'https://api.trongrid.io', description: 'Tron 网络 RPC URL' },
        { key: 'ETH_RPC_URL', value: process.env.NEXT_PUBLIC_ETH_RPC_URL || 'https://eth.llamarpc.com', description: '以太坊网络 RPC URL' },
        { key: 'DB_HOST', value: process.env.NEXT_PUBLIC_DB_HOST || 'api-web-postgres', description: '数据库主机' },
        { key: 'DB_PORT', value: process.env.NEXT_PUBLIC_DB_PORT || '5432', description: '数据库端口' },
        { key: 'DB_NAME', value: process.env.NEXT_PUBLIC_DB_NAME || 'apiweb', description: '数据库名称' },
        { key: 'REDIS_HOST', value: process.env.NEXT_PUBLIC_REDIS_HOST || 'api-web-redis', description: 'Redis 主机' },
        { key: 'REDIS_PORT', value: process.env.NEXT_PUBLIC_REDIS_PORT || '6379', description: 'Redis 端口' },
      ];
      setConfigs(configsData);
    } catch (error) {
      console.error('Failed to load configs:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleEditConfig = (key: string, value: string) => {
    setEditingConfig(key);
    setEditValue(value);
  };

  const handleSaveConfig = async (key: string) => {
    try {
      // 这里应该调用 API 保存配置，目前只是前端展示
      alert(`配置 ${key} 已更新为: ${editValue}\n\n注意：实际配置需要通过环境变量或配置文件修改。`);
      setEditingConfig(null);
      loadConfigs();
    } catch (error) {
      console.error('Failed to save config:', error);
      alert('保存配置失败');
    }
  };

  const formatTime = (time: string) => {
    return new Date(time).toLocaleString('zh-CN');
  };

  return (
    <div style={{ padding: '20px', fontFamily: 'Arial, sans-serif', maxWidth: '1400px', margin: '0 auto' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
        <h1 style={{ margin: 0 }}>API-Web 管理后台</h1>
        <a href="/" style={{ color: '#0070f3', textDecoration: 'none' }}>← 返回首页</a>
      </div>

      <div style={{ marginBottom: '20px', borderBottom: '1px solid #ddd', display: 'flex', gap: '10px' }}>
        <button
          onClick={() => setActiveTab('stats')}
          style={{
            padding: '10px 20px',
            border: 'none',
            background: activeTab === 'stats' ? '#0070f3' : '#f0f0f0',
            color: activeTab === 'stats' ? 'white' : 'black',
            cursor: 'pointer',
            borderRadius: '4px 4px 0 0',
          }}
        >
          统计信息
        </button>
        <button
          onClick={() => setActiveTab('logs')}
          style={{
            padding: '10px 20px',
            border: 'none',
            background: activeTab === 'logs' ? '#0070f3' : '#f0f0f0',
            color: activeTab === 'logs' ? 'white' : 'black',
            cursor: 'pointer',
            borderRadius: '4px 4px 0 0',
          }}
        >
          API 日志
        </button>
        <button
          onClick={() => setActiveTab('bots')}
          style={{
            padding: '10px 20px',
            border: 'none',
            background: activeTab === 'bots' ? '#0070f3' : '#f0f0f0',
            color: activeTab === 'bots' ? 'white' : 'black',
            cursor: 'pointer',
            borderRadius: '4px 4px 0 0',
          }}
        >
          机器人连接
        </button>
        <button
          onClick={() => setActiveTab('config')}
          style={{
            padding: '10px 20px',
            border: 'none',
            background: activeTab === 'config' ? '#0070f3' : '#f0f0f0',
            color: activeTab === 'config' ? 'white' : 'black',
            cursor: 'pointer',
            borderRadius: '4px 4px 0 0',
          }}
        >
          配置管理
        </button>
      </div>

      {loading && <div style={{ padding: '20px', textAlign: 'center' }}>加载中...</div>}

      {activeTab === 'stats' && stats && (
        <div>
          <h2>统计信息</h2>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '20px', marginTop: '20px' }}>
            <div style={{ padding: '20px', background: '#f5f5f5', borderRadius: '8px', textAlign: 'center' }}>
              <div style={{ fontSize: '32px', fontWeight: 'bold', color: '#0070f3' }}>{stats.todayRequests}</div>
              <div style={{ color: '#666', marginTop: '8px' }}>今日请求数</div>
            </div>
            <div style={{ padding: '20px', background: '#f5f5f5', borderRadius: '8px', textAlign: 'center' }}>
              <div style={{ fontSize: '32px', fontWeight: 'bold', color: '#0070f3' }}>{stats.totalRequests}</div>
              <div style={{ color: '#666', marginTop: '8px' }}>总请求数</div>
            </div>
            <div style={{ padding: '20px', background: '#f5f5f5', borderRadius: '8px', textAlign: 'center' }}>
              <div style={{ fontSize: '32px', fontWeight: 'bold', color: '#0070f3' }}>{stats.activeBots}</div>
              <div style={{ color: '#666', marginTop: '8px' }}>活跃机器人</div>
            </div>
          </div>

          <h3 style={{ marginTop: '30px' }}>热门 API（24小时）</h3>
          <div style={{ marginTop: '10px', overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse', background: 'white' }}>
              <thead>
                <tr style={{ background: '#f5f5f5' }}>
                  <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>API 路径</th>
                  <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>请求次数</th>
                </tr>
              </thead>
              <tbody>
                {stats.topApis.map((api, index) => (
                  <tr key={index}>
                    <td style={{ padding: '12px', border: '1px solid #ddd' }}><code>{api.api_path}</code></td>
                    <td style={{ padding: '12px', border: '1px solid #ddd' }}>{api.count}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {activeTab === 'logs' && (
        <div>
          <h2>API 请求日志</h2>
          <div style={{ marginTop: '20px', overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse', background: 'white' }}>
              <thead>
                <tr style={{ background: '#f5f5f5' }}>
                  <th style={{ padding: '10px', textAlign: 'left', border: '1px solid #ddd' }}>时间</th>
                  <th style={{ padding: '10px', textAlign: 'left', border: '1px solid #ddd' }}>API 路径</th>
                  <th style={{ padding: '10px', textAlign: 'left', border: '1px solid #ddd' }}>方法</th>
                  <th style={{ padding: '10px', textAlign: 'left', border: '1px solid #ddd' }}>来源IP</th>
                  <th style={{ padding: '10px', textAlign: 'left', border: '1px solid #ddd' }}>机器人ID</th>
                  <th style={{ padding: '10px', textAlign: 'left', border: '1px solid #ddd' }}>状态码</th>
                  <th style={{ padding: '10px', textAlign: 'left', border: '1px solid #ddd' }}>响应时间(ms)</th>
                </tr>
              </thead>
              <tbody>
                {logs.map((log) => (
                  <tr key={log.id}>
                    <td style={{ padding: '10px', border: '1px solid #ddd' }}>{formatTime(log.created_at)}</td>
                    <td style={{ padding: '10px', border: '1px solid #ddd' }}><code>{log.api_path}</code></td>
                    <td style={{ padding: '10px', border: '1px solid #ddd' }}>{log.method}</td>
                    <td style={{ padding: '10px', border: '1px solid #ddd' }}>{log.source_ip}</td>
                    <td style={{ padding: '10px', border: '1px solid #ddd' }}>{log.bot_id || '-'}</td>
                    <td style={{ padding: '10px', border: '1px solid #ddd' }}>{log.status_code || '-'}</td>
                    <td style={{ padding: '10px', border: '1px solid #ddd' }}>{log.response_time || '-'}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <div style={{ marginTop: '20px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <div>共 {total} 条记录</div>
            <div>
              <button
                onClick={() => setPage(Math.max(1, page - 1))}
                disabled={page === 1}
                style={{ padding: '5px 10px', marginRight: '10px', cursor: page === 1 ? 'not-allowed' : 'pointer' }}
              >
                上一页
              </button>
              <span>第 {page} 页</span>
              <button
                onClick={() => setPage(page + 1)}
                disabled={page * 20 >= total}
                style={{ padding: '5px 10px', marginLeft: '10px', cursor: page * 20 >= total ? 'not-allowed' : 'pointer' }}
              >
                下一页
              </button>
            </div>
          </div>
        </div>
      )}

      {activeTab === 'bots' && (
        <div>
          <h2>机器人连接列表</h2>
          <div style={{ marginTop: '20px', overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse', background: 'white' }}>
              <thead>
                <tr style={{ background: '#f5f5f5' }}>
                  <th style={{ padding: '10px', textAlign: 'left', border: '1px solid #ddd' }}>机器人ID</th>
                  <th style={{ padding: '10px', textAlign: 'left', border: '1px solid #ddd' }}>来源IP</th>
                  <th style={{ padding: '10px', textAlign: 'left', border: '1px solid #ddd' }}>来源主机</th>
                  <th style={{ padding: '10px', textAlign: 'left', border: '1px solid #ddd' }}>请求次数</th>
                  <th style={{ padding: '10px', textAlign: 'left', border: '1px solid #ddd' }}>最后请求时间</th>
                  <th style={{ padding: '10px', textAlign: 'left', border: '1px solid #ddd' }}>状态</th>
                </tr>
              </thead>
              <tbody>
                {bots.map((bot) => (
                  <tr key={bot.id}>
                    <td style={{ padding: '10px', border: '1px solid #ddd' }}>{bot.bot_id}</td>
                    <td style={{ padding: '10px', border: '1px solid #ddd' }}>{bot.source_ip}</td>
                    <td style={{ padding: '10px', border: '1px solid #ddd' }}>{bot.source_host || '-'}</td>
                    <td style={{ padding: '10px', border: '1px solid #ddd' }}>{bot.request_count}</td>
                    <td style={{ padding: '10px', border: '1px solid #ddd' }}>{formatTime(bot.last_request_time)}</td>
                    <td style={{ padding: '10px', border: '1px solid #ddd' }}>
                      <span style={{ color: bot.status === 'active' ? 'green' : 'gray', fontWeight: 'bold' }}>
                        {bot.status === 'active' ? '活跃' : '非活跃'}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <div style={{ marginTop: '20px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <div>共 {total} 条记录</div>
            <div>
              <button
                onClick={() => setPage(Math.max(1, page - 1))}
                disabled={page === 1}
                style={{ padding: '5px 10px', marginRight: '10px', cursor: page === 1 ? 'not-allowed' : 'pointer' }}
              >
                上一页
              </button>
              <span>第 {page} 页</span>
              <button
                onClick={() => setPage(page + 1)}
                disabled={page * 20 >= total}
                style={{ padding: '5px 10px', marginLeft: '10px', cursor: page * 20 >= total ? 'not-allowed' : 'pointer' }}
              >
                下一页
              </button>
            </div>
          </div>
        </div>
      )}

      {activeTab === 'config' && (
        <div>
          <h2>配置管理</h2>
          <div style={{ marginTop: '20px' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse', background: 'white' }}>
              <thead>
                <tr style={{ background: '#f5f5f5' }}>
                  <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>配置项</th>
                  <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>当前值</th>
                  <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>说明</th>
                  <th style={{ padding: '12px', textAlign: 'left', border: '1px solid #ddd' }}>操作</th>
                </tr>
              </thead>
              <tbody>
                {configs.map((config) => (
                  <tr key={config.key}>
                    <td style={{ padding: '12px', border: '1px solid #ddd', fontWeight: 'bold' }}>{config.key}</td>
                    <td style={{ padding: '12px', border: '1px solid #ddd' }}>
                      {editingConfig === config.key ? (
                        <input
                          type="text"
                          value={editValue}
                          onChange={(e) => setEditValue(e.target.value)}
                          style={{ width: '100%', padding: '6px', border: '1px solid #ddd', borderRadius: '4px' }}
                        />
                      ) : (
                        <code style={{ background: '#f5f5f5', padding: '4px 8px', borderRadius: '4px' }}>{config.value}</code>
                      )}
                    </td>
                    <td style={{ padding: '12px', border: '1px solid #ddd', color: '#666' }}>{config.description}</td>
                    <td style={{ padding: '12px', border: '1px solid #ddd' }}>
                      {editingConfig === config.key ? (
                        <>
                          <button
                            onClick={() => handleSaveConfig(config.key)}
                            style={{ padding: '6px 12px', marginRight: '8px', background: '#0070f3', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' }}
                          >
                            保存
                          </button>
                          <button
                            onClick={() => setEditingConfig(null)}
                            style={{ padding: '6px 12px', background: '#999', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' }}
                          >
                            取消
                          </button>
                        </>
                      ) : (
                        <button
                          onClick={() => handleEditConfig(config.key, config.value)}
                          style={{ padding: '6px 12px', background: '#0070f3', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' }}
                        >
                          编辑
                        </button>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            <div style={{ marginTop: '20px', padding: '15px', background: '#fff3cd', borderRadius: '8px', color: '#856404' }}>
              <strong>注意：</strong>配置修改需要通过环境变量或 Docker Compose 配置文件进行。前端仅用于查看当前配置值。
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
