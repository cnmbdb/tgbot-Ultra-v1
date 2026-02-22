import { NextRequest, NextResponse } from 'next/server';

// 简化中间件，只添加请求ID，实际日志记录在路由中完成
export async function middleware(request: NextRequest) {
  // 只处理 API 请求
  if (request.nextUrl.pathname.startsWith('/api/') && !request.nextUrl.pathname.startsWith('/api/admin')) {
    const response = NextResponse.next();
    // 添加请求ID用于追踪
    response.headers.set('x-request-id', Date.now().toString());
    return response;
  }

  return NextResponse.next();
}

export const config = {
  matcher: '/api/:path*',
};
