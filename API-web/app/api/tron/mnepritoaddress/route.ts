import { NextRequest, NextResponse } from 'next/server';
import { getAddressFromPrivateKey, getAddressFromMnemonic, getAccountBalance } from '@/lib/tron';

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
      const result = getAddressFromMnemonic(inputkey);
      address = result.address;
      privateKey = result.privateKey;
    } else {
      // 私钥
      address = getAddressFromPrivateKey(inputkey);
      privateKey = inputkey.startsWith('0x') ? inputkey.slice(2) : inputkey;
    }

    // 获取余额
    const balance = await getAccountBalance(address);

    return NextResponse.json({
      code: 200,
      data: {
        address,
        privateKey: `0x${privateKey}`,
        usdtamount: balance.usdt.toString(),
        trxamount: balance.trx.toString(),
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
