import type { Metadata } from "next";
import { CircleUser } from "lucide-react";
import { ProfileForm } from "@/components/account/profile-form";
import { getMe } from "@/lib/data";

export const metadata: Metadata = { title: "Account Details" };

export default async function ProfilePage() {
  const me = await getMe();
  if (!me) return null;

  return (
    <div className="rounded-xl bg-white p-6 shadow-sm">
      <h2 className="mb-5 flex items-center gap-2 font-bold text-slate-900">
        <CircleUser className="h-5 w-5 text-blue-600" /> Account Details
      </h2>
      <ProfileForm user={me.user} />
    </div>
  );
}
