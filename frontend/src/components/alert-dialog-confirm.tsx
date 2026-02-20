import * as React from "react";

import {
	AlertDialog,
	AlertDialogAction,
	AlertDialogCancel,
	AlertDialogContent,
	AlertDialogDescription,
	AlertDialogFooter,
	AlertDialogHeader,
	AlertDialogTitle,
	AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import {
	type UseAlertDialogOptions,
	useAlertDialog,
} from "@/hooks/use-alert-dialog";

export interface AlertDialogConfirmProps extends UseAlertDialogOptions {
	trigger?: React.ReactNode;
	open?: boolean;
	onOpenChange?: (open: boolean) => void;
}

export function AlertDialogConfirm({
	trigger,
	open: controlledOpen,
	onOpenChange: controlledOnOpenChange,
	title = "Are you absolutely sure?",
	description = "This action cannot be undone.",
	cancelText = "Cancel",
	confirmText = "Continue",
	onConfirm,
	onCancel,
}: AlertDialogConfirmProps) {
	const hook = useAlertDialog({
		title,
		description,
		cancelText,
		confirmText,
		onConfirm,
		onCancel,
	});

	const isControlled = controlledOpen !== undefined;
	const open = isControlled ? controlledOpen : hook.open;
	const onOpenChange = isControlled
		? controlledOnOpenChange
		: hook.onOpenChange;

	const handleConfirm = React.useCallback(async () => {
		if (onConfirm) {
			await onConfirm();
		}
		if (isControlled && controlledOnOpenChange) {
			controlledOnOpenChange(false);
		} else {
			hook.onConfirm();
		}
	}, [onConfirm, isControlled, controlledOnOpenChange, hook]);

	const displayTitle = title || hook.title;
	const displayDescription = description || hook.description;
	const displayCancelText = cancelText || hook.cancelText;
	const displayConfirmText = confirmText || hook.confirmText;

	return (
		<AlertDialog open={open} onOpenChange={onOpenChange}>
			{trigger && <AlertDialogTrigger asChild>{trigger}</AlertDialogTrigger>}
			<AlertDialogContent>
				<AlertDialogHeader>
					<AlertDialogTitle>{displayTitle}</AlertDialogTitle>
					<AlertDialogDescription>{displayDescription}</AlertDialogDescription>
				</AlertDialogHeader>
				<AlertDialogFooter>
					<AlertDialogCancel>{displayCancelText}</AlertDialogCancel>
					<AlertDialogAction onClick={handleConfirm}>
						{displayConfirmText}
					</AlertDialogAction>
				</AlertDialogFooter>
			</AlertDialogContent>
		</AlertDialog>
	);
}
