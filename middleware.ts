import { NextRequest, NextResponse } from 'next/server';

const ADMIN_COOKIE_NAME = 'apiweb_admin_session';
const ADMIN_BASE_PATH = '/secure-admin-8f3k9q';

// 中间件：
// 1. 为普通 API 请求添加请求 ID
// 2. 为后台管理页面与 /api/admin/* 增加简单的 Cookie 鉴权保护
export async function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;

  // 保护后台页面与 admin API（登录接口与登录页本身除外）
  const isAdminLoginPage = pathname === `${ADMIN_BASE_PATH}/login`;
  const isAdminPage = pathname.startsWith(ADMIN_BASE_PATH) && !isAdminLoginPage;
  const isAdminApi = pathname.startsWith('/api/admin') && pathname !== '/api/admin/login';

  if (isAdminPage || isAdminApi) {
    const adminPassword = process.env.ADMIN_PASSWORD;
    const session = request.cookies.get(ADMIN_COOKIE_NAME)?.value;

    if (!adminPassword) {
      return NextResponse.json(
        {
          code: 500,
          message: '服务端未配置 ADMIN_PASSWORD，请在环境变量中设置管理密码。',
        },
        { status: 500 },
      );
    }

    if (!session || session !== adminPassword) {
      // 未登录或会话无效，统一跳转到登录页
      const loginUrl = request.nextUrl.clone();
      loginUrl.pathname = `${ADMIN_BASE_PATH}/login`;
      loginUrl.search = '';
      return NextResponse.redirect(loginUrl);
    }

    return NextResponse.next();
  }

  // 为普通 API 请求添加请求 ID（不含 /api/admin/*）
  if (pathname.startsWith('/api/') && !pathname.startsWith('/api/admin')) {
    const response = NextResponse.next();
    response.headers.set('x-request-id', Date.now().toString());
    return response;
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/api/:path*', '/secure-admin-8f3k9q/:path*'],
};

