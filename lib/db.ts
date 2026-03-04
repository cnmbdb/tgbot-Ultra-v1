import { Pool } from 'pg';

let pool: Pool | null = null;

export function getDbPool() {
  if (!pool) {
    pool = new Pool({
      host: process.env.DB_HOST || 'api-web-postgres',
      port: parseInt(process.env.DB_PORT || '5432'),
      user: process.env.DB_USER || 'root',
      password: process.env.DB_PASSWORD || 'apiwebpassword',
      database: process.env.DB_NAME || 'apiweb',
      max: 10,
      idleTimeoutMillis: 30000,
      connectionTimeoutMillis: 2000,
    });
  }
  return pool;
}

export async function query(sql: string, params?: any[]): Promise<any> {
  const pool = getDbPool();
  try {
    const result = await pool.query(sql, params);
    return result.rows;
  } catch (error) {
    console.error('Database query error:', error);
    throw error;
  }
}

// 记录 API 请求日志
export async function logApiRequest(data: {
  apiPath: string;
  method: string;
  sourceIp: string;
  sourceHost?: string;
  botId?: string;
  // Telegram 机器人管理员用户名（例如 @xxxx）
  adminUsername?: string;
  // 机器人进程标识或名称
  botProcess?: string;
  requestData?: any;
  responseData?: any;
  statusCode?: number;
  responseTime?: number;
  errorMessage?: string;
}) {
  try {
    await query(
      `INSERT INTO api_logs 
       (api_path, method, source_ip, source_host, bot_id, request_data, response_data, status_code, response_time, error_message)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)`,
      [
        data.apiPath,
        data.method,
        data.sourceIp,
        data.sourceHost || null,
        data.botId || null,
        data.requestData ? JSON.stringify(data.requestData) : null,
        data.responseData ? JSON.stringify(data.responseData) : null,
        data.statusCode || null,
        data.responseTime || null,
        data.errorMessage || null,
      ]
    );

    // 更新或创建机器人连接记录
    if (data.botId) {
      await query(
        `INSERT INTO bot_connections 
         (bot_id, source_ip, source_host, admin_username, process_name, last_request_time, request_count, status)
         VALUES ($1, $2, $3, $4, $5, NOW(), 1, 'active')
         ON CONFLICT (bot_id) DO UPDATE SET
         source_ip = EXCLUDED.source_ip,
         source_host = EXCLUDED.source_host,
         admin_username = EXCLUDED.admin_username,
         process_name = EXCLUDED.process_name,
         last_request_time = NOW(),
         request_count = bot_connections.request_count + 1,
         status = 'active'`,
        [
          data.botId,
          data.sourceIp,
          data.sourceHost || null,
          data.adminUsername || null,
          data.botProcess || null,
        ]
      );
    }
  } catch (error) {
    console.error('Failed to log API request:', error);
    // 不抛出错误，避免影响主流程
  }
}

// 获取 API 日志
export async function getApiLogs(params: {
  page?: number;
  pageSize?: number;
  apiPath?: string;
  sourceIp?: string;
  botId?: string;
  startTime?: string;
  endTime?: string;
}) {
  const page = params.page || 1;
  const pageSize = params.pageSize || 20;
  const offset = (page - 1) * pageSize;

  let whereClause = '1=1';
  const queryParams: any[] = [];

  let paramIndex = 1;
  if (params.apiPath) {
    whereClause += ` AND api_path LIKE $${paramIndex}`;
    queryParams.push(`%${params.apiPath}%`);
    paramIndex++;
  }
  if (params.sourceIp) {
    whereClause += ` AND source_ip = $${paramIndex}`;
    queryParams.push(params.sourceIp);
    paramIndex++;
  }
  if (params.botId) {
    whereClause += ` AND bot_id = $${paramIndex}`;
    queryParams.push(params.botId);
    paramIndex++;
  }
  if (params.startTime) {
    whereClause += ` AND created_at >= $${paramIndex}`;
    queryParams.push(params.startTime);
    paramIndex++;
  }
  if (params.endTime) {
    whereClause += ` AND created_at <= $${paramIndex}`;
    queryParams.push(params.endTime);
    paramIndex++;
  }

  const logs = await query(
    `SELECT * FROM api_logs 
     WHERE ${whereClause}
     ORDER BY created_at DESC
     LIMIT $${paramIndex} OFFSET $${paramIndex + 1}`,
    [...queryParams, pageSize, offset]
  );

  const countResult = await query(
    `SELECT COUNT(*) as total FROM api_logs WHERE ${whereClause}`,
    queryParams
  );

  return {
    logs,
    total: parseInt(countResult[0]?.total || '0'),
    page,
    pageSize,
  };
}

// 获取机器人连接列表
export async function getBotConnections(params: {
  page?: number;
  pageSize?: number;
  botId?: string;
  sourceIp?: string;
  status?: string;
}) {
  const page = params.page || 1;
  const pageSize = params.pageSize || 20;
  const offset = (page - 1) * pageSize;

  let whereClause = '1=1';
  const queryParams: any[] = [];

  let paramIndex = 1;
  if (params.botId) {
    whereClause += ` AND bot_id LIKE $${paramIndex}`;
    queryParams.push(`%${params.botId}%`);
    paramIndex++;
  }
  if (params.sourceIp) {
    whereClause += ` AND source_ip = $${paramIndex}`;
    queryParams.push(params.sourceIp);
    paramIndex++;
  }
  if (params.status) {
    whereClause += ` AND status = $${paramIndex}`;
    queryParams.push(params.status);
    paramIndex++;
  }

  const connections = await query(
    `SELECT * FROM bot_connections 
     WHERE ${whereClause}
     ORDER BY last_request_time DESC
     LIMIT $${paramIndex} OFFSET $${paramIndex + 1}`,
    [...queryParams, pageSize, offset]
  );

  const countResult = await query(
    `SELECT COUNT(*) as total FROM bot_connections WHERE ${whereClause}`,
    queryParams
  );

  return {
    connections,
    total: parseInt(countResult[0]?.total || '0'),
    page,
    pageSize,
  };
}

// 获取统计信息
export async function getStatistics() {
  const todayCount = await query(
    `SELECT COUNT(*) as count FROM api_logs 
     WHERE DATE(created_at) = CURRENT_DATE`
  );
  
  const totalCount = await query(
    `SELECT COUNT(*) as count FROM api_logs`
  );

  const activeBots = await query(
    `SELECT COUNT(*) as count FROM bot_connections 
     WHERE status = 'active' AND last_request_time >= NOW() - INTERVAL '1 hour'`
  );

  const topApis = await query(
    `SELECT api_path, COUNT(*) as count 
     FROM api_logs 
     WHERE created_at >= NOW() - INTERVAL '24 hours'
     GROUP BY api_path 
     ORDER BY count DESC 
     LIMIT 10`
  );

  return {
    todayRequests: parseInt(todayCount[0]?.count || '0'),
    totalRequests: parseInt(totalCount[0]?.count || '0'),
    activeBots: parseInt(activeBots[0]?.count || '0'),
    topApis,
  };
}

// 获取“服务器列表”（按来源主机/IP 汇总）
export async function getServers(params: { page?: number; pageSize?: number }) {
  const page = params.page || 1;
  const pageSize = params.pageSize || 20;
  const offset = (page - 1) * pageSize;

  const rows = await query(
    `SELECT 
       COALESCE(NULLIF(source_host, ''), '-') as source_host,
       source_ip,
       COUNT(*)::bigint as request_count,
       MAX(created_at) as last_request_time
     FROM api_logs
     GROUP BY source_host, source_ip
     ORDER BY last_request_time DESC
     LIMIT $1 OFFSET $2`,
    [pageSize, offset],
  );

  const totalResult = await query(
    `SELECT COUNT(*) as total FROM (
      SELECT 1 FROM api_logs GROUP BY source_host, source_ip
    ) t`,
  );

  return {
    servers: rows,
    total: parseInt(totalResult[0]?.total || '0'),
    page,
    pageSize,
  };
}

// 获取“API 用户列表”（按 bot_id 汇总；bot_id 为空则归类为 unknown）
export async function getApiUsers(params: { page?: number; pageSize?: number }) {
  const page = params.page || 1;
  const pageSize = params.pageSize || 20;
  const offset = (page - 1) * pageSize;

  const rows = await query(
    `SELECT 
       COALESCE(NULLIF(bot_id, ''), 'unknown') as api_user_id,
       COUNT(*)::bigint as request_count,
       COUNT(DISTINCT source_ip)::bigint as ip_count,
       MAX(created_at) as last_request_time
     FROM api_logs
     GROUP BY COALESCE(NULLIF(bot_id, ''), 'unknown')
     ORDER BY last_request_time DESC
     LIMIT $1 OFFSET $2`,
    [pageSize, offset],
  );

  const totalResult = await query(
    `SELECT COUNT(*) as total FROM (
      SELECT 1 FROM api_logs GROUP BY COALESCE(NULLIF(bot_id, ''), 'unknown')
    ) t`,
  );

  return {
    users: rows,
    total: parseInt(totalResult[0]?.total || '0'),
    page,
    pageSize,
  };
}
