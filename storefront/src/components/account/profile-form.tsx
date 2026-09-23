"use client";

import { useFormAction } from "@/components/ui/use-form-action";
import { useRouter } from "next/navigation";
import { useEffect } from "react";
import { Lock, Mail, MapPin, Phone, Save, User as UserIcon } from "lucide-react";
import { updateProfile } from "@/app/actions/account";
import { Alert, FieldError, inputClass } from "@/components/ui/form";
import type { User } from "@/lib/types";

export function ProfileForm({ user }: { user: User }) {
  const router = useRouter();
  const [state, onSubmit, pending] = useFormAction(updateProfile, null);
  const errors = state && !state.ok ? state.errors : undefined;

  useEffect(() => {
    if (state?.ok) router.refresh(); // header shows the (possibly new) name
  }, [state, router]);

  const label = "flex items-center gap-1.5 text-sm font-medium";

  return (
    <form onSubmit={onSubmit} className="space-y-5">
      {state?.ok && <Alert tone="success">{state.message}</Alert>}
      {state && !state.ok && !errors && <Alert tone="error">{state.message}</Alert>}
      <div className="grid gap-5 sm:grid-cols-2">
        <div>
          <label className={label} htmlFor="name"><UserIcon className="h-4 w-4 text-gray-400" /> Full Name</label>
          <input id="name" name="name" defaultValue={user.name} required className={`${inputClass} mt-1`} />
          <FieldError errors={errors} name="name" />
        </div>
        <div>
          <label className={label} htmlFor="email"><Mail className="h-4 w-4 text-gray-400" /> Email</label>
          <input id="email" type="email" name="email" defaultValue={user.email} required className={`${inputClass} mt-1`} />
          <FieldError errors={errors} name="email" />
        </div>
        <div>
          <label className={label} htmlFor="phone"><Phone className="h-4 w-4 text-gray-400" /> Phone <span className="font-normal text-gray-400">— optional</span></label>
          <input id="phone" name="phone" defaultValue={user.phone ?? ""} className={`${inputClass} mt-1`} />
          <FieldError errors={errors} name="phone" />
        </div>
        <div>
          <label className={label} htmlFor="address"><MapPin className="h-4 w-4 text-gray-400" /> Address <span className="font-normal text-gray-400">— optional</span></label>
          <input id="address" name="address" defaultValue={user.address ?? ""} className={`${inputClass} mt-1`} />
          <FieldError errors={errors} name="address" />
        </div>
      </div>
      <div className="border-t pt-5">
        <h3 className="mb-1 flex items-center gap-1.5 text-sm font-semibold"><Lock className="h-4 w-4 text-gray-400" /> Change Password</h3>
        <p className="mb-3 text-xs text-gray-400">Leave blank to keep your current password.</p>
        <div className="grid gap-5 sm:grid-cols-2">
          <label className="text-sm font-medium">
            New Password
            <input type="password" name="password" minLength={6} autoComplete="new-password" className={`${inputClass} mt-1`} />
            <FieldError errors={errors} name="password" />
          </label>
          <label className="text-sm font-medium">
            Confirm New Password
            <input type="password" name="password_confirmation" autoComplete="new-password" className={`${inputClass} mt-1`} />
          </label>
        </div>
      </div>
      <button disabled={pending} className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 font-medium text-white transition hover:bg-blue-700 disabled:opacity-70">
        <Save className="h-4 w-4" /> {pending ? "Saving…" : "Save Changes"}
      </button>
    </form>
  );
}
