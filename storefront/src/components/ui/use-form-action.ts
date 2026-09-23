"use client";

import { startTransition, useActionState, type FormEvent } from "react";
import type { ActionResult } from "@/lib/types";

/**
 * useActionState for forms that should keep what the user typed when the
 * server says no (React resets forms after a form `action` completes, which
 * would wipe e.g. the email after a wrong password).
 */
export function useFormAction<T>(
  action: (state: ActionResult<T> | null, form: FormData) => Promise<ActionResult<T>>,
  initial: ActionResult<T> | null,
) {
  const [state, dispatch, pending] = useActionState<ActionResult<T> | null, FormData>(action, initial);

  const onSubmit = (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    startTransition(() => dispatch(form));
  };

  return [state, onSubmit, pending] as const;
}
