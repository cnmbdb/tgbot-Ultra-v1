import { NextRequest, NextResponse } from 'next/server';

const ADMIN_COOKIE_NAME = 'apiweb_admin_session';

export async function POST(request: NextRequest) {
  let body: { password?: string } = {};

  try {
    body = await request.json();
  } catch {
    // ignore parse error, body 仍保持为空对象
  }

  const password = body.password || '';
  const adminPassword = process.env.ADMIN_PASSWORD;

  if (!adminPassword) {
    return NextResponse.json(
      {
        code: 500,
        message: '服务端未配置 ADMIN_PASSWORD，请在环境变量中设置管理密码。',
      },
      { status: 500 },
    );
  }

  if (!password || password !== adminPassword) {
    return NextResponse.json(
      {
        code: 401,
        message: '密码错误',
      },
      { status: 401 },
    );
  }

  const response = NextResponse.json(
    {
      code: 200,
      message: '登录成功',
    },
    { status: 200 },
  );

  // 设置简单的会话 Cookie，值为服务端配置的密码（仅 HttpOnly，可在生产上配合 HTTPS 使用）
  response.cookies.set(ADMIN_COOKIE_NAME, adminPassword, {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    path: '/',
    maxAge: 60 * 60 * 8, // 8 小时
  });

  return response;
}

