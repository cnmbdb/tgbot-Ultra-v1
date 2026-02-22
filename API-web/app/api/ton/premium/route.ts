import { NextRequest, NextResponse } from 'next/server';
import { processTonPremium } from '@/lib/ton';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { username, mnemonic, hash_value, cookie, months } = body;

    if (!username || !mnemonic || !hash_value || !cookie || !months) {
      return NextResponse.json(
        { code: 400, msg: '缺少必要参数' },
        { status: 400 }
      );
    }

    const result = await processTonPremium({
      username,
      mnemonic,
      hash_value,
      cookie,
      months,
    });

    return NextResponse.json(result);
  } catch (error: any) {
    return NextResponse.json(
      {
        code: 500,
        msg: error.message || '处理失败',
      },
      { status: 500 }
    );
  }
}
