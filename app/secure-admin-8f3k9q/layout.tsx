import { AdminShell } from './_components/AdminShell';

export default function SecureAdminLayout({ children }: { children: React.ReactNode }) {
  return <AdminShell>{children}</AdminShell>;
}

