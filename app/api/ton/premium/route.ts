import { NextRequest, NextResponse } from 'next/server';
import { processTonPremium } from '@/lib/ton';
import { logApiCall } from '@/lib/logger';

export async function POST(request: NextRequest) {
  const startTime = Date.now();
  let responseData: any = null;
  let statusCode = 200;
  let errorMessage: string | undefined;

  try {
    const body = await request.json();
    const { username, mnemonic, hash_value, cookie, months } = body;

    if (!username || !mnemonic || !hash_value || !cookie || !months) {
      statusCode = 400;
      responseData = { code: 400, msg: '缺少必要参数' };
      const resp = NextResponse.json(responseData, { status: 400 });
      setTimeout(() => {
        logApiCall(request, responseData, statusCode, Date.now() - startTime);
      }, 0);
      return resp;
    }

    const result = await processTonPremium({
      username,
      mnemonic,
      hash_value,
      cookie,
      months,
    });

    responseData = result;
    const resp = NextResponse.json(result);
    setTimeout(() => {
      logApiCall(request, responseData, statusCode, Date.now() - startTime);
    }, 0);
    return resp;
  } catch (error: any) {
    statusCode = 500;
    errorMessage = error.message || '处理失败';
    responseData = {
      code: 500,
      msg: errorMessage,
    };
    const resp = NextResponse.json(responseData, { status: 500 });
    setTimeout(() => {
      logApiCall(request, responseData, statusCode, Date.now() - startTime, errorMessage);
    }, 0);
    return resp;
  }
}
