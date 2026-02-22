import { NextRequest, NextResponse } from 'next/server';
import { getEthereumAddressFromPrivateKey, getEthereumAddressFromMnemonic } from '@/lib/ethereum';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { inputkey, type } = body;

    if (!inputkey) {
      return NextResponse.json(
        { code: 400, msg: '缺少必要参数' },
        { status: 400 }
      );
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

    return NextResponse.json({
      code: 200,
      data: {
        address,
        privateKey,
      },
      msg: '查询成功',
    });
  } catch (error: any) {
    return NextResponse.json(
      {
        code: 500,
        msg: error.message || '查询失败',
      },
      { status: 500 }
    );
  }
}
