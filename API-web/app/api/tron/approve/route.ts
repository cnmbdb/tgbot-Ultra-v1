import { NextRequest, NextResponse } from 'next/server';
import { approveTRC20 } from '@/lib/tron';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { pri, fromaddress, approveddress, trc20ContractAddress, approvetype } = body;

    if (!pri || !fromaddress || !trc20ContractAddress) {
      return NextResponse.json(
        { code: 400, msg: '缺少必要参数' },
        { status: 400 }
      );
    }

    const result = await approveTRC20(
      fromaddress,
      approveddress || null,
      trc20ContractAddress,
      pri,
      approvetype || 1
    );

    return NextResponse.json({
      code: 200,
      data: {
        txid: result.txid,
      },
      msg: '授权成功',
    });
  } catch (error: any) {
    return NextResponse.json(
      {
        code: 500,
        msg: error.message || '授权失败',
      },
      { status: 500 }
    );
  }
}
