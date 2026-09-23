import type { Metadata } from "next";
import { redirect } from "next/navigation";
import { RegisterForm } from "@/components/auth/auth-forms";
import { getMe } from "@/lib/data";
import { safeNextPath } from "@/lib/next-param";

export const metadata: Metadata = { title: "Create Account" };

export default async function RegisterPage({ searchParams }: PageProps<"/register">) {
  const next = safeNextPath((await searchParams).next);
  if (await getMe()) redirect(next);

  return (
    <div className="mx-auto max-w-md px-4 py-16">
      <div className="rounded-xl bg-white p-8 shadow-sm">
        <h1 className="mb-6 text-center text-2xl font-bold">Create Account</h1>
        <RegisterForm next={next} />
      </div>
    </div>
  );
}
