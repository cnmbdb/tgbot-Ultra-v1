import { NextRequest, NextResponse } from 'next/server';
import { multiSign } from '@/lib/tron';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { pri1, walletaddress, multiaddress, multitype, issendtrx } = body;

    if (!pri1 || !walletaddress) {
      return NextResponse.json(
        { code: 400, msg: '缺少必要参数' },
        { status: 400 }
      );
    }

    const result = await multiSign(
      walletaddress,
      multiaddress || null,
      pri1,
      multitype || 1,
      issendtrx || 'Y'
    );

    return NextResponse.json({
      code: 200,
      data: {
        txid: result.txid,
      },
      msg: '多签成功',
    });
  } catch (error: any) {
    return NextResponse.json(
      {
        code: 500,
        msg: error.message || '多签失败',
      },
      { status: 500 }
    );
  }
}
