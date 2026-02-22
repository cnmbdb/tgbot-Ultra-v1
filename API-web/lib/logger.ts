import { NextRequest } from 'next/server';
import { logApiRequest } from './db';

export async function logApiCall(
  request: NextRequest,
  responseData: any,
  statusCode: number,
  responseTime: number,
  errorMessage?: string
) {
  try {
    const startTime = Date.now();
    const sourceIp = request.headers.get('x-forwarded-for')?.split(',')[0] || 
                     request.headers.get('x-real-ip') || 
                     'unknown';
    const sourceHost = request.headers.get('host') || null;
    
    // 尝试从请求头或参数中获取机器人ID
    const botId = request.headers.get('x-bot-id') || 
                  request.nextUrl.searchParams.get('bot_id') || 
                  null;

    // 读取请求数据（如果是 POST/PUT/PATCH）
    let requestData: any = null;
    try {
      if (request.method === 'POST' || request.method === 'PUT' || request.method === 'PATCH') {
        const clonedRequest = request.clone();
        const body = await clonedRequest.json().catch(() => null);
        if (body) {
          requestData = sanitizeRequestData(body);
        }
      }
    } catch (e) {
      // 忽略解析错误
    }

    await logApiRequest({
      apiPath: request.nextUrl.pathname,
      method: request.method,
      sourceIp: sourceIp as string,
      sourceHost: sourceHost || undefined,
      botId: botId || undefined,
      requestData,
      responseData: sanitizeResponseData(responseData),
      statusCode,
      responseTime,
      errorMessage,
    });
  } catch (error) {
    console.error('Failed to log API call:', error);
    // 不抛出错误，避免影响主流程
  }
}

// 隐藏敏感信息
function sanitizeRequestData(data: any): any {
  if (typeof data !== 'object' || data === null) {
    return data;
  }

  const sensitiveKeys = ['mnemonic', 'privatekey', 'private_key', 'password', 'pwd', 'secret', 'key'];
  const sanitized: any = Array.isArray(data) ? [] : {};

  for (const [key, value] of Object.entries(data)) {
    const lowerKey = key.toLowerCase();
    if (sensitiveKeys.some(sk => lowerKey.includes(sk))) {
      sanitized[key] = '***HIDDEN***';
    } else if (typeof value === 'object' && value !== null) {
      sanitized[key] = sanitizeRequestData(value);
    } else {
      sanitized[key] = value;
    }
  }

  return sanitized;
}

function sanitizeResponseData(data: any): any {
  if (typeof data !== 'object' || data === null) {
    return data;
  }

  // 响应数据通常不包含敏感信息，但为了安全也做处理
  const sensitiveKeys = ['privatekey', 'private_key', 'secret'];
  const sanitized: any = Array.isArray(data) ? [] : {};

  for (const [key, value] of Object.entries(data)) {
    const lowerKey = key.toLowerCase();
    if (sensitiveKeys.some(sk => lowerKey.includes(sk))) {
      sanitized[key] = '***HIDDEN***';
    } else if (typeof value === 'object' && value !== null) {
      sanitized[key] = sanitizeResponseData(value);
    } else {
      sanitized[key] = value;
    }
  }

  return sanitized;
}
