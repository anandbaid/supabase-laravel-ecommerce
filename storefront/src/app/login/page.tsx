import type { Metadata } from "next";
import { redirect } from "next/navigation";
import { LoginForm } from "@/components/auth/auth-forms";
import { getMe } from "@/lib/data";
import { safeNextPath } from "@/lib/next-param";

export const metadata: Metadata = { title: "Login" };

export default async function LoginPage({ searchParams }: PageProps<"/login">) {
  const sp = await searchParams;
  const next = safeNextPath(sp.next);
  if (await getMe()) redirect(next);

  const notice = next.startsWith("/checkout")
    ? "Please log in to checkout."
    : next.startsWith("/account")
      ? "Please log in to continue."
      : undefined;

  return (
    <div className="mx-auto max-w-md px-4 py-16">
      <div className="rounded-xl bg-white p-8 shadow-sm">
        <h1 className="mb-6 text-center text-2xl font-bold">Welcome Back</h1>
        <LoginForm next={next} notice={notice} />
      </div>
    </div>
  );
}
