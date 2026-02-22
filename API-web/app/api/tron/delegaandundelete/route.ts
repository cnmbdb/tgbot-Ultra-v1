import { NextRequest, NextResponse } from 'next/server';
import { delegateAndUndelegate } from '@/lib/tron';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { fromaddress, toaddress, amount, pri1, isdelegate } = body;

    if (!fromaddress || !toaddress || !amount || !pri1) {
      return NextResponse.json(
        { code: 400, msg: '缺少必要参数' },
        { status: 400 }
      );
    }

    const result = await delegateAndUndelegate(
      fromaddress,
      toaddress,
      parseFloat(amount),
      pri1,
      isdelegate !== false
    );

    return NextResponse.json({
      code: 200,
      data: {
        txid: result.txid,
      },
      msg: '操作成功',
    });
  } catch (error: any) {
    return NextResponse.json(
      {
        code: 500,
        msg: error.message || '操作失败',
      },
      { status: 500 }
    );
  }
}
