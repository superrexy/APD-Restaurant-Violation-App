import type {
  User,
  UserStoreRequest,
  UserUpdateRequest,
} from "@/api/rest/client";
import { usersApi } from "@/api/rest/client";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { zodResolver } from "@hookform/resolvers/zod";
import { Loader2 } from "lucide-react";
import { useEffect } from "react";
import { useForm } from "react-hook-form";
import { toast } from "sonner";
import { z } from "zod";
import useRestApi from "../../../hooks/use-rest-api";

const userFormSchema = z
  .object({
    id: z.number().optional(),
    name: z.string().min(1, "Name is required").max(255, "Name is too long"),
    email: z.email("Invalid email address"),
    password: z
      .string()
      .optional()
      .refine(
        (val) => {
          if (!val || val.length === 0) return true;
          return val.length >= 6;
        },
        {
          message: "Password must be at least 6 characters",
        },
      )
      .refine(
        (val) => {
          if (!val || val.length === 0) return true;
          return val.length <= 255;
        },
        {
          message: "Password is too long",
        },
      ),
  })
  .refine(
    (data) => {
      if (!data.id) {
        return data.password && data.password.length > 0;
      }
      return true;
    },
    {
      message: "Password is required for new users",
      path: ["password"],
    },
  );

type UserFormValues = z.infer<typeof userFormSchema> & { id?: number };

interface UserFormModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  mode: "create" | "edit";
  user?: User;
  onSuccess?: () => void;
}

const UserFormModal = ({
  open,
  onOpenChange,
  mode,
  user,
  onSuccess,
}: UserFormModalProps) => {
  const { callAPI } = useRestApi();

  const form = useForm<UserFormValues>({
    resolver: zodResolver(userFormSchema),
    defaultValues: {
      id: undefined,
      name: "",
      email: "",
      password: "",
    },
  });

  useEffect(() => {
    if (mode === "edit" && user) {
      form.reset({
        id: user.id,
        name: user.name,
        email: user.email,
        password: "",
      });
    } else if (mode === "create") {
      form.reset({
        id: undefined,
        name: "",
        email: "",
        password: "",
      });
    }
  }, [mode, user, form]);

  const onSubmit = async (values: UserFormValues) => {
    const isEdit = mode === "edit";
    const userId = values.id;

    const submitData: UserStoreRequest | UserUpdateRequest = {
      name: values.name,
      email: values.email,
    };

    if (!isEdit && values.password) {
      (submitData as UserStoreRequest).password = values.password;
    }

    if (isEdit && values.password && values.password.length > 0) {
      (submitData as UserUpdateRequest).password = values.password;
    }

    try {
      if (isEdit && userId) {
        await callAPI(() =>
          usersApi.update(userId, submitData as UserUpdateRequest),
        );
        toast.success("User updated successfully");
      } else {
        await callAPI(() => usersApi.create(submitData as UserStoreRequest));
        toast.success("User created successfully");
      }

      form.reset();
      onOpenChange(false);
      onSuccess?.();
    } catch (error) {
      console.error("Error submitting user form:", error);
    }
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>
            {mode === "create" ? "Create User" : "Edit User"}
          </DialogTitle>
          <DialogDescription>
            {mode === "create"
              ? "Create a new user account."
              : "Update user information. Leave password empty to keep current password."}
          </DialogDescription>
        </DialogHeader>
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <FormField
              control={form.control}
              name="name"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Name</FormLabel>
                  <FormControl>
                    <Input placeholder="John Doe" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={form.control}
              name="email"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Email</FormLabel>
                  <FormControl>
                    <Input
                      type="email"
                      placeholder="john@example.com"
                      {...field}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={form.control}
              name="password"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>
                    {mode === "create" ? "Password" : "New Password (optional)"}
                  </FormLabel>
                  <FormControl>
                    <Input
                      type="password"
                      placeholder={
                        mode === "create"
                          ? "••••••••"
                          : "Leave empty to keep current"
                      }
                      {...field}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <DialogFooter>
              <Button
                type="button"
                variant="outline"
                onClick={() => onOpenChange(false)}
                disabled={form.formState.isSubmitting}
              >
                Cancel
              </Button>
              <Button type="submit" disabled={form.formState.isSubmitting}>
                {form.formState.isSubmitting ? (
                  <>
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    {mode === "create" ? "Creating..." : "Updating..."}
                  </>
                ) : mode === "create" ? (
                  "Create"
                ) : (
                  "Update"
                )}
              </Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
};

export default UserFormModal;
