import { NextRequest, NextResponse } from 'next/server';
import { getBotConnections } from '@/lib/db';

export async function GET(request: NextRequest) {
  try {
    const searchParams = request.nextUrl.searchParams;
    const page = parseInt(searchParams.get('page') || '1');
    const pageSize = parseInt(searchParams.get('pageSize') || '20');
    const botId = searchParams.get('botId') || undefined;
    const sourceIp = searchParams.get('sourceIp') || undefined;
    const status = searchParams.get('status') || undefined;

    const result = await getBotConnections({
      page,
      pageSize,
      botId,
      sourceIp,
      status,
    });

    return NextResponse.json({
      code: 200,
      msg: 'success',
      data: result,
    });
  } catch (error: any) {
    return NextResponse.json(
      {
        code: 500,
        msg: error.message || '获取机器人连接失败',
      },
      { status: 500 }
    );
  }
}
