import { NextRequest, NextResponse } from 'next/server';
import { sendTRC20 } from '@/lib/tron';
import { logApiCall } from '@/lib/logger';

export async function POST(request: NextRequest) {
  const startTime = Date.now();
  let responseData: any = null;
  let statusCode = 200;
  let errorMessage: string | undefined;

  try {
    const body = await request.json();
    const { fromaddress, toaddress, sendamount, trc20ContractAddress, pri1, permissionid } = body;

    if (!fromaddress || !toaddress || !sendamount || !trc20ContractAddress || !pri1) {
      statusCode = 400;
      responseData = { code: 400, msg: '缺少必要参数' };
      const resp = NextResponse.json(responseData, { status: 400 });
      setTimeout(() => {
        logApiCall(request, responseData, statusCode, Date.now() - startTime);
      }, 0);
      return resp;
    }

    const result = await sendTRC20(
      fromaddress,
      toaddress,
      parseFloat(sendamount),
      trc20ContractAddress,
      pri1,
      permissionid || 0
    );

    responseData = {
      code: 200,
      data: { txid: result.txid },
      msg: '发送成功',
    };
    const resp = NextResponse.json(responseData);
    setTimeout(() => {
      logApiCall(request, responseData, statusCode, Date.now() - startTime);
    }, 0);
    return resp;
  } catch (error: any) {
    statusCode = 500;
    errorMessage = error.message || '发送失败';
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
