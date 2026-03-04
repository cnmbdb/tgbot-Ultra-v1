import { NextRequest, NextResponse } from 'next/server';
import { delegateAndUndelegate } from '@/lib/tron';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    
    // 支持两种参数格式：
    // 1. 新格式: fromaddress, toaddress, amount, pri1, isdelegate
    // 2. 旧格式: pri, fromaddress, receiveaddress, resourcename, resourceamount, resourcetype, permissionid
    let fromaddress: string;
    let toaddress: string;
    let amount: number;
    let pri1: string;
    let isdelegate: boolean;
    let permissionid: number = 0;

    if (body.pri && body.fromaddress && body.receiveaddress) {
      // 旧格式（兼容现有代码）
      fromaddress = body.fromaddress;
      toaddress = body.receiveaddress;
      amount = parseFloat(body.resourceamount || body.amount || '0');
      pri1 = body.pri;
      // resourcetype: 1=代理, 2=回收能量, 3=回收TRX
      // 如果是回收（2或3），则 isdelegate = false
      isdelegate = body.resourcetype === 1;
      permissionid = body.permissionid || 0;
    } else {
      // 新格式
      fromaddress = body.fromaddress;
      toaddress = body.toaddress;
      amount = parseFloat(body.amount || '0');
      pri1 = body.pri1;
      isdelegate = body.isdelegate !== false;
      permissionid = body.permissionid || 0;
    }

    if (!fromaddress || !toaddress || !amount || !pri1) {
      return NextResponse.json(
        { code: 400, msg: '缺少必要参数' },
        { status: 400 }
      );
    }

    const result = await delegateAndUndelegate(
      fromaddress,
      toaddress,
      amount,
      pri1,
      isdelegate,
      permissionid
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
