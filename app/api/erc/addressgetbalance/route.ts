import { NextRequest, NextResponse } from 'next/server';
import { getEthereumBalance } from '@/lib/ethereum';
import { logApiCall } from '@/lib/logger';

export async function POST(request: NextRequest) {
  const startTime = Date.now();
  let responseData: any = null;
  let statusCode = 200;
  let errorMessage: string | undefined;

  try {
    const body = await request.json();
    const { address } = body;

    if (!address) {
      statusCode = 400;
      responseData = { code: 400, msg: '缺少必要参数' };
      const resp = NextResponse.json(responseData, { status: 400 });
      setTimeout(() => {
        logApiCall(request, responseData, statusCode, Date.now() - startTime);
      }, 0);
      return resp;
    }

    const balance = await getEthereumBalance(address);

    responseData = {
      code: 200,
      data: {
        ethamount: balance.eth,
        usdtamount: balance.usdt,
      },
      msg: '查询成功',
    };
    const resp = NextResponse.json(responseData);
    setTimeout(() => {
      logApiCall(request, responseData, statusCode, Date.now() - startTime);
    }, 0);
    return resp;
  } catch (error: any) {
    statusCode = 500;
    errorMessage = error.message || '查询失败';
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
