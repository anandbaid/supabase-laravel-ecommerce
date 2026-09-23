import type { InputHTMLAttributes, ReactNode } from "react";

export const inputClass =
  "w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500";

export function FieldError({ errors, name }: { errors?: Record<string, string[]>; name: string }) {
  const msg = errors?.[name]?.[0];
  return msg ? <p className="mt-1 text-xs text-red-600">{msg}</p> : null;
}

type FieldProps = InputHTMLAttributes<HTMLInputElement> & {
  name: string;
  label: ReactNode;
  errors?: Record<string, string[]>;
  optional?: boolean;
  wrapperClassName?: string;
};

export function Field({ name, label, errors, optional, required, wrapperClassName, ...rest }: FieldProps) {
  const invalid = Boolean(errors?.[name]);
  return (
    <div className={wrapperClassName}>
      <label htmlFor={rest.id ?? name} className="mb-1 block text-sm font-medium text-gray-700">
        {label}
        {required && <span className="text-red-500" aria-hidden> *</span>}
        {optional && <span className="font-normal text-gray-400"> (optional)</span>}
      </label>
      <input
        id={rest.id ?? name}
        name={name}
        required={required}
        aria-invalid={invalid || undefined}
        className={`${inputClass} ${invalid ? "border-red-400" : ""}`}
        {...rest}
      />
      <FieldError errors={errors} name={name} />
    </div>
  );
}

export function Alert({ tone, children }: { tone: "success" | "error" | "info"; children: ReactNode }) {
  const styles = {
    success: "border-green-200 bg-green-50 text-green-700",
    error: "border-red-200 bg-red-50 text-red-700",
    info: "border-blue-200 bg-blue-50 text-blue-800",
  }[tone];
  return <div className={`rounded-lg border px-4 py-3 text-sm ${styles}`} role={tone === "error" ? "alert" : "status"}>{children}</div>;
}
