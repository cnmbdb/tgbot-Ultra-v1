import { NextRequest, NextResponse } from 'next/server';
import { logApiCall } from '@/lib/logger';

// /api/premium 路由，重定向到 /api/ton/premium 的处理逻辑
export async function POST(request: NextRequest) {
  const startTime = Date.now();
  let responseData: any = null;
  let statusCode = 200;
  let errorMessage: string | undefined = undefined;

  try {
    const body = await request.json();
    const { username, mnemonic, hash_value, cookie, months } = body;

    if (!username || !mnemonic || !hash_value || !cookie || !months) {
      statusCode = 400;
      responseData = { code: 400, msg: '缺少必要参数' };
      return NextResponse.json(responseData, { status: 400 });
    }

    // 直接导入并使用 ton/premium 的处理逻辑
    const { processTonPremium } = await import('@/lib/ton');
    
    const result = await processTonPremium({
      username,
      mnemonic,
      hash_value,
      cookie,
      months,
    });

    responseData = result;
    const response = NextResponse.json(result);
    
    // 异步记录日志
    setTimeout(() => {
      logApiCall(request, responseData, statusCode, Date.now() - startTime, errorMessage);
    }, 0);

    return response;
  } catch (error: any) {
    statusCode = 500;
    errorMessage = error.message || '处理失败';
    responseData = {
      code: 500,
      msg: errorMessage,
    };
    
    const response = NextResponse.json(responseData, { status: 500 });
    
    // 异步记录日志
    setTimeout(() => {
      logApiCall(request, responseData, statusCode, Date.now() - startTime, errorMessage);
    }, 0);

    return response;
  }
}
