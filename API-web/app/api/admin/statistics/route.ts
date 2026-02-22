import { NextRequest, NextResponse } from 'next/server';
import { getStatistics } from '@/lib/db';

export async function GET(request: NextRequest) {
  try {
    const stats = await getStatistics();

    return NextResponse.json({
      code: 200,
      msg: 'success',
      data: stats,
    });
  } catch (error: any) {
    return NextResponse.json(
      {
        code: 500,
        msg: error.message || '获取统计信息失败',
      },
      { status: 500 }
    );
  }
}
