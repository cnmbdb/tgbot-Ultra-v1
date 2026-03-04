import { NextRequest, NextResponse } from 'next/server';
import { approveTRC20 } from '@/lib/tron';
import { logApiCall } from '@/lib/logger';

export async function POST(request: NextRequest) {
  const startTime = Date.now();
  let responseData: any = null;
  let statusCode = 200;
  let errorMessage: string | undefined;

  try {
    const body = await request.json();
    const { pri, fromaddress, approveddress, trc20ContractAddress, approvetype } = body;

    if (!pri || !fromaddress || !trc20ContractAddress) {
      statusCode = 400;
      responseData = { code: 400, msg: '缺少必要参数' };
      const resp = NextResponse.json(responseData, { status: 400 });
      setTimeout(() => {
        logApiCall(request, responseData, statusCode, Date.now() - startTime);
      }, 0);
      return resp;
    }

    const result = await approveTRC20(
      fromaddress,
      approveddress || null,
      trc20ContractAddress,
      pri,
      approvetype || 1
    );

    responseData = {
      code: 200,
      data: { txid: result.txid },
      msg: '授权成功',
    };
    const resp = NextResponse.json(responseData);
    setTimeout(() => {
      logApiCall(request, responseData, statusCode, Date.now() - startTime);
    }, 0);
    return resp;
  } catch (error: any) {
    statusCode = 500;
    errorMessage = error.message || '授权失败';
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
