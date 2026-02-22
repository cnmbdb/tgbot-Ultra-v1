import { NextRequest, NextResponse } from 'next/server';
import { getEthereumBalance } from '@/lib/ethereum';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { address } = body;

    if (!address) {
      return NextResponse.json(
        { code: 400, msg: '缺少必要参数' },
        { status: 400 }
      );
    }

    const balance = await getEthereumBalance(address);

    return NextResponse.json({
      code: 200,
      data: {
        ethamount: balance.eth,
        usdtamount: balance.usdt,
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
