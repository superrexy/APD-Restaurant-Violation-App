import { useNavigate } from "@tanstack/react-router";
import { useId, useState } from "react";
import { toast } from "sonner";
import { z } from "zod";
import { authApi } from "@/api/rest/client";
import { Button } from "@/components/ui/button";
import {
	Card,
	CardContent,
	CardDescription,
	CardHeader,
	CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";
import { useAuthStore } from "@/store/use-auth";

const loginFormSchema = z.object({
	email: z.email("Invalid email address"),
	password: z.string().min(6, "Password must be at least 6 characters"),
});

type LoginFormValues = z.infer<typeof loginFormSchema>;

const LoginPage = ({ className, ...props }: React.ComponentProps<"div">) => {
	const navigate = useNavigate();
	const [isLoading, setIsLoading] = useState(false);
	const setAuth = useAuthStore((state) => state.setAuth);
	const [email, setEmail] = useState("");
	const [password, setPassword] = useState("");
	const [errors, setErrors] = useState<
		Partial<Record<keyof LoginFormValues, string[]>>
	>({});
	const emailId = useId();
	const passwordId = useId();

	const onSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
		e.preventDefault();

		const values = { email, password };

		const result = loginFormSchema.safeParse(values);
		if (!result.success) {
			const fieldErrors: Partial<Record<keyof LoginFormValues, string[]>> = {};
			for (const issue of result.error.issues) {
				const path = issue.path[0] as keyof LoginFormValues;
				if (path) {
					fieldErrors[path] = fieldErrors[path] || [];
					fieldErrors[path]?.push(issue.message);
				}
			}
			setErrors(fieldErrors);
			return;
		}

		setErrors({});
		setIsLoading(true);
		try {
			const response = await authApi.login(values);
			setAuth(response.data.token, response.data.user);
			navigate({ to: "/" });
		} catch (error) {
			console.error("Login error:", error);
			const message =
				error instanceof Error && "response" in error
					? (error as { response?: { data?: { message?: string } } }).response
							?.data?.message
					: undefined;
			toast.error(message || "Invalid email or password");
		} finally {
			setIsLoading(false);
		}
	};

	const handleSubmit = (e: React.MouseEvent<HTMLButtonElement>) => {
		console.log("handleSubmit called");
		const form = e.currentTarget.closest("form");
		if (form) {
			form.dispatchEvent(
				new Event("submit", { bubbles: true, cancelable: true }),
			);
		}
	};

	return (
		<div className="h-screen w-screen flex items-center justify-center">
			<div
				className={cn(
					"flex flex-col gap-6 w-full max-w-md lg:max-w-lg",
					className,
				)}
				{...props}
			>
				<Card>
					<CardHeader>
						<CardTitle>Login to your account</CardTitle>
						<CardDescription>
							Enter your email below to login to your account
						</CardDescription>
					</CardHeader>
					<CardContent>
						<form onSubmit={onSubmit} className="space-y-6">
							<div className="grid gap-2">
								<label htmlFor={emailId} className="text-sm font-medium">
									Email
								</label>
								<Input
									id={emailId}
									type="email"
									name="email"
									placeholder="m@example.com"
									value={email}
									onChange={(e) => setEmail(e.target.value)}
								/>
								{errors.email && (
									<p className="text-destructive text-sm">{errors.email[0]}</p>
								)}
							</div>
							<div className="grid gap-2">
								<label htmlFor={passwordId} className="text-sm font-medium">
									Password
								</label>
								<Input
									id={passwordId}
									type="password"
									name="password"
									value={password}
									onChange={(e) => setPassword(e.target.value)}
								/>
								{errors.password && (
									<p className="text-destructive text-sm">
										{errors.password[0]}
									</p>
								)}
							</div>
							<Button
								type="button"
								onClick={handleSubmit}
								disabled={isLoading}
								className="w-full"
							>
								{isLoading ? "Logging in..." : "Login"}
							</Button>
						</form>
					</CardContent>
				</Card>
			</div>
		</div>
	);
};

export default LoginPage;
