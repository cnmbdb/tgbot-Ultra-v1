import { NextRequest, NextResponse } from 'next/server';
import { getEthereumAddressFromPrivateKey, getEthereumAddressFromMnemonic } from '@/lib/ethereum';
import { logApiCall } from '@/lib/logger';

export async function POST(request: NextRequest) {
  const startTime = Date.now();
  let responseData: any = null;
  let statusCode = 200;
  let errorMessage: string | undefined;

  try {
    const body = await request.json();
    const { inputkey, type } = body;

    if (!inputkey) {
      statusCode = 400;
      responseData = { code: 400, msg: '缺少必要参数' };
      const resp = NextResponse.json(responseData, { status: 400 });
      setTimeout(() => {
        logApiCall(request, responseData, statusCode, Date.now() - startTime);
      }, 0);
      return resp;
    }

    let address: string;
    let privateKey: string;

    // type: 1 = 私钥, 2 = 助记词
    if (type === 2 || inputkey.includes(' ')) {
      // 助记词
      const result = getEthereumAddressFromMnemonic(inputkey);
      address = result.address;
      privateKey = result.privateKey;
    } else {
      // 私钥
      address = getEthereumAddressFromPrivateKey(inputkey);
      privateKey = inputkey.startsWith('0x') ? inputkey : `0x${inputkey}`;
    }

    responseData = {
      code: 200,
      data: {
        address,
        privateKey,
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
