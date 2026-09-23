import { redirect } from "next/navigation";
import { AccountSidebar } from "@/components/account/sidebar";
import { getMe } from "@/lib/data";

export default async function AccountLayout({ children }: LayoutProps<"/account">) {
  const me = await getMe();
  if (!me) redirect("/login?next=/account");

  return (
    <div className="mx-auto max-w-6xl px-4 py-10">
      <h1 className="mb-6 text-2xl font-bold">My Account</h1>
      <div className="grid items-start gap-6 md:grid-cols-[220px_1fr]">
        <AccountSidebar />
        <div className="min-w-0">{children}</div>
      </div>
    </div>
  );
}
