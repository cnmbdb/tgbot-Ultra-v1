import { NextRequest, NextResponse } from 'next/server';
import { sendTRX } from '@/lib/tron';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { fromaddress, toaddress, sendamount, pri1, permissionid } = body;

    if (!fromaddress || !toaddress || !sendamount || !pri1) {
      return NextResponse.json(
        { code: 400, msg: '缺少必要参数' },
        { status: 400 }
      );
    }

    const result = await sendTRX(
      fromaddress,
      toaddress,
      parseFloat(sendamount),
      pri1,
      permissionid || 0
    );

    return NextResponse.json({
      code: 200,
      data: {
        txid: result.txid,
      },
      msg: '发送成功',
    });
  } catch (error: any) {
    return NextResponse.json(
      {
        code: 500,
        msg: error.message || '发送失败',
      },
      { status: 500 }
    );
  }
}
