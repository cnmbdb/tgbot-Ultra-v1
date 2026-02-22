import { NextRequest, NextResponse } from 'next/server';
import { getApiLogs } from '@/lib/db';

export async function GET(request: NextRequest) {
  try {
    const searchParams = request.nextUrl.searchParams;
    const page = parseInt(searchParams.get('page') || '1');
    const pageSize = parseInt(searchParams.get('pageSize') || '20');
    const apiPath = searchParams.get('apiPath') || undefined;
    const sourceIp = searchParams.get('sourceIp') || undefined;
    const botId = searchParams.get('botId') || undefined;
    const startTime = searchParams.get('startTime') || undefined;
    const endTime = searchParams.get('endTime') || undefined;

    const result = await getApiLogs({
      page,
      pageSize,
      apiPath,
      sourceIp,
      botId,
      startTime,
      endTime,
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
        msg: error.message || '获取日志失败',
      },
      { status: 500 }
    );
  }
}
